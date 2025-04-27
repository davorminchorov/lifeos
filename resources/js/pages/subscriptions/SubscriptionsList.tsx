import React, { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';
import SubscriptionManager from '../../components/subscriptions/SubscriptionManager';
import { useSubscriptionStore } from '../../store/subscriptionStore';
import {
  useSubscriptions,
  useSubscriptionCategories,
  useCancelSubscription
} from '../../queries/subscriptionQueries';
import { Subscription as SubscriptionType } from '../../components/subscriptions/SubscriptionCard';

interface Meta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

const SubscriptionsList: React.FC = () => {
  const navigate = useNavigate();
  const [state, actions] = useSubscriptionStore();
  const { filters } = state;

  // Convert our store filters to query params
  const queryParams = {
    status: filters.status === 'all' ? '' : filters.status,
    category: filters.category === 'all' ? '' : filters.category,
    sort_by: filters.sort_by
  };

  // Use React Query hooks
  const {
    data: subscriptionsData,
    isLoading,
    error: queryError
  } = useSubscriptions(queryParams);

  const { data: categories = [] } = useSubscriptionCategories();

  const cancelSubscriptionMutation = useCancelSubscription();

  // Update error state from query
  useEffect(() => {
    if (queryError) {
      actions.setError('Failed to load subscriptions');
      console.error(queryError);
    } else {
      actions.setError(null);
    }
  }, [queryError, actions]);

  // Extract and transform subscription data for the UI
  const subscriptions: SubscriptionType[] = subscriptionsData?.data?.map(sub => ({
    id: sub.id,
    name: sub.name,
    description: sub.description,
    status: sub.status === 'active' ? 'active' :
            sub.status === 'cancelled' ? 'canceled' :
            sub.status === 'paused' ? 'past_due' : 'inactive',
    currentPeriodEnd: sub.next_payment_date || undefined,
    price: sub.amount,
    interval: sub.billing_cycle === 'monthly' ? 'month' :
              sub.billing_cycle === 'annually' ? 'year' :
              sub.billing_cycle === 'weekly' ? 'week' : 'day',
    currency: sub.currency,
    features: sub.category ? [sub.category] : undefined
  })) || [];

  // Extract pagination meta
  const meta: Meta = subscriptionsData?.meta || {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  };

  const handlePageChange = (page: number) => {
    // React Query will handle refetching when we change the page param
    // This would ideally be reflected in the URL as well
  };

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    actions.updateFilter({ name, value });
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // The query key change will trigger a refetch
  };

  const handleManageSubscription = (subscription: SubscriptionType) => {
    navigate(`/subscriptions/${subscription.id}`);
  };

  const handleCancelSubscription = async (subscription: SubscriptionType) => {
    const endDate = new Date().toISOString().split('T')[0]; // Today's date

    cancelSubscriptionMutation.mutate(
      { subscriptionId: subscription.id, endDate },
      {
        onError: (error) => {
          console.error('Error cancelling subscription:', error);
          actions.setError('Failed to cancel subscription');
        }
      }
    );
  };

  const handleRenewSubscription = (subscription: SubscriptionType) => {
    // Navigate to edit page where the user can update the subscription
    navigate(`/subscriptions/${subscription.id}/edit`);
  };

  return (
    <PageContainer
      title="Subscriptions"
      subtitle="Manage your recurring subscriptions and track upcoming payments"
      actions={
        <Link to="/subscriptions/create">
          <Button variant="filled">Add Subscription</Button>
        </Link>
      }
    >
      <Card variant="elevated" className="mb-6">
        <CardHeader>
          <CardTitle>Filter Subscriptions</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label htmlFor="status" className="block text-sm font-medium text-on-surface-variant mb-1">
                Status
              </label>
              <select
                id="status"
                name="status"
                value={filters.status}
                onChange={handleFilterChange}
                className="w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface"
              >
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div>
              <label htmlFor="category" className="block text-sm font-medium text-on-surface-variant mb-1">
                Category
              </label>
              <select
                id="category"
                name="category"
                value={filters.category}
                onChange={handleFilterChange}
                className="w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface"
              >
                <option value="all">All Categories</option>
                {categories.map(category => (
                  <option key={category} value={category}>{category}</option>
                ))}
              </select>
            </div>

            <div>
              <label htmlFor="sort_by" className="block text-sm font-medium text-on-surface-variant mb-1">
                Sort By
              </label>
              <select
                id="sort_by"
                name="sort_by"
                value={filters.sort_by}
                onChange={handleFilterChange}
                className="w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface"
              >
                <option value="name">Name</option>
                <option value="amount">Amount</option>
                <option value="next_payment_date">Next Payment</option>
              </select>
            </div>

            <div className="flex items-end">
              <Button type="submit" variant="tonal" className="w-full">Filter</Button>
            </div>
          </form>
        </CardContent>
      </Card>

      {isLoading ? (
        <div className="flex justify-center py-10">
          <div className="animate-pulse text-center">
            <div className="h-10 w-40 bg-surface-variant rounded mx-auto mb-4"></div>
            <div className="h-4 w-60 bg-surface-variant rounded mx-auto"></div>
          </div>
        </div>
      ) : state.error ? (
        <div className="bg-error-container border border-error text-on-error-container p-4 rounded-lg">
          {state.error}
        </div>
      ) : (
        <>
          <SubscriptionManager
            subscriptions={subscriptions}
            onManageSubscription={handleManageSubscription}
            onCancelSubscription={handleCancelSubscription}
            onRenewSubscription={handleRenewSubscription}
          />

          {/* Pagination */}
          {meta.total > 0 && meta.last_page > 1 && (
            <div className="mt-8 flex justify-center">
              <div className="flex space-x-2">
                {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(page => (
                  <button
                    key={page}
                    onClick={() => handlePageChange(page)}
                    className={`px-3 py-1 rounded-md ${
                      meta.current_page === page
                        ? 'bg-primary text-on-primary'
                        : 'bg-surface-variant text-on-surface-variant'
                    }`}
                  >
                    {page}
                  </button>
                ))}
              </div>
            </div>
          )}

          {subscriptions.length === 0 && (
            <div className="text-center py-8 p-10 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1">
              <p className="text-headline-small text-on-surface-variant mb-4">You don't have any subscriptions yet.</p>
              <Link to="/subscriptions/create">
                <Button variant="filled">Add Subscription</Button>
              </Link>
            </div>
          )}
        </>
      )}
    </PageContainer>
  );
};

export default SubscriptionsList;

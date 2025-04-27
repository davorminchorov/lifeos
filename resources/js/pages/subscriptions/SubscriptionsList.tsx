import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';
import SubscriptionManager from '../../components/subscriptions/SubscriptionManager';
import { Subscription as SubscriptionType } from '../../components/subscriptions/SubscriptionCard';

interface SubscriptionAPI {
  id: string;
  name: string;
  description: string;
  amount: number;
  currency: string;
  billing_cycle: string;
  start_date: string;
  end_date: string | null;
  status: string;
  website: string | null;
  category: string | null;
  next_payment_date: string | null;
}

interface Meta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

const SubscriptionsList: React.FC = () => {
  const [subscriptions, setSubscriptions] = useState<SubscriptionType[]>([]);
  const [meta, setMeta] = useState<Meta>({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState({
    status: '',
    category: '',
    search: '',
  });

  const fetchSubscriptions = async (page = 1) => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: '10',
        sort_by: 'name',
        sort_direction: 'asc',
        ...filters,
      }).toString();

      const response = await axios.get(`/api/subscriptions?${params}`);
      const apiData = response.data.data || [];
      const metaData = response.data.meta || {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
      };

      // Transform API data to SubscriptionType
      const transformedData: SubscriptionType[] = apiData.map((sub: SubscriptionAPI) => ({
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
      }));

      setSubscriptions(transformedData);
      setMeta(metaData);
      setError(null);
    } catch (err) {
      setError('Failed to load subscriptions');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSubscriptions();
  }, [filters]);

  const handlePageChange = (page: number) => {
    fetchSubscriptions(page);
  };

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    fetchSubscriptions();
  };

  const handleManageSubscription = (subscription: SubscriptionType) => {
    // Navigate to subscription detail page
    window.location.href = `/subscriptions/${subscription.id}`;
  };

  const handleCancelSubscription = async (subscription: SubscriptionType) => {
    try {
      const endDate = new Date().toISOString().split('T')[0]; // Today's date
      await axios.post(`/api/subscriptions/${subscription.id}/cancel`, { end_date: endDate });

      // Refresh subscriptions after cancellation
      fetchSubscriptions();
    } catch (err) {
      console.error('Error cancelling subscription:', err);
      setError('Failed to cancel subscription');
    }
  };

  const handleRenewSubscription = async (subscription: SubscriptionType) => {
    try {
      // For a basic renewal, we'll update the subscription with a new start date
      const startDate = new Date().toISOString().split('T')[0]; // Today's date

      // Get current subscription details first
      const response = await axios.get(`/api/subscriptions/${subscription.id}`);
      const currentData = response.data;

      // Update with new values while keeping most properties the same
      await axios.put(`/api/subscriptions/${subscription.id}`, {
        ...currentData,
        start_date: startDate,
        status: 'active',
        end_date: null
      });

      // Refresh subscriptions after renewal
      fetchSubscriptions();
    } catch (err) {
      console.error('Error renewing subscription:', err);
      setError('Failed to renew subscription');
    }
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
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="cancelled">Cancelled</option>
                <option value="paused">Paused</option>
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
                <option value="">All Categories</option>
                <option value="streaming">Streaming</option>
                <option value="software">Software</option>
                <option value="hosting">Hosting</option>
                <option value="utilities">Utilities</option>
                <option value="memberships">Memberships</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div>
              <label htmlFor="search" className="block text-sm font-medium text-on-surface-variant mb-1">
                Search
              </label>
              <input
                type="text"
                id="search"
                name="search"
                value={filters.search}
                onChange={handleFilterChange}
                placeholder="Search subscriptions..."
                className="w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface"
              />
            </div>

            <div className="flex items-end">
              <Button type="submit" variant="tonal" className="w-full">Filter</Button>
            </div>
          </form>
        </CardContent>
      </Card>

      {loading ? (
        <div className="flex justify-center py-10">
          <div className="animate-pulse text-center">
            <div className="h-10 w-40 bg-surface-variant rounded mx-auto mb-4"></div>
            <div className="h-4 w-60 bg-surface-variant rounded mx-auto"></div>
          </div>
        </div>
      ) : error ? (
        <div className="bg-error-container border border-error text-on-error-container p-4 rounded-lg">
          {error}
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

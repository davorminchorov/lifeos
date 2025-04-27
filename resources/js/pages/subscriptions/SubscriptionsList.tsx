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
        ...filters,
      });

      // Uncomment for actual API usage:
      // const response = await axios.get(`/api/subscriptions?${params}`);
      // const apiData = response.data.data || [];

      // For demo purposes - using mock data
      const apiData: SubscriptionAPI[] = [
        {
          id: '1',
          name: 'Netflix',
          description: 'Premium streaming service',
          amount: 15.99,
          currency: 'USD',
          billing_cycle: 'monthly',
          start_date: '2023-01-15',
          end_date: null,
          status: 'active',
          website: 'https://netflix.com',
          category: 'streaming',
          next_payment_date: new Date(Date.now() + 15 * 24 * 60 * 60 * 1000).toISOString(),
        },
        {
          id: '2',
          name: 'Spotify',
          description: 'Music streaming service',
          amount: 9.99,
          currency: 'USD',
          billing_cycle: 'monthly',
          start_date: '2022-03-10',
          end_date: '2023-11-10',
          status: 'cancelled',
          website: 'https://spotify.com',
          category: 'streaming',
          next_payment_date: null,
        }
      ];

      // Transform API data to SubscriptionType
      const transformedData: SubscriptionType[] = apiData.map(sub => ({
        id: sub.id,
        name: sub.name,
        description: sub.description,
        status: sub.status === 'active' ? 'active' :
                sub.status === 'cancelled' ? 'canceled' :
                sub.status === 'paused' ? 'past_due' : 'inactive',
        currentPeriodEnd: sub.next_payment_date || undefined,
        price: sub.amount,
        interval: sub.billing_cycle === 'monthly' ? 'month' :
                  sub.billing_cycle === 'yearly' ? 'year' :
                  sub.billing_cycle === 'weekly' ? 'week' : 'day',
        currency: sub.currency,
        features: sub.category ? [sub.category] : undefined
      }));

      setSubscriptions(transformedData);

      // Mock meta data
      setMeta({
        current_page: page,
        per_page: 10,
        total: transformedData.length,
        last_page: 1,
      });

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
    console.log('Managing subscription:', subscription);
    // Navigate to subscription detail page
    window.location.href = `/subscriptions/${subscription.id}`;
  };

  const handleCancelSubscription = (subscription: SubscriptionType) => {
    console.log('Canceling subscription:', subscription);
    // For demo, just update the local state
    setSubscriptions(prevSubscriptions =>
      prevSubscriptions.map(sub =>
        sub.id === subscription.id
          ? {...sub, status: 'canceled'}
          : sub
      )
    );
  };

  const handleRenewSubscription = (subscription: SubscriptionType) => {
    console.log('Renewing subscription:', subscription);
    // For demo, just update the local state
    setSubscriptions(prevSubscriptions =>
      prevSubscriptions.map(sub =>
        sub.id === subscription.id
          ? {...sub, status: 'active', currentPeriodEnd: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString()}
          : sub
      )
    );
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

      {error && (
        <div className="bg-error-container text-on-error-container p-4 rounded-lg mb-6">
          {error}
        </div>
      )}

      {loading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      ) : (
        <SubscriptionManager
          subscriptions={subscriptions}
          onManageSubscription={handleManageSubscription}
          onCancelSubscription={handleCancelSubscription}
          onRenewSubscription={handleRenewSubscription}
        />
      )}

      {/* Pagination controls */}
      {meta.last_page > 1 && (
        <div className="flex justify-center mt-6">
          <nav className="flex items-center space-x-2">
            <Button
              variant="text"
              disabled={meta.current_page === 1}
              onClick={() => handlePageChange(meta.current_page - 1)}
            >
              Previous
            </Button>

            {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(page => (
              <Button
                key={page}
                variant={meta.current_page === page ? "filled" : "text"}
                onClick={() => handlePageChange(page)}
              >
                {page}
              </Button>
            ))}

            <Button
              variant="text"
              disabled={meta.current_page === meta.last_page}
              onClick={() => handlePageChange(meta.current_page + 1)}
            >
              Next
            </Button>
          </nav>
        </div>
      )}
    </PageContainer>
  );
};

export default SubscriptionsList;

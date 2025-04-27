import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';

interface Subscription {
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
  const [subscriptions, setSubscriptions] = useState<Subscription[]>([]);
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

      const response = await axios.get(`/api/subscriptions?${params}`);
      setSubscriptions(response.data.data);
      setMeta(response.data.meta);
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

  const renderStatusBadge = (status: string) => {
    let className = '';

    switch (status) {
      case 'active':
        className = 'bg-green-100 text-green-800';
        break;
      case 'cancelled':
        className = 'bg-red-100 text-red-800';
        break;
      case 'paused':
        className = 'bg-yellow-100 text-yellow-800';
        break;
      default:
        className = 'bg-gray-100 text-gray-800';
    }

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${className}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  if (loading && subscriptions.length === 0) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Subscriptions</h1>
        <Link to="/subscriptions/create">
          <Button>Add Subscription</Button>
        </Link>
      </div>

      <Card className="mb-6">
        <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4">
          <div>
            <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-1">
              Status
            </label>
            <select
              id="status"
              name="status"
              value={filters.status}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="cancelled">Cancelled</option>
              <option value="paused">Paused</option>
            </select>
          </div>

          <div>
            <label htmlFor="category" className="block text-sm font-medium text-gray-700 mb-1">
              Category
            </label>
            <select
              id="category"
              name="category"
              value={filters.category}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2"
            >
              <option value="">All Categories</option>
              <option value="streaming">Streaming</option>
              <option value="software">Software</option>
              <option value="hosting">Hosting</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-1">
              Search
            </label>
            <input
              type="text"
              id="search"
              name="search"
              value={filters.search}
              onChange={handleFilterChange}
              placeholder="Search subscriptions..."
              className="w-full border border-gray-300 rounded-md shadow-sm p-2"
            />
          </div>

          <div className="flex items-end">
            <Button type="submit" className="w-full">Filter</Button>
          </div>
        </form>
      </Card>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      {!subscriptions ? (
        <Card>
          <div className="p-6 text-center">
            <p className="text-gray-500">Loading subscriptions...</p>
          </div>
        </Card>
      ) : subscriptions.length === 0 ? (
        <Card>
          <div className="p-6 text-center">
            <p className="text-gray-500">No subscriptions found. Create your first subscription!</p>
          </div>
        </Card>
      ) : (
        <>
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-lg overflow-hidden">
              <thead className="bg-gray-100">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Billing Cycle
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Next Payment
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {subscriptions.map((subscription) => (
                  <tr key={subscription.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="font-medium text-gray-900">{subscription.name}</div>
                      {subscription.category && (
                        <div className="text-sm text-gray-500">{subscription.category}</div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {formatCurrency(subscription.amount, subscription.currency)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {subscription.billing_cycle.charAt(0).toUpperCase() + subscription.billing_cycle.slice(1)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {subscription.next_payment_date || 'N/A'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {renderStatusBadge(subscription.status)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        to={`/subscriptions/${subscription.id}`}
                        className="text-indigo-600 hover:text-indigo-900 mr-4"
                      >
                        View
                      </Link>
                      <Link
                        to={`/subscriptions/${subscription.id}/edit`}
                        className="text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </Link>
                      {subscription.status === 'active' && (
                        <>
                          <span className="mx-2 text-gray-300">|</span>
                          <Link
                            to={`/payments/record/${subscription.id}`}
                            className="text-indigo-600 hover:text-indigo-900"
                          >
                            Record Payment
                          </Link>
                        </>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {meta.last_page > 1 && (
            <div className="flex justify-center mt-6">
              <nav className="flex items-center">
                <button
                  onClick={() => handlePageChange(meta.current_page - 1)}
                  disabled={meta.current_page === 1}
                  className={`px-3 py-1 rounded-md ${
                    meta.current_page === 1
                      ? 'text-gray-400 cursor-not-allowed'
                      : 'text-gray-700 hover:bg-gray-100'
                  }`}
                >
                  Previous
                </button>

                {[...Array(meta.last_page)].map((_, i) => (
                  <button
                    key={i}
                    onClick={() => handlePageChange(i + 1)}
                    className={`px-3 py-1 rounded-md ${
                      meta.current_page === i + 1
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-700 hover:bg-gray-100'
                    }`}
                  >
                    {i + 1}
                  </button>
                ))}

                <button
                  onClick={() => handlePageChange(meta.current_page + 1)}
                  disabled={meta.current_page === meta.last_page}
                  className={`px-3 py-1 rounded-md ${
                    meta.current_page === meta.last_page
                      ? 'text-gray-400 cursor-not-allowed'
                      : 'text-gray-700 hover:bg-gray-100'
                  }`}
                >
                  Next
                </button>
              </nav>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default SubscriptionsList;

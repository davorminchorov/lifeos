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
      setSubscriptions(response.data.data || []);
      setMeta(response.data.meta || {
        current_page: 1,
        per_page: 10,
        total: 0,
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

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex flex-col space-y-4 mb-8">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 sm:mb-0">Subscriptions</h1>
          </div>

          <Link to="/subscriptions/create">
            <Button className="whitespace-nowrap">Add Subscription</Button>
          </Link>
        </div>

        <p className="text-gray-600">Manage your recurring subscriptions and track upcoming payments.</p>
      </div>

      <Card className="mb-6 border border-gray-200 shadow-sm">
        <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-t-lg border-b border-gray-200">
          <div>
            <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-1">
              Status
            </label>
            <select
              id="status"
              name="status"
              value={filters.status}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
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
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
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
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
            />
          </div>

          <div className="flex items-end">
            <Button type="submit" className="w-full">Filter</Button>
          </div>
        </form>

        {error && (
          <div className="bg-red-100 border-y border-red-400 text-red-700 px-4 py-3">
            {error}
          </div>
        )}

        {loading && (subscriptions?.length === 0 || !subscriptions) ? (
          <div className="p-8 flex flex-col items-center justify-center">
            <div className="animate-pulse space-y-4 w-full max-w-3xl">
              <div className="h-8 bg-gray-200 rounded w-1/3"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
            </div>
            <p className="text-gray-500 mt-4">Loading subscriptions...</p>
          </div>
        ) : (!subscriptions || subscriptions.length === 0) ? (
          <div className="flex flex-col items-center justify-center p-10">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M10 18a7.94 7.94 0 01-4.242-1.096M15 18a7.94 7.94 0 004.242-1.096M8 15v2m4-2v2m4-2v2M3 13.5C3 7.882 7.882 3 13.5 3S24 7.882 24 13.5" />
            </svg>
            <p className="text-lg font-medium text-gray-600 mb-1">No subscriptions found</p>
            <p className="text-gray-500 text-center mb-4">Start tracking your recurring services by adding your first subscription.</p>
            <Link to="/subscriptions/create">
              <Button size="sm">Add Your First Subscription</Button>
            </Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-50">
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
              <tbody className="bg-white divide-y divide-gray-200">
                {subscriptions.map((subscription) => (
                  <tr key={subscription.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="font-medium text-gray-900">{subscription.name}</div>
                      {subscription.category && (
                        <div className="text-xs text-gray-500 mt-1">
                          <span className="px-2 py-0.5 bg-gray-100 rounded-full">
                            {subscription.category}
                          </span>
                        </div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {formatCurrency(subscription.amount, subscription.currency)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {subscription.billing_cycle.charAt(0).toUpperCase() + subscription.billing_cycle.slice(1)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {subscription.next_payment_date ? (
                        <div className="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                          {subscription.next_payment_date}
                        </div>
                      ) : (
                        <span className="text-gray-400">N/A</span>
                      )}
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
        )}
      </Card>

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="flex justify-center mt-6">
          <nav className="flex items-center shadow-sm rounded-md overflow-hidden border border-gray-200">
            <button
              onClick={() => handlePageChange(meta.current_page - 1)}
              disabled={meta.current_page === 1}
              className={`px-3 py-2 ${
                meta.current_page === 1
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'text-gray-700 hover:bg-gray-100'
              }`}
            >
              Previous
            </button>

            {meta && [...Array(meta.last_page)].map((_, i) => (
              <button
                key={i}
                onClick={() => handlePageChange(i + 1)}
                className={`px-4 py-2 ${
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
              className={`px-3 py-2 ${
                meta.current_page === meta.last_page
                  ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  : 'text-gray-700 hover:bg-gray-100'
              }`}
            >
              Next
            </button>
          </nav>
        </div>
      )}

      {!loading && (!subscriptions || subscriptions.length === 0) && (
        <div className="mt-8 text-center">
          <p className="text-gray-500 mb-2">Looking for payment reporting?</p>
          <p className="text-gray-700 mb-4">Visit the reports section to analyze your subscription spending.</p>
          <Link to="/reports">
            <Button variant="outlined">View Reports</Button>
          </Link>
        </div>
      )}
    </div>
  );
};

export default SubscriptionsList;

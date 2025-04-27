import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { exportToCsv } from '../../utils/exportData';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import PaymentStats from '../../components/payments/PaymentStats';

interface Payment {
  id: string;
  subscription_id: string;
  subscription_name: string;
  amount: number;
  currency: string;
  payment_date: string;
  notes: string | null;
  created_at: string;
}

interface Meta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

const PaymentsList: React.FC = () => {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [meta, setMeta] = useState<Meta>({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState({
    subscription_id: '',
    from_date: '',
    to_date: '',
    search: '',
  });
  const [stats, setStats] = useState({
    totalSpent: 0,
    currency: 'USD',
    paymentCount: 0,
    averagePayment: 0,
    thisMonth: 0,
    lastMonth: 0
  });

  // Calculate payment statistics
  const calculateStats = (paymentData: Payment[] | undefined) => {
    // If paymentData is undefined or null, use an empty array
    const data = paymentData || [];

    // Get current month and last month
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    // Calculate total spent
    const totalSpent = data.reduce((sum, payment) => sum + payment.amount, 0);

    // Count payments
    const paymentCount = data.length;

    // Calculate average payment
    const averagePayment = paymentCount > 0 ? totalSpent / paymentCount : 0;

    // Calculate this month's payments
    const thisMonthPayments = data.filter(payment => {
      const paymentDate = new Date(payment.payment_date);
      return paymentDate.getMonth() === currentMonth && paymentDate.getFullYear() === currentYear;
    });

    const thisMonth = thisMonthPayments.reduce((sum, payment) => sum + payment.amount, 0);

    // Calculate last month's payments
    const lastMonthDate = new Date(currentYear, currentMonth - 1, 1);
    const lastMonthMonth = lastMonthDate.getMonth();
    const lastMonthYear = lastMonthDate.getFullYear();

    const lastMonthPayments = data.filter(payment => {
      const paymentDate = new Date(payment.payment_date);
      return paymentDate.getMonth() === lastMonthMonth && paymentDate.getFullYear() === lastMonthYear;
    });

    const lastMonth = lastMonthPayments.reduce((sum, payment) => sum + payment.amount, 0);

    // Determine most common currency
    const currency = data.length > 0 ? data[0].currency : 'USD';

    setStats({
      totalSpent,
      currency,
      paymentCount,
      averagePayment,
      thisMonth,
      lastMonth
    });
  };

  // Define fetchPayments function
  const fetchPayments = async (page = 1) => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        ...filters,
      });

      const response = await axios.get(`/api/payments?${params}`);
      setPayments(response.data.data);
      setMeta(response.data.meta);
      setError(null);

      // Calculate stats with the data
      calculateStats(response.data.data);
    } catch (err) {
      setError('Failed to load payments');
      console.error(err);

      // Mock data for development
      const mockData = [
        {
          id: '1',
          subscription_id: '1',
          subscription_name: 'Netflix',
          amount: 15.99,
          currency: 'USD',
          payment_date: '2023-10-15',
          notes: 'Monthly payment',
          created_at: '2023-10-15T10:00:00Z'
        },
        {
          id: '2',
          subscription_id: '2',
          subscription_name: 'Spotify',
          amount: 9.99,
          currency: 'USD',
          payment_date: '2023-10-20',
          notes: null,
          created_at: '2023-10-20T11:30:00Z'
        },
        {
          id: '3',
          subscription_id: '1',
          subscription_name: 'Netflix',
          amount: 15.99,
          currency: 'USD',
          payment_date: '2023-09-15',
          notes: 'Monthly payment',
          created_at: '2023-09-15T14:20:00Z'
        },
        {
          id: '4',
          subscription_id: '3',
          subscription_name: 'Adobe Creative Cloud',
          amount: 52.99,
          currency: 'USD',
          payment_date: '2023-10-05',
          notes: 'Monthly subscription',
          created_at: '2023-10-05T09:15:00Z'
        },
        {
          id: '5',
          subscription_id: '4',
          subscription_name: 'Gym Membership',
          amount: 29.99,
          currency: 'USD',
          payment_date: '2023-09-01',
          notes: 'Monthly gym fee',
          created_at: '2023-09-01T16:45:00Z'
        }
      ];

      setPayments(mockData);

      setMeta({
        current_page: 1,
        per_page: 10,
        total: 5,
        last_page: 1,
      });

      calculateStats(mockData);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPayments();
  }, [filters]);

  const handlePageChange = (page: number) => {
    fetchPayments(page);
  };

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    fetchPayments();
  };

  if (loading && payments?.length === 0) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <h1 className="text-3xl font-bold mb-2">Payment History</h1>
        <p className="text-gray-600 mb-8">View and analyze your payment history across all subscriptions.</p>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          {/* Skeleton loaders for stats cards */}
          {[...Array(4)].map((_, index) => (
            <Card key={index}>
              <div className="p-6">
                <div className="animate-pulse flex justify-between items-start">
                  <div className="space-y-3 w-2/3">
                    <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                    <div className="h-6 bg-gray-200 rounded w-3/4"></div>
                    <div className="h-3 bg-gray-200 rounded w-4/5"></div>
                  </div>
                  <div className="h-10 w-10 bg-gray-200 rounded-md"></div>
                </div>
              </div>
            </Card>
          ))}
        </div>

        <Card className="mb-6 border border-gray-200 shadow-sm">
          <div className="animate-pulse p-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              {[...Array(4)].map((_, index) => (
                <div key={index} className="space-y-2">
                  <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                  <div className="h-10 bg-gray-200 rounded w-full"></div>
                </div>
              ))}
            </div>
          </div>

          <div className="p-8 flex flex-col items-center justify-center">
            <div className="animate-pulse space-y-4 w-full max-w-3xl">
              <div className="h-8 bg-gray-200 rounded w-1/3"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
            </div>
            <p className="text-gray-500 mt-4">Loading payment data...</p>
          </div>
        </Card>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex flex-col space-y-4 mb-8">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 sm:mb-0">Payment History</h1>
          </div>

          <Button
            onClick={() => {
              // Prepare data for export
              const exportData = payments.map(payment => ({
                Subscription: payment.subscription_name,
                Amount: payment.amount,
                Currency: payment.currency,
                'Payment Date': formatDate(payment.payment_date),
                Notes: payment.notes || ''
              }));

              // Generate filename with current date
              const date = new Date().toISOString().split('T')[0];
              const filename = `payment-history-${date}.csv`;

              // Export to CSV
              exportToCsv(exportData, filename);
            }}
            variant="outlined"
            size="sm"
            disabled={!payments || payments.length === 0}
          >
            Export to CSV
          </Button>
        </div>

        <p className="text-gray-600">View and analyze your payment history across all subscriptions.</p>
      </div>

      <PaymentStats
        totalSpent={stats.totalSpent}
        currency={stats.currency}
        paymentCount={stats.paymentCount}
        averagePayment={stats.averagePayment}
        thisMonth={stats.thisMonth}
        lastMonth={stats.lastMonth}
      />

      <Card className="mb-6 border border-gray-200 shadow-sm">
        <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-t-lg border-b border-gray-200">
          <div>
            <label htmlFor="subscription_id" className="block text-sm font-medium text-gray-700 mb-1">
              Subscription
            </label>
            <select
              id="subscription_id"
              name="subscription_id"
              value={filters.subscription_id}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
            >
              <option value="">All Subscriptions</option>
              <option value="1">Netflix</option>
              <option value="2">Spotify</option>
              <option value="3">Adobe Creative Cloud</option>
              <option value="4">Gym Membership</option>
            </select>
          </div>

          <div>
            <label htmlFor="from_date" className="block text-sm font-medium text-gray-700 mb-1">
              From Date
            </label>
            <input
              type="date"
              id="from_date"
              name="from_date"
              value={filters.from_date}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
            />
          </div>

          <div>
            <label htmlFor="to_date" className="block text-sm font-medium text-gray-700 mb-1">
              To Date
            </label>
            <input
              type="date"
              id="to_date"
              name="to_date"
              value={filters.to_date}
              onChange={handleFilterChange}
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

        {!payments ? (
          <div className="p-8 flex flex-col items-center justify-center">
            <div className="animate-pulse space-y-4 w-full max-w-3xl">
              <div className="h-8 bg-gray-200 rounded w-1/3"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
            </div>
            <p className="text-gray-500 mt-4">Loading payment data...</p>
          </div>
        ) : payments.length === 0 ? (
          <div className="flex flex-col items-center justify-center p-10">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p className="text-lg font-medium text-gray-600 mb-1">No payment records found</p>
            <p className="text-gray-500 text-center mb-4">Adjust your filters or record a payment for a subscription to see payment history.</p>
            <Link to="/subscriptions">
              <Button size="sm">Go to Subscriptions</Button>
            </Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subscription
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Payment Date
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Notes
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {payments.map((payment) => (
                  <tr key={payment.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Link to={`/subscriptions/${payment.subscription_id}`} className="font-medium text-gray-900 hover:text-indigo-600">
                        {payment.subscription_name}
                      </Link>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap font-medium">
                      {formatCurrency(payment.amount, payment.currency)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {formatDate(payment.payment_date)}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      {payment.notes || '—'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        to={`/subscriptions/${payment.subscription_id}`}
                        className="text-indigo-600 hover:text-indigo-900"
                      >
                        View Subscription
                      </Link>
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

      {!loading && (!payments || payments.length === 0) && (
        <div className="mt-8 text-center">
          <p className="text-gray-500 mb-2">Need to record a payment?</p>
          <p className="text-gray-700 mb-4">Visit your subscriptions to record new payments.</p>
          <Link to="/subscriptions">
            <Button variant="outlined">View Subscriptions</Button>
          </Link>
        </div>
      )}
    </div>
  );
};

export default PaymentsList;

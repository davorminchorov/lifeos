import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { exportToCsv } from '../../utils/exportData';
import Button from '../../ui/Button/Button';
import Card from '../../ui/Card/Card';
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
  const calculateStats = (paymentData: Payment[]) => {
    // Get current month and last month
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    // Calculate total spent
    const totalSpent = paymentData.reduce((sum, payment) => sum + payment.amount, 0);

    // Count payments
    const paymentCount = paymentData.length;

    // Calculate average payment
    const averagePayment = paymentCount > 0 ? totalSpent / paymentCount : 0;

    // Calculate this month's payments
    const thisMonthPayments = paymentData.filter(payment => {
      const paymentDate = new Date(payment.payment_date);
      return paymentDate.getMonth() === currentMonth && paymentDate.getFullYear() === currentYear;
    });

    const thisMonth = thisMonthPayments.reduce((sum, payment) => sum + payment.amount, 0);

    // Calculate last month's payments
    const lastMonthDate = new Date(currentYear, currentMonth - 1, 1);
    const lastMonthMonth = lastMonthDate.getMonth();
    const lastMonthYear = lastMonthDate.getFullYear();

    const lastMonthPayments = paymentData.filter(payment => {
      const paymentDate = new Date(payment.payment_date);
      return paymentDate.getMonth() === lastMonthMonth && paymentDate.getFullYear() === lastMonthYear;
    });

    const lastMonth = lastMonthPayments.reduce((sum, payment) => sum + payment.amount, 0);

    // Determine most common currency
    const currency = paymentData.length > 0 ? paymentData[0].currency : 'USD';

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

  if (loading && payments.length === 0) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex justify-center items-center h-64">
          <svg className="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Payment History</h1>

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
          variant="outline"
          size="sm"
          disabled={payments.length === 0}
        >
          Export to CSV
        </Button>
      </div>

      <PaymentStats
        totalSpent={stats.totalSpent}
        currency={stats.currency}
        paymentCount={stats.paymentCount}
        averagePayment={stats.averagePayment}
        thisMonth={stats.thisMonth}
        lastMonth={stats.lastMonth}
      />

      <Card className="mb-6">
        <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4">
          <div>
            <label htmlFor="subscription_id" className="block text-sm font-medium text-gray-700 mb-1">
              Subscription
            </label>
            <select
              id="subscription_id"
              name="subscription_id"
              value={filters.subscription_id}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2"
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
              className="w-full border border-gray-300 rounded-md shadow-sm p-2"
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

      {payments.length === 0 ? (
        <Card>
          <div className="p-6 text-center">
            <p className="text-gray-500">No payment records found.</p>
          </div>
        </Card>
      ) : (
        <>
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white rounded-lg overflow-hidden">
              <thead className="bg-gray-100">
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
              <tbody className="divide-y divide-gray-200">
                {payments.map((payment) => (
                  <tr key={payment.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <Link to={`/subscriptions/${payment.subscription_id}`} className="text-indigo-600 hover:text-indigo-900">
                        {payment.subscription_name}
                      </Link>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {formatCurrency(payment.amount, payment.currency)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {formatDate(payment.payment_date)}
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

export default PaymentsList;

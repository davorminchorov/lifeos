import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { exportToCsv } from '../../utils/exportData';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../../ui/Card';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { CreditCard, Download, Filter, Calendar, ArrowUpRight, PlusCircle } from 'lucide-react';

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

  const exportData = () => {
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
  };

  const monthlyChange = stats.thisMonth - stats.lastMonth;
  const percentChange = stats.lastMonth > 0 ? (monthlyChange / stats.lastMonth) * 100 : 0;

  if (loading && payments?.length === 0) {
    return (
      <PageContainer title="Payment History">
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      </PageContainer>
    );
  }

  return (
    <PageContainer
      title="Payment History"
      subtitle="View and analyze your payment history across all subscriptions"
      actions={
        <Button
          variant="outlined"
          onClick={exportData}
          disabled={!payments || payments.length === 0}
          icon={<Download className="h-4 w-4 mr-2" />}
        >
          Export to CSV
        </Button>
      }
    >
      <PageSection>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {/* Total Spent Card */}
          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-primary/10 rounded-full">
                    <CreditCard className="h-6 w-6 text-primary" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">Total Spent</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {formatCurrency(stats.totalSpent, stats.currency)}
                    </p>
                  </div>
                </div>
                <ArrowUpRight className="h-5 w-5 text-primary" />
              </div>
            </CardContent>
          </Card>

          {/* Payment Count Card */}
          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-secondary/10 rounded-full">
                    <CreditCard className="h-6 w-6 text-secondary" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">Payments Made</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {stats.paymentCount}
                    </p>
                  </div>
                </div>
                <ArrowUpRight className="h-5 w-5 text-secondary" />
              </div>
            </CardContent>
          </Card>

          {/* Average Payment Card */}
          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-tertiary/10 rounded-full">
                    <Calendar className="h-6 w-6 text-tertiary" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">Average Payment</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {formatCurrency(stats.averagePayment, stats.currency)}
                    </p>
                  </div>
                </div>
                <ArrowUpRight className="h-5 w-5 text-tertiary" />
              </div>
            </CardContent>
          </Card>

          {/* This Month Card */}
          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-success/10 rounded-full">
                    <Calendar className="h-6 w-6 text-success" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">This Month</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {formatCurrency(stats.thisMonth, stats.currency)}
                    </p>
                  </div>
                </div>
                <div className={`text-xs font-medium ${monthlyChange >= 0 ? 'text-success' : 'text-error'}`}>
                  {monthlyChange >= 0 ? '+' : ''}{percentChange.toFixed(1)}%
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </PageSection>

      <PageSection>
        <Card variant="elevated">
          <CardHeader>
            <CardTitle>Filter Payments</CardTitle>
            <CardDescription>Filter your payment history by subscription and date range</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label htmlFor="subscription_id" className="block text-sm font-medium text-on-surface-variant mb-1">
                  Subscription
                </label>
                <select
                  id="subscription_id"
                  name="subscription_id"
                  value={filters.subscription_id}
                  onChange={handleFilterChange}
                  className="block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                  <option value="">All Subscriptions</option>
                  <option value="1">Netflix</option>
                  <option value="2">Spotify</option>
                  <option value="3">Adobe Creative Cloud</option>
                  <option value="4">Gym Membership</option>
                </select>
              </div>

              <div>
                <label htmlFor="from_date" className="block text-sm font-medium text-on-surface-variant mb-1">
                  From Date
                </label>
                <input
                  type="date"
                  id="from_date"
                  name="from_date"
                  value={filters.from_date}
                  onChange={handleFilterChange}
                  className="block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                />
              </div>

              <div>
                <label htmlFor="to_date" className="block text-sm font-medium text-on-surface-variant mb-1">
                  To Date
                </label>
                <input
                  type="date"
                  id="to_date"
                  name="to_date"
                  value={filters.to_date}
                  onChange={handleFilterChange}
                  className="block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                />
              </div>

              <div className="flex items-end">
                <Button
                  type="submit"
                  variant="filled"
                  className="w-full"
                  icon={<Filter className="h-4 w-4 mr-2" />}
                >
                  Filter
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </PageSection>

      <PageSection>
        <Card variant="elevated">
          <CardHeader>
            <CardTitle>Payment History</CardTitle>
            <CardDescription>Your payment records across all subscriptions</CardDescription>
          </CardHeader>
          <CardContent>
            {error && (
              <div className="bg-error/10 text-error p-4 rounded-lg mb-4">
                {error}
              </div>
            )}

            {!payments ? (
              <div className="p-8 flex flex-col items-center justify-center">
                <div className="animate-pulse space-y-4 w-full max-w-3xl">
                  <div className="h-8 bg-surface-variant rounded w-1/3"></div>
                  <div className="h-8 bg-surface-variant rounded w-full"></div>
                  <div className="h-8 bg-surface-variant rounded w-full"></div>
                  <div className="h-8 bg-surface-variant rounded w-full"></div>
                  <div className="h-8 bg-surface-variant rounded w-full"></div>
                </div>
                <p className="text-on-surface-variant mt-4">Loading payment data...</p>
              </div>
            ) : payments.length === 0 ? (
              <div className="flex flex-col items-center justify-center p-10">
                <CreditCard className="h-16 w-16 text-on-surface-variant/20 mb-4" />
                <p className="text-headline-small text-on-surface font-medium mb-1">No payment records found</p>
                <p className="text-body-medium text-on-surface-variant text-center mb-4">
                  Adjust your filters or record a payment for a subscription to see payment history.
                </p>
                <Link to="/subscriptions">
                  <Button
                    variant="filled"
                    icon={<PlusCircle className="h-4 w-4 mr-2" />}
                  >
                    Go to Subscriptions
                  </Button>
                </Link>
              </div>
            ) : (
              <div>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Subscription</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Payment Date</TableHead>
                      <TableHead>Notes</TableHead>
                      <TableHead className="text-right">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {payments.map((payment) => (
                      <TableRow key={payment.id}>
                        <TableCell>
                          <Link
                            to={`/subscriptions/${payment.subscription_id}`}
                            className="font-medium text-on-surface hover:text-primary"
                          >
                            {payment.subscription_name}
                          </Link>
                        </TableCell>
                        <TableCell className="font-medium">
                          {formatCurrency(payment.amount, payment.currency)}
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center">
                            <Calendar className="h-4 w-4 text-on-surface-variant mr-1" />
                            {formatDate(payment.payment_date)}
                          </div>
                        </TableCell>
                        <TableCell>
                          {payment.notes || <span className="text-on-surface-variant">—</span>}
                        </TableCell>
                        <TableCell className="text-right">
                          <Link
                            to={`/subscriptions/${payment.subscription_id}`}
                            className="text-primary hover:text-primary/80"
                          >
                            View Subscription
                          </Link>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>

                {/* Pagination */}
                {meta && meta.last_page > 1 && (
                  <div className="flex justify-center mt-6">
                    <nav className="flex items-center">
                      <Button
                        variant="text"
                        onClick={() => handlePageChange(meta.current_page - 1)}
                        disabled={meta.current_page === 1}
                        className="mr-2"
                      >
                        Previous
                      </Button>

                      {[...Array(meta.last_page)].map((_, i) => (
                        <Button
                          key={i}
                          variant={meta.current_page === i + 1 ? "filled" : "text"}
                          onClick={() => handlePageChange(i + 1)}
                          className="mx-1"
                        >
                          {i + 1}
                        </Button>
                      ))}

                      <Button
                        variant="text"
                        onClick={() => handlePageChange(meta.current_page + 1)}
                        disabled={meta.current_page === meta.last_page}
                        className="ml-2"
                      >
                        Next
                      </Button>
                    </nav>
                  </div>
                )}
              </div>
            )}
          </CardContent>
        </Card>
      </PageSection>
    </PageContainer>
  );
};

export default PaymentsList;

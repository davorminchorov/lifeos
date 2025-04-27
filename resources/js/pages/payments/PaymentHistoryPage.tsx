import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { Download, CreditCard, Calendar, ArrowUpRight, TrendingUp, Filter, ExternalLink } from 'lucide-react';
import { Button } from '../../ui/Button/Button';
import { Card, CardContent } from '../../ui/Card';
import PageTitle from '../../components/common/PageTitle';
import PageLayout from '../../components/common/PageLayout';
import Skeleton from '../../ui/Skeleton';
import { formatCurrency } from '../../utils/format';
import { useToast } from '../../ui/Toast';

interface Payment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  payment_method: string;
  subscription_name: string;
  category: string;
  notes?: string;
}

interface PaymentSummary {
  total_spent: number;
  payments_count: number;
  average_payment: number;
  this_month: number;
  previous_month: number;
}

export default function PaymentHistoryPage() {
  const [payments, setPayments] = useState<Payment[]>([]);
  const [summary, setSummary] = useState<PaymentSummary>({
    total_spent: 0,
    payments_count: 0,
    average_payment: 0,
    this_month: 0,
    previous_month: 0
  });
  const [loading, setLoading] = useState(true);
  const [subscriptions, setSubscriptions] = useState<{ id: string; name: string }[]>([]);
  const [filters, setFilters] = useState({
    subscription_id: 'all',
    from_date: '',
    to_date: ''
  });
  const { toast } = useToast();

  useEffect(() => {
    fetchPaymentData();
    fetchSubscriptions();
  }, []);

  const fetchPaymentData = async () => {
    setLoading(true);
    try {
      const response = await axios.get('/api/payment-history', { params: filters });
      setPayments(response.data.payments || []);
      setSummary(response.data.summary || {
        total_spent: 0,
        payments_count: 0,
        average_payment: 0,
        this_month: 0,
        previous_month: 0
      });
    } catch (error) {
      console.error('Error fetching payment history:', error);
      toast({
        title: 'Error',
        description: 'Failed to load payment history data',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchSubscriptions = async () => {
    try {
      const response = await axios.get('/api/subscriptions/list');
      setSubscriptions(response.data.subscriptions || []);
    } catch (error) {
      console.error('Error fetching subscriptions:', error);
    }
  };

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const handleFilter = () => {
    fetchPaymentData();
  };

  const exportToCSV = async () => {
    try {
      const response = await axios.get('/api/payment-history/export', {
        params: filters,
        responseType: 'blob'
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', 'payment_history.csv');
      document.body.appendChild(link);
      link.click();
      link.remove();

      toast({
        title: 'Success',
        description: 'Payment history exported successfully',
        variant: 'default'
      });
    } catch (error) {
      console.error('Error exporting payment history:', error);
      toast({
        title: 'Error',
        description: 'Failed to export payment history',
        variant: 'destructive'
      });
    }
  };

  const calculatePercentageChange = () => {
    if (!summary.previous_month) return 0;
    return ((summary.this_month - summary.previous_month) / summary.previous_month) * 100;
  };

  const percentChange = calculatePercentageChange();
  const percentChangeFormatted = isNaN(percentChange) || !isFinite(percentChange)
    ? '+0.0%'
    : `${percentChange > 0 ? '+' : ''}${percentChange.toFixed(1)}%`;

  return (
    <PageLayout>
      <div className="max-w-7xl mx-auto">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
          <PageTitle
            title="Payment History"
            description="View and analyze your payment history across all subscriptions"
            icon={<CreditCard className="h-8 w-8" />}
          />
          <Button
            onClick={exportToCSV}
            className="bg-surface-variant text-on-surface-variant shadow-elevation-1 hover:shadow-elevation-2 self-start md:self-auto"
          >
            <Download className="h-4 w-4 mr-2" />
            Export to CSV
          </Button>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card className="bg-surface shadow-elevation-1 border border-outline/40">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div>
                  <p className="text-label-medium text-on-surface-variant mb-1">Total Spent</p>
                  <p className="text-headline-medium text-on-surface font-medium">
                    {loading ? (
                      <Skeleton className="h-8 w-24" />
                    ) : (
                      formatCurrency(summary.total_spent, 'USD')
                    )}
                  </p>
                  <p className="text-body-small text-on-surface-variant mt-1">Lifetime payment total</p>
                </div>
                <div className="p-2 bg-primary-container text-on-primary-container rounded-full">
                  <CreditCard className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-surface shadow-elevation-1 border border-outline/40">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div>
                  <p className="text-label-medium text-on-surface-variant mb-1">Payments Made</p>
                  <p className="text-headline-medium text-on-surface font-medium">
                    {loading ? (
                      <Skeleton className="h-8 w-16" />
                    ) : (
                      summary.payments_count
                    )}
                  </p>
                  <p className="text-body-small text-on-surface-variant mt-1">Total number of payments</p>
                </div>
                <div className="p-2 bg-secondary-container text-on-secondary-container rounded-full">
                  <Calendar className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-surface shadow-elevation-1 border border-outline/40">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div>
                  <p className="text-label-medium text-on-surface-variant mb-1">Average Payment</p>
                  <p className="text-headline-medium text-on-surface font-medium">
                    {loading ? (
                      <Skeleton className="h-8 w-24" />
                    ) : (
                      formatCurrency(summary.average_payment, 'USD')
                    )}
                  </p>
                  <p className="text-body-small text-on-surface-variant mt-1">Average payment amount</p>
                </div>
                <div className="p-2 bg-tertiary-container text-on-tertiary-container rounded-full">
                  <ArrowUpRight className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-surface shadow-elevation-1 border border-outline/40">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div>
                  <p className="text-label-medium text-on-surface-variant mb-1">This Month</p>
                  <p className="text-headline-medium text-on-surface font-medium">
                    {loading ? (
                      <Skeleton className="h-8 w-24" />
                    ) : (
                      formatCurrency(summary.this_month, 'USD')
                    )}
                  </p>
                  <p className={`text-body-small mt-1 ${percentChange > 0 ? 'text-error' : percentChange < 0 ? 'text-tertiary' : 'text-on-surface-variant'}`}>
                    {percentChangeFormatted} vs last month
                  </p>
                </div>
                <div className="p-2 bg-error-container text-on-error-container rounded-full">
                  <TrendingUp className="h-5 w-5" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card className="mb-8 bg-surface shadow-elevation-1 border border-outline/40">
          <CardContent className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label htmlFor="subscription" className="block text-label-medium text-on-surface-variant mb-2">
                  Subscription
                </label>
                <select
                  id="subscription"
                  name="subscription_id"
                  value={filters.subscription_id}
                  onChange={handleFilterChange}
                  className="w-full p-2 rounded-md border border-outline bg-surface-variant text-on-surface-variant"
                >
                  <option value="all">All Subscriptions</option>
                  {subscriptions.map(sub => (
                    <option key={sub.id} value={sub.id}>{sub.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label htmlFor="from_date" className="block text-label-medium text-on-surface-variant mb-2">
                  From Date
                </label>
                <input
                  type="date"
                  id="from_date"
                  name="from_date"
                  value={filters.from_date}
                  onChange={handleFilterChange}
                  className="w-full p-2 rounded-md border border-outline bg-surface-variant text-on-surface-variant"
                />
              </div>

              <div>
                <label htmlFor="to_date" className="block text-label-medium text-on-surface-variant mb-2">
                  To Date
                </label>
                <input
                  type="date"
                  id="to_date"
                  name="to_date"
                  value={filters.to_date}
                  onChange={handleFilterChange}
                  className="w-full p-2 rounded-md border border-outline bg-surface-variant text-on-surface-variant"
                />
              </div>

              <div className="flex items-end">
                <Button
                  onClick={handleFilter}
                  className="bg-primary text-on-primary shadow-elevation-1 hover:shadow-elevation-2 w-full"
                >
                  <Filter className="h-4 w-4 mr-2" />
                  Filter
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Payment History Table */}
        <Card className="bg-surface shadow-elevation-1 border border-outline/40">
          <CardContent className="p-0">
            {loading ? (
              <div className="p-8 flex flex-col items-center justify-center">
                <div className="space-y-3 w-full max-w-2xl">
                  <Skeleton className="h-8 w-32 mx-auto mb-4" />
                  <Skeleton className="h-12 w-full" />
                  <Skeleton className="h-12 w-full" />
                  <Skeleton className="h-12 w-full" />
                  <Skeleton className="h-12 w-full" />
                  <Skeleton className="h-12 w-full" />
                </div>
                <p className="text-body-medium text-on-surface-variant mt-4">Loading payment data...</p>
              </div>
            ) : payments.length === 0 ? (
              <div className="p-8 text-center">
                <div className="py-12 flex flex-col items-center justify-center border-2 border-dashed border-outline/40 rounded-lg">
                  <div className="p-3 rounded-full bg-surface-variant mb-4">
                    <CreditCard className="h-8 w-8 text-on-surface-variant/40" />
                  </div>
                  <h3 className="text-headline-small text-on-surface font-medium mb-2">No payment records found</h3>
                  <p className="text-body-medium text-on-surface-variant max-w-md mb-6">
                    Need to record a payment? Visit your subscriptions to record new payments.
                  </p>
                  <Link to="/subscriptions">
                    <Button className="bg-primary text-on-primary shadow-elevation-1 hover:shadow-elevation-2">
                      <ExternalLink className="h-4 w-4 mr-2" />
                      View Subscriptions
                    </Button>
                  </Link>
                </div>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-surface-variant">
                    <tr>
                      <th className="px-6 py-3 text-left text-label-large font-medium text-on-surface-variant">Date</th>
                      <th className="px-6 py-3 text-left text-label-large font-medium text-on-surface-variant">Subscription</th>
                      <th className="px-6 py-3 text-left text-label-large font-medium text-on-surface-variant">Method</th>
                      <th className="px-6 py-3 text-left text-label-large font-medium text-on-surface-variant">Category</th>
                      <th className="px-6 py-3 text-right text-label-large font-medium text-on-surface-variant">Amount</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-outline/40">
                    {payments.map((payment) => (
                      <tr key={payment.id} className="hover:bg-surface-variant/30">
                        <td className="px-6 py-4 text-body-medium text-on-surface">
                          {new Date(payment.payment_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                          })}
                        </td>
                        <td className="px-6 py-4 text-body-medium text-on-surface">{payment.subscription_name}</td>
                        <td className="px-6 py-4 text-body-medium text-on-surface">
                          {payment.payment_method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}
                        </td>
                        <td className="px-6 py-4 text-body-medium text-on-surface">{payment.category}</td>
                        <td className="px-6 py-4 text-body-medium text-on-surface text-right font-medium">
                          {formatCurrency(payment.amount, payment.currency)}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </PageLayout>
  );
}

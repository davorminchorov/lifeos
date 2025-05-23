import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Download, CreditCard, Calendar, ArrowUpRight, TrendingUp, Filter, ExternalLink, AlertCircle } from 'lucide-react';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../../ui/Card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { Badge } from '../../ui/Badge';
import { formatCurrency } from '../../utils/format';
import { useToast } from '../../ui/Toast';
import { usePaymentStore } from '../../store/paymentStore';
import { usePaymentHistory, useSubscriptionsList, useExportPaymentHistory } from '../../queries/paymentQueries';

export default function PaymentHistoryPage() {
  const { toast } = useToast();

  // Get state and actions from store
  const [state, actions] = usePaymentStore();
  const { filters, exportStatus } = state;

  // Use React Query hooks
  const {
    data: paymentData,
    isLoading,
    error: queryError
  } = usePaymentHistory(filters);

  const { data: subscriptions = [] } = useSubscriptionsList();

  const exportMutation = useExportPaymentHistory();

  // Update store when data is fetched
  useEffect(() => {
    if (paymentData) {
      actions.setPayments(paymentData.payments || []);
      actions.setSummary(paymentData.summary || {
        total_spent: 0,
        payments_count: 0,
        average_payment: 0,
        this_month: 0,
        previous_month: 0
      });
    }
  }, [paymentData, actions]);

  // Update subscriptions in store
  useEffect(() => {
    if (subscriptions.length > 0) {
      actions.setSubscriptions(subscriptions);
    }
  }, [subscriptions, actions]);

  // Handle query error
  useEffect(() => {
    if (queryError) {
      console.error('Error fetching payment history:', queryError);
      toast({
        title: "Error",
        description: "Failed to load payment history. Please try again.",
        variant: "destructive",
      });
      actions.setError('Failed to load payment history. Please try again.');
    }
  }, [queryError, toast, actions]);

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    actions.updateFilter({ name, value });
  };

  const exportToCSV = async () => {
    actions.setExportStatus('loading');

    exportMutation.mutate(filters, {
      onSuccess: (data) => {
        const url = window.URL.createObjectURL(new Blob([data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'payment_history.csv');
        document.body.appendChild(link);
        link.click();
        link.remove();

        actions.setExportStatus('success');

        // Reset status after 3 seconds
        setTimeout(() => {
          actions.setExportStatus('idle');
        }, 3000);
      },
      onError: (error) => {
        console.error('Error exporting payment history:', error);
        actions.setExportStatus('error');

        // Reset status after 3 seconds
        setTimeout(() => {
          actions.setExportStatus('idle');
        }, 3000);
      }
    });
  };

  const calculatePercentageChange = () => {
    const { this_month, previous_month } = state.summary;
    if (!previous_month) return 0;
    return ((this_month - previous_month) / previous_month) * 100;
  };

  const percentChange = calculatePercentageChange();
  const percentChangeFormatted = isNaN(percentChange) || !isFinite(percentChange)
    ? '+0.0%'
    : `${percentChange > 0 ? '+' : ''}${percentChange.toFixed(1)}%`;

  if (isLoading) {
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
          onClick={exportToCSV}
          disabled={exportStatus === 'loading'}
          icon={<Download className="h-4 w-4 mr-2" />}
        >
          {exportStatus === 'loading' ? 'Exporting...' :
           exportStatus === 'success' ? 'Exported!' :
           exportStatus === 'error' ? 'Failed' :
           'Export to CSV'}
        </Button>
      }
    >
      {state.error && (
        <div className="mb-6 p-4 bg-error/10 text-error rounded-lg flex items-center space-x-2">
          <AlertCircle className="h-5 w-5" />
          <span>{state.error}</span>
        </div>
      )}

      {/* Summary Cards */}
      <PageSection>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-primary/10 rounded-full">
                    <CreditCard className="h-6 w-6 text-primary" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">Total Spent</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {formatCurrency(state.summary.total_spent, 'USD')}
                    </p>
                  </div>
                </div>
                <ArrowUpRight className="h-5 w-5 text-primary" />
              </div>
              <p className="text-body-small text-on-surface-variant mt-2 ml-12">Lifetime payment total</p>
            </CardContent>
          </Card>

          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-secondary/10 rounded-full">
                    <Calendar className="h-6 w-6 text-secondary" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">Payments Made</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {state.summary.payments_count}
                    </p>
                  </div>
                </div>
                <ArrowUpRight className="h-5 w-5 text-secondary" />
              </div>
              <p className="text-body-small text-on-surface-variant mt-2 ml-12">Total number of payments</p>
            </CardContent>
          </Card>

          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-tertiary/10 rounded-full">
                    <ArrowUpRight className="h-6 w-6 text-tertiary" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">Average Payment</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {formatCurrency(state.summary.average_payment, 'USD')}
                    </p>
                  </div>
                </div>
                <ArrowUpRight className="h-5 w-5 text-tertiary" />
              </div>
              <p className="text-body-small text-on-surface-variant mt-2 ml-12">Average payment amount</p>
            </CardContent>
          </Card>

          <Card variant="elevated">
            <CardContent className="p-6">
              <div className="flex justify-between items-start">
                <div className="flex items-center space-x-4">
                  <div className="flex-shrink-0 p-3 bg-error/10 rounded-full">
                    <TrendingUp className="h-6 w-6 text-error" />
                  </div>
                  <div>
                    <p className="text-on-surface-variant text-sm mb-1">This Month</p>
                    <p className="text-on-surface text-2xl font-bold">
                      {formatCurrency(state.summary.this_month, 'USD')}
                    </p>
                  </div>
                </div>
                <div className={`text-xs font-medium ${percentChange > 0 ? 'text-error' : 'text-tertiary'}`}>
                  {percentChangeFormatted}
                </div>
              </div>
              <p className={`text-body-small mt-2 ml-12 ${percentChange > 0 ? 'text-error' : percentChange < 0 ? 'text-tertiary' : 'text-on-surface-variant'}`}>
                vs last month
              </p>
            </CardContent>
          </Card>
        </div>
      </PageSection>

      {/* Filters */}
      <PageSection>
        <Card variant="elevated">
          <CardHeader>
            <CardTitle>Filter Payments</CardTitle>
            <CardDescription>Filter your payment history by subscription and date range</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label htmlFor="subscription" className="block text-sm font-medium text-on-surface-variant mb-2">
                  Subscription
                </label>
                <select
                  id="subscription"
                  name="subscription_id"
                  value={filters.subscription_id}
                  onChange={handleFilterChange}
                  className="block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                  <option value="all">All Subscriptions</option>
                  {state.subscriptions.map(sub => (
                    <option key={sub.id} value={sub.id}>{sub.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label htmlFor="from_date" className="block text-sm font-medium text-on-surface-variant mb-2">
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
                <label htmlFor="to_date" className="block text-sm font-medium text-on-surface-variant mb-2">
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
                  onClick={() => {
                    // refetch data with current filters
                    // the query key will handle the refetch for us
                  }}
                  variant="filled"
                  className="w-full"
                  icon={<Filter className="h-4 w-4 mr-2" />}
                >
                  Filter
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </PageSection>

      {/* Payment History Table */}
      <PageSection>
        <Card variant="elevated">
          <CardHeader>
            <CardTitle>Payment History</CardTitle>
            <CardDescription>Your payment records across all subscriptions</CardDescription>
          </CardHeader>
          <CardContent>
            {state.payments.length === 0 ? (
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
                    <Button
                      variant="filled"
                      icon={<ExternalLink className="h-4 w-4 mr-2" />}
                    >
                      View Subscriptions
                    </Button>
                  </Link>
                </div>
              </div>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Date</TableHead>
                    <TableHead>Subscription</TableHead>
                    <TableHead>Method</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead className="text-right">Amount</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {state.payments.map((payment) => (
                    <TableRow key={payment.id}>
                      <TableCell>
                        {new Date(payment.payment_date).toLocaleDateString('en-US', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric',
                        })}
                      </TableCell>
                      <TableCell>{payment.subscription_name}</TableCell>
                      <TableCell>
                        <Badge variant="outline">
                          {payment.payment_method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <Badge variant="secondary">
                          {payment.category}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right font-medium">
                        {formatCurrency(payment.amount, payment.currency)}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
      </PageSection>

      {/* Export Status Notifications - Simple inline alternative to toast system */}
      {exportStatus === 'success' && (
        <div className="fixed bottom-4 right-4 bg-success text-on-success px-4 py-2 rounded-md shadow-lg flex items-center space-x-2">
          <Download className="h-4 w-4" />
          <span>Export completed successfully!</span>
        </div>
      )}

      {exportStatus === 'error' && (
        <div className="fixed bottom-4 right-4 bg-error text-on-error px-4 py-2 rounded-md shadow-lg flex items-center space-x-2">
          <AlertCircle className="h-4 w-4" />
          <span>Export failed. Please try again.</span>
        </div>
      )}
    </PageContainer>
  );
}

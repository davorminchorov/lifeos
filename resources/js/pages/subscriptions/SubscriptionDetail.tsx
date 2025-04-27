import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import PaymentHistoryCard from '../../components/subscriptions/PaymentHistoryCard';
import PaymentSummaryCard from '../../components/subscriptions/PaymentSummaryCard';
import RecordPaymentModal, { RecordPaymentFormData } from '../../components/subscriptions/RecordPaymentModal';

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
  payments: SubscriptionPayment[];
  total_paid: number;
}

interface SubscriptionPayment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  notes: string | null;
  created_at: string;
}

const SubscriptionDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [subscription, setSubscription] = useState<Subscription | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showCancelModal, setShowCancelModal] = useState(false);
  const [cancelDate, setCancelDate] = useState(new Date().toISOString().split('T')[0]);
  const [isCancelling, setIsCancelling] = useState(false);
  const [cancelError, setCancelError] = useState<string | null>(null);

  // Record payment modal state
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [isRecordingPayment, setIsRecordingPayment] = useState(false);
  const [paymentError, setPaymentError] = useState<string | null>(null);

  useEffect(() => {
    fetchSubscription();
  }, [id]);

  const fetchSubscription = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`/api/subscriptions/${id}`);
      setSubscription(response.data);
      setError(null);
    } catch (err) {
      setError('Failed to load subscription details');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleCancelSubscription = async () => {
    setIsCancelling(true);
    setCancelError(null);

    try {
      await axios.post(`/api/subscriptions/${id}/cancel`, {
        end_date: cancelDate,
      });
      setShowCancelModal(false);
      fetchSubscription(); // Refresh data
    } catch (err: any) {
      setCancelError(err.response?.data?.error || 'Failed to cancel subscription');
      console.error(err);
    } finally {
      setIsCancelling(false);
    }
  };

  const handleRecordPayment = async (paymentData: RecordPaymentFormData) => {
    setIsRecordingPayment(true);
    setPaymentError(null);

    try {
      await axios.post(`/api/subscriptions/${id}/payments`, paymentData);
      setShowPaymentModal(false);
      fetchSubscription(); // Refresh data
    } catch (err: any) {
      setPaymentError(err.response?.data?.error || 'Failed to record payment');
      console.error(err);
    } finally {
      setIsRecordingPayment(false);
    }
  };

  const renderStatusBadge = (status: string) => {
    let className = '';

    switch (status) {
      case 'active':
        className = 'bg-tertiary-container text-on-tertiary-container';
        break;
      case 'cancelled':
        className = 'bg-error-container text-on-error-container';
        break;
      case 'paused':
        className = 'bg-secondary-container text-on-secondary-container';
        break;
      default:
        className = 'bg-surface-variant text-on-surface-variant';
    }

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${className}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  if (error || !subscription) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="bg-error-container border border-error text-on-error-container px-4 py-3 rounded">
          {error || 'Subscription not found'}
        </div>
        <div className="mt-4">
          <Button onClick={() => navigate('/subscriptions')} variant="filled">Back to subscriptions</Button>
        </div>
      </div>
    );
  }

  // Prepare data for payment history component
  const paymentHistoryData = subscription.payments.map(payment => ({
    ...payment,
    currency: subscription.currency  // Ensure currency is passed to each payment
  }));

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-on-surface">{subscription.name}</h1>
        <div className="flex space-x-3">
          <Link to={`/subscriptions/${id}/edit`}>
            <Button variant="outlined">Edit</Button>
          </Link>
          {subscription.status === 'active' && (
            <Button
              variant="outlined"
              className="text-error border-error hover:bg-error-container/10"
              onClick={() => setShowCancelModal(true)}
            >
              Cancel Subscription
            </Button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <Card variant="elevated" className="md:col-span-2">
          <CardHeader>
            <CardTitle>Subscription Details</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <div>
                  <p className="text-sm text-on-surface-variant">Status</p>
                  <p className="mt-1">{renderStatusBadge(subscription.status)}</p>
                </div>

                <div>
                  <p className="text-sm text-on-surface-variant">Description</p>
                  <p className="mt-1 text-on-surface">{subscription.description}</p>
                </div>

                <div>
                  <p className="text-sm text-on-surface-variant">Amount</p>
                  <p className="mt-1 font-semibold text-on-surface">
                    {formatCurrency(subscription.amount, subscription.currency)}
                  </p>
                </div>

                <div>
                  <p className="text-sm text-on-surface-variant">Billing Cycle</p>
                  <p className="mt-1 text-on-surface">{subscription.billing_cycle.charAt(0).toUpperCase() + subscription.billing_cycle.slice(1)}</p>
                </div>
              </div>

              <div className="space-y-4">
                <div>
                  <p className="text-sm text-on-surface-variant">Category</p>
                  <p className="mt-1 text-on-surface">
                    {subscription.category
                      ? subscription.category.charAt(0).toUpperCase() + subscription.category.slice(1)
                      : 'Not categorized'}
                  </p>
                </div>

                <div>
                  <p className="text-sm text-on-surface-variant">Start Date</p>
                  <p className="mt-1 text-on-surface">{new Date(subscription.start_date).toLocaleDateString()}</p>
                </div>

                {subscription.end_date && (
                  <div>
                    <p className="text-sm text-on-surface-variant">End Date</p>
                    <p className="mt-1 text-on-surface">{new Date(subscription.end_date).toLocaleDateString()}</p>
                  </div>
                )}

                <div>
                  <p className="text-sm text-on-surface-variant">Next Payment</p>
                  <p className="mt-1 text-on-surface">
                    {subscription.next_payment_date
                      ? new Date(subscription.next_payment_date).toLocaleDateString()
                      : 'Not scheduled'}
                  </p>
                </div>

                {subscription.website && (
                  <div>
                    <p className="text-sm text-on-surface-variant">Website</p>
                    <a
                      href={subscription.website}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="mt-1 block text-primary hover:text-primary/80"
                    >
                      {subscription.website}
                    </a>
                  </div>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        <Card variant="filled">
          <CardHeader>
            <CardTitle>Payment Summary</CardTitle>
          </CardHeader>
          <CardContent>
            <PaymentSummaryCard
              amount={subscription.amount}
              currency={subscription.currency}
              billingCycle={subscription.billing_cycle}
              nextPaymentDate={subscription.next_payment_date}
              totalPaid={subscription.total_paid || 0}
              startDate={subscription.start_date}
              paymentCount={subscription.payments?.length || 0}
            />
            <div className="mt-6">
              <Button
                variant="tonal"
                onClick={() => setShowPaymentModal(true)}
                className="w-full"
                disabled={subscription.status !== 'active'}
              >
                Record Payment
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      {subscription.payments && subscription.payments.length > 0 && (
        <Card variant="elevated" className="mb-6">
          <CardHeader>
            <CardTitle>Payment History</CardTitle>
          </CardHeader>
          <CardContent>
            <PaymentHistoryCard payments={paymentHistoryData} currency={subscription.currency} />
          </CardContent>
        </Card>
      )}

      {/* Cancel Subscription Modal */}
      {showCancelModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-surface rounded-lg shadow-elevation-3 max-w-md w-full">
            <div className="p-6">
              <h3 className="text-lg font-medium text-on-surface mb-4">Cancel Subscription</h3>

              {cancelError && (
                <div className="mb-4 p-3 bg-error-container text-on-error-container rounded">
                  {cancelError}
                </div>
              )}

              <p className="text-on-surface-variant mb-4">
                Are you sure you want to cancel your {subscription.name} subscription?
              </p>

              <div className="mb-4">
                <label htmlFor="cancelDate" className="block text-sm font-medium text-on-surface-variant mb-1">
                  End Date
                </label>
                <input
                  type="date"
                  id="cancelDate"
                  value={cancelDate}
                  onChange={(e) => setCancelDate(e.target.value)}
                  className="w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface"
                  min={new Date().toISOString().split('T')[0]}
                />
              </div>

              <div className="flex justify-end space-x-3">
                <Button
                  variant="text"
                  onClick={() => setShowCancelModal(false)}
                  disabled={isCancelling}
                >
                  Keep Subscription
                </Button>
                <Button
                  variant="filled"
                  className="bg-error text-on-error"
                  onClick={handleCancelSubscription}
                  disabled={isCancelling}
                >
                  {isCancelling ? 'Cancelling...' : 'Confirm Cancellation'}
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Record Payment Modal */}
      {showPaymentModal && (
        <RecordPaymentModal
          onClose={() => setShowPaymentModal(false)}
          onSubmit={handleRecordPayment}
          isSubmitting={isRecordingPayment}
          error={paymentError}
          defaultAmount={subscription.amount}
          defaultCurrency={subscription.currency}
        />
      )}
    </div>
  );
};

export default SubscriptionDetail;

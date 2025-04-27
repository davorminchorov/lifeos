import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
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

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  if (error || !subscription) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error || 'Subscription not found'}
        </div>
        <div className="mt-4">
          <Button onClick={() => navigate('/subscriptions')}>Back to subscriptions</Button>
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
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">{subscription.name}</h1>
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
        <Card className="md:col-span-2">
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h2 className="text-xl font-semibold mb-4">Subscription Details</h2>

                <div className="space-y-3">
                  <div>
                    <p className="text-sm text-gray-500">Status</p>
                    <p>{renderStatusBadge(subscription.status)}</p>
                  </div>

                  <div>
                    <p className="text-sm text-gray-500">Description</p>
                    <p>{subscription.description}</p>
                  </div>

                  <div>
                    <p className="text-sm text-gray-500">Amount</p>
                    <p className="font-semibold">
                      {formatCurrency(subscription.amount, subscription.currency)}
                    </p>
                  </div>

                  <div>
                    <p className="text-sm text-gray-500">Billing Cycle</p>
                    <p>{subscription.billing_cycle.charAt(0).toUpperCase() + subscription.billing_cycle.slice(1)}</p>
                  </div>

                  {subscription.category && (
                    <div>
                      <p className="text-sm text-gray-500">Category</p>
                      <p>{subscription.category}</p>
                    </div>
                  )}

                  {subscription.website && (
                    <div>
                      <p className="text-sm text-gray-500">Website</p>
                      <a
                        href={subscription.website}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-indigo-600 hover:text-indigo-800"
                      >
                        {subscription.website}
                      </a>
                    </div>
                  )}
                </div>
              </div>

              <div>
                <h2 className="text-xl font-semibold mb-4">Payment Information</h2>

                <div className="space-y-3">
                  <div>
                    <p className="text-sm text-gray-500">Start Date</p>
                    <p>{formatDate(subscription.start_date)}</p>
                  </div>

                  {subscription.end_date && (
                    <div>
                      <p className="text-sm text-gray-500">End Date</p>
                      <p>{formatDate(subscription.end_date)}</p>
                    </div>
                  )}

                  {subscription.next_payment_date && (
                    <div>
                      <p className="text-sm text-gray-500">Next Payment</p>
                      <p className="font-semibold">{formatDate(subscription.next_payment_date)}</p>
                    </div>
                  )}

                  <div>
                    <p className="text-sm text-gray-500">Total Paid</p>
                    <p className="font-semibold">
                      {formatCurrency(subscription.total_paid, subscription.currency)}
                    </p>
                  </div>

                  {subscription.status === 'active' && (
                    <div className="pt-2">
                      <Link to={`/payments/record/${subscription.id}`}>
                        <Button size="sm">
                          Record Payment
                        </Button>
                      </Link>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </Card>

        <div className="md:col-span-1">
          <PaymentSummaryCard
            startDate={subscription.start_date}
            nextPaymentDate={subscription.next_payment_date}
            amount={subscription.amount}
            currency={subscription.currency}
            billingCycle={subscription.billing_cycle}
            totalPaid={subscription.total_paid}
            paymentCount={subscription.payments?.length || 0}
          />
        </div>
      </div>

      <div className="mb-6">
        <PaymentHistoryCard
          payments={paymentHistoryData}
          currency={subscription.currency}
          onRecordPayment={() => setShowPaymentModal(true)}
        />
      </div>

      <RecordPaymentModal
        isOpen={showPaymentModal}
        onClose={() => setShowPaymentModal(false)}
        onSubmit={handleRecordPayment}
        initialAmount={subscription.amount}
        isLoading={isRecordingPayment}
        error={paymentError}
      />

      {showCancelModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <Card className="w-full max-w-md">
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Cancel Subscription</h3>

              {cancelError && (
                <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                  {cancelError}
                </div>
              )}

              <p className="mb-4 text-gray-700">
                Are you sure you want to cancel your subscription to <strong>{subscription.name}</strong>?
              </p>

              <div className="mb-6">
                <label htmlFor="end_date" className="block text-sm font-medium text-gray-700 mb-1">
                  End Date
                </label>
                <input
                  type="date"
                  id="end_date"
                  value={cancelDate}
                  onChange={(e) => setCancelDate(e.target.value)}
                  min={new Date().toISOString().split('T')[0]}
                  className="w-full border border-gray-300 rounded-md shadow-sm p-2"
                />
              </div>

              <div className="flex justify-end space-x-3">
                <Button
                  variant="outlined"
                  onClick={() => setShowCancelModal(false)}
                  disabled={isCancelling}
                >
                  Nevermind
                </Button>
                <Button
                  variant="outlined"
                  className="text-red-600 border-red-600 hover:bg-red-50"
                  onClick={handleCancelSubscription}
                  disabled={isCancelling}
                >
                  {isCancelling ? 'Cancelling...' : 'Confirm Cancellation'}
                </Button>
              </div>
            </div>
          </Card>
        </div>
      )}
    </div>
  );
};

export default SubscriptionDetail;

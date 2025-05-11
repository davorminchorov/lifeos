import React, { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import PaymentHistoryCard from '../../components/subscriptions/PaymentHistoryCard';
import PaymentSummaryCard from '../../components/subscriptions/PaymentSummaryCard';
import RecordPaymentModal, { RecordPaymentFormData } from '../../components/subscriptions/RecordPaymentModal';
import ReminderConfigurationModal, { ReminderFormData } from '../../components/subscriptions/ReminderConfigurationModal';
import { useToast } from '../../ui/Toast';
import { useSubscriptionDetail, useSubscriptionPayments, useRecordPayment, useCancelSubscription, useConfigureReminders } from '../../queries/subscriptionQueries';

interface SubscriptionPayment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  notes: string | null;
  created_at: string;
}

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
  reminder_days_before?: number | null;
  reminder_enabled?: boolean;
  reminder_method?: string | null;
  total_paid?: number;
}

const SubscriptionDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { toast } = useToast();

  // Local UI state
  const [showCancelModal, setShowCancelModal] = useState(false);
  const [cancelDate, setCancelDate] = useState(new Date().toISOString().split('T')[0]);
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [showReminderModal, setShowReminderModal] = useState(false);

  // TanStack Query hooks
  const {
    data: subscription,
    isLoading: isLoadingSubscription,
    error: subscriptionError
  } = useSubscriptionDetail(id as string);

  const {
    data: payments = [],
    isLoading: isLoadingPayments
  } = useSubscriptionPayments(id as string);

  const {
    mutate: recordPayment,
    isPending: isRecordingPayment,
    error: paymentError
  } = useRecordPayment();

  const {
    mutate: cancelSubscription,
    isPending: isCancelling,
    error: cancelError
  } = useCancelSubscription();

  const {
    mutate: configureReminders,
    isPending: isConfiguringReminders,
    error: reminderError
  } = useConfigureReminders();

  const handleCancelSubscription = () => {
    if (!id) return;

    cancelSubscription(
      { id, end_date: cancelDate },
      {
        onSuccess: () => {
          toast({
            title: "Success",
            description: "Subscription cancelled successfully",
            variant: "success",
          });
          setShowCancelModal(false);
        },
        onError: (err: any) => {
          toast({
            title: "Error",
            description: err?.message || 'Failed to cancel subscription',
            variant: "destructive",
          });
        }
      }
    );
  };

  const handleRecordPayment = (paymentData: RecordPaymentFormData) => {
    if (!id) return;

    recordPayment(
      { subscriptionId: id, ...paymentData },
      {
        onSuccess: () => {
          toast({
            title: "Success",
            description: "Payment recorded successfully",
            variant: "success",
          });
          setShowPaymentModal(false);
        },
        onError: (err: any) => {
          toast({
            title: "Error",
            description: err?.message || 'Failed to record payment',
            variant: "destructive",
          });
        }
      }
    );
  };

  const handleConfigureReminders = (reminderData: ReminderFormData) => {
    if (!id) return;

    configureReminders(
      {
        subscriptionId: id,
        ...reminderData
      },
      {
        onSuccess: () => {
          toast({
            title: "Success",
            description: "Reminders configured successfully",
            variant: "success",
          });
          setShowReminderModal(false);
        },
        onError: (err: any) => {
          toast({
            title: "Error",
            description: err?.message || 'Failed to configure reminders',
            variant: "destructive",
          });
        }
      }
    );
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

  const renderReminderStatus = (subscription: Subscription) => {
    if (!subscription.reminder_enabled) {
      return <span className="text-on-surface-variant">Not configured</span>;
    }

    return (
      <div className="flex flex-col">
        <span className="text-tertiary">
          {subscription.reminder_days_before} {subscription.reminder_days_before === 1 ? 'day' : 'days'} before payment
        </span>
        <span className="text-sm text-on-surface-variant capitalize">
          via {subscription.reminder_method?.replace('_', ' ')}
        </span>
      </div>
    );
  };

  // Show loading state if either subscription or payments are loading
  const isLoading = isLoadingSubscription || isLoadingPayments;

  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="flex justify-center items-center h-64">
          <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    );
  }

  if (subscriptionError || !subscription) {
    const errorMessage = subscriptionError instanceof Error ? subscriptionError.message : 'Subscription not found';
    return (
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="bg-error-container border border-error text-on-error-container px-4 py-3 rounded">
          {errorMessage}
        </div>
        <div className="mt-4">
          <Button onClick={() => navigate('/subscriptions')} variant="filled">Back to subscriptions</Button>
        </div>
      </div>
    );
  }

  // Transform payments to the format expected by PaymentHistoryCard
  const formattedPayments = payments.map(payment => ({
    id: payment.id,
    amount: payment.amount,
    date: payment.payment_date,
    status: 'paid', // Assuming all recorded payments are paid
    reference: payment.notes || undefined
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
                  <p className="text-sm text-on-surface-variant">Payment Reminders</p>
                  <div className="mt-1 flex justify-between items-center">
                    {renderReminderStatus(subscription)}
                    <Button
                      variant="text"
                      onClick={() => setShowReminderModal(true)}
                      className="text-sm"
                    >
                      {subscription.reminder_enabled ? 'Update' : 'Configure'}
                    </Button>
                  </div>
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

            <div className="mt-6 pt-6 border-t border-outline border-opacity-20">
              <div className="flex justify-end">
                <Button
                  variant="filled"
                  onClick={() => setShowPaymentModal(true)}
                  disabled={subscription.status !== 'active'}
                >
                  Record Payment
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card variant="elevated">
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
              paymentCount={payments?.length || 0}
            />
          </CardContent>
        </Card>
      </div>

      {payments.length > 0 ? (
        <Card variant="elevated" className="mb-6">
          <CardHeader>
            <CardTitle>Payment History</CardTitle>
          </CardHeader>
          <CardContent>
            <PaymentHistoryCard
              payments={formattedPayments}
              currency={subscription.currency}
            />
          </CardContent>
        </Card>
      ) : (
        <Card variant="elevated" className="mb-6">
          <CardContent className="py-8">
            <p className="text-center text-on-surface-variant">No payment history available.</p>
          </CardContent>
        </Card>
      )}

      {/* Cancel subscription modal */}
      {showCancelModal && (
        <div className="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div className="bg-surface rounded-xl shadow-elevation-3 max-w-md w-full mx-auto">
            <div className="p-6">
              <h3 className="text-headline-small font-medium text-on-surface mb-2">Cancel Subscription</h3>
              <p className="text-body-medium text-on-surface-variant mb-6">
                Are you sure you want to cancel your subscription to {subscription.name}?
              </p>

              {cancelError && (
                <div className="mb-4 p-3 bg-error-container text-on-error-container rounded">
                  {(cancelError as Error).message || 'An error occurred'}
                </div>
              )}

              <div className="mb-6">
                <label htmlFor="cancel-date" className="block text-sm font-medium text-on-surface-variant mb-1">
                  Cancellation Date
                </label>
                <input
                  type="date"
                  id="cancel-date"
                  name="cancel-date"
                  value={cancelDate}
                  onChange={(e) => setCancelDate(e.target.value)}
                  className="w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface"
                  min={new Date().toISOString().split('T')[0]}
                />
              </div>

              <div className="flex justify-end space-x-3">
                <Button variant="text" onClick={() => setShowCancelModal(false)} disabled={isCancelling}>
                  Cancel
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

      {/* Record payment modal */}
      {showPaymentModal && (
        <RecordPaymentModal
          onClose={() => setShowPaymentModal(false)}
          onSubmit={handleRecordPayment}
          isSubmitting={isRecordingPayment}
          error={paymentError instanceof Error ? paymentError.message : null}
          defaultCurrency={subscription.currency}
          defaultAmount={subscription.amount}
        />
      )}

      {/* Reminder Configuration Modal */}
      <ReminderConfigurationModal
        isOpen={showReminderModal}
        onClose={() => setShowReminderModal(false)}
        onSubmit={handleConfigureReminders}
        isSubmitting={isConfiguringReminders}
        subscriptionName={subscription.name}
        defaultValues={{
          days_before: subscription.reminder_days_before || 3,
          enabled: subscription.reminder_enabled || false,
          method: subscription.reminder_method || 'email',
        }}
      />
    </div>
  );
};

export default SubscriptionDetail;

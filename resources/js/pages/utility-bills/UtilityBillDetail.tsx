import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import BillSummaryCard from '../../components/utility-bills/BillSummaryCard';
import BillPaymentHistoryCard from '../../components/utility-bills/BillPaymentHistoryCard';
import RecordBillPaymentModal, { BillPaymentFormData } from '../../components/utility-bills/RecordBillPaymentModal';
import ScheduleReminderModal, { ReminderFormData } from '../../components/utility-bills/ScheduleReminderModal';

interface UtilityBill {
  id: string;
  name: string;
  description: string;
  provider: string;
  account_number: string | null;
  amount: number | null;
  currency: string;
  category: string;
  due_date: string;
  next_due_date: string;
  payment_method: string | null;
  status: string;
  reminder_days: number;
  reminder_date: string | null;
  payments: PaymentRecord[];
  total_paid: number;
}

interface PaymentRecord {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  payment_method: string;
  reference_number: string | null;
  notes: string | null;
  created_at: string;
}

const UtilityBillDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [bill, setBill] = useState<UtilityBill | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Payment modal state
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [isRecordingPayment, setIsRecordingPayment] = useState(false);
  const [paymentError, setPaymentError] = useState<string | null>(null);

  // Reminder modal state
  const [showReminderModal, setShowReminderModal] = useState(false);
  const [isSchedulingReminder, setIsSchedulingReminder] = useState(false);
  const [reminderError, setReminderError] = useState<string | null>(null);

  useEffect(() => {
    fetchBill();
  }, [id]);

  const fetchBill = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`/api/utility-bills/${id}`);
      setBill(response.data);
      setError(null);
    } catch (err) {
      setError('Failed to load bill details');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleRecordPayment = async (paymentData: BillPaymentFormData) => {
    setIsRecordingPayment(true);
    setPaymentError(null);

    try {
      await axios.post(`/api/utility-bills/${id}/pay`, paymentData);
      setShowPaymentModal(false);
      fetchBill(); // Refresh data
    } catch (err: any) {
      setPaymentError(err.response?.data?.error || 'Failed to record payment');
      console.error(err);
    } finally {
      setIsRecordingPayment(false);
    }
  };

  const handleScheduleReminder = async (reminderData: ReminderFormData) => {
    setIsSchedulingReminder(true);
    setReminderError(null);

    try {
      await axios.post(`/api/utility-bills/${id}/remind`, reminderData);
      setShowReminderModal(false);
      fetchBill(); // Refresh data
    } catch (err: any) {
      setReminderError(err.response?.data?.error || 'Failed to schedule reminder');
      console.error(err);
    } finally {
      setIsSchedulingReminder(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  if (error || !bill) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error || 'Bill not found'}
        </div>
        <div className="mt-4">
          <Button onClick={() => navigate('/utility-bills')}>Back to bills</Button>
        </div>
      </div>
    );
  }

  // Calculate if the bill has an active reminder
  const hasReminder = bill.reminder_days > 0;

  // Format payments for the BillPaymentHistoryCard component
  const formattedPayments = bill.payments.map(payment => ({
    ...payment,
    reference_number: payment.reference_number || undefined,
    notes: payment.notes || undefined
  }));

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">{bill.name}</h1>
        <div className="flex space-x-3">
          <Link to={`/utility-bills/${id}/edit`}>
            <Button variant="outlined">Edit</Button>
          </Link>
          {bill.status !== 'paid' && (
            <Button
              onClick={() => setShowPaymentModal(true)}
            >
              Record Payment
            </Button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {/* Main info card - col span 2 */}
        <Card className="md:col-span-2">
          <div className="p-6">
            <div className="space-y-6">
              <div>
                <h2 className="text-xl font-semibold mb-4">Bill Details</h2>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-3">
                    <div>
                      <p className="text-sm text-gray-500">Description</p>
                      <p>{bill.description || 'No description provided'}</p>
                    </div>

                    <div>
                      <p className="text-sm text-gray-500">Account Number</p>
                      <p>{bill.account_number || 'Not provided'}</p>
                    </div>

                    {bill.payment_method && (
                      <div>
                        <p className="text-sm text-gray-500">Preferred Payment Method</p>
                        <p className="capitalize">
                          {bill.payment_method.replace(/_/g, ' ')}
                        </p>
                      </div>
                    )}
                  </div>

                  <div className="space-y-3">
                    <div>
                      <p className="text-sm text-gray-500">Billing Period</p>
                      <p>Monthly</p>
                    </div>

                    <div>
                      <p className="text-sm text-gray-500">Next Due Date</p>
                      <p className="font-medium">{formatDate(bill.next_due_date)}</p>
                    </div>

                    <div>
                      <p className="text-sm text-gray-500">Total Paid</p>
                      <p className="font-medium">
                        {formatCurrency(bill.total_paid, bill.currency)}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Notes section */}
              <div>
                <h3 className="text-lg font-medium mb-2">Notes</h3>
                <Card className="bg-gray-50">
                  <div className="p-4">
                    <p className="text-gray-700">
                      {bill.description || 'No additional notes available.'}
                    </p>
                  </div>
                </Card>
              </div>
            </div>
          </div>
        </Card>

        {/* Bill Summary Card - col span 1 */}
        <div className="md:col-span-1">
          <BillSummaryCard
            name={bill.name}
            provider={bill.provider}
            category={bill.category}
            dueDate={bill.due_date}
            amount={bill.amount}
            currency={bill.currency}
            status={bill.status}
            reminderDays={bill.reminder_days}
            reminderDate={bill.reminder_date}
            hasReminder={hasReminder}
            onScheduleReminder={() => setShowReminderModal(true)}
          />
        </div>
      </div>

      {/* Payment History */}
      <div className="mb-6">
        <BillPaymentHistoryCard
          payments={formattedPayments}
          currency={bill.currency}
          onRecordPayment={() => setShowPaymentModal(true)}
        />
      </div>

      {/* Record Payment Modal */}
      <RecordBillPaymentModal
        isOpen={showPaymentModal}
        onClose={() => setShowPaymentModal(false)}
        onSubmit={handleRecordPayment}
        initialAmount={bill.amount}
        currency={bill.currency}
        isLoading={isRecordingPayment}
        error={paymentError}
      />

      {/* Schedule Reminder Modal */}
      <ScheduleReminderModal
        isOpen={showReminderModal}
        onClose={() => setShowReminderModal(false)}
        onSubmit={handleScheduleReminder}
        currentReminderDays={bill.reminder_days}
        dueDate={bill.due_date}
        isLoading={isSchedulingReminder}
        error={reminderError}
      />
    </div>
  );
};

export default UtilityBillDetail;

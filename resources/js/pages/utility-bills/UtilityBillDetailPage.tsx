import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Separator } from '../../ui/Separator';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../../ui/Dialog';
import { Input } from '../../ui/Input';
import { Label } from '../../ui/Label';
import { Textarea } from '../../ui/Textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../ui/Select';
import { formatCurrency } from '../../utils/format';
import { ArrowLeft, Edit, AlertTriangle, CreditCard, Bell, CheckCircle2, Clock } from 'lucide-react';
import { PageContainer, PageSection } from '../../ui/PageContainer';

interface UtilityBill {
  id: string;
  name: string;
  provider: string;
  amount: number;
  due_date: string;
  category: string;
  is_recurring: boolean;
  recurrence_period: string | null;
  status: string;
  notes: string | null;
  payments: Payment[];
  reminders: Reminder[];
  currency: string;
}

interface Payment {
  id: string;
  payment_date: string;
  payment_amount: number;
  payment_method: string;
  notes: string | null;
}

interface Reminder {
  id: string;
  reminder_date: string;
  reminder_message: string;
  status: string;
  sent_at: string | null;
}

export default function UtilityBillDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [bill, setBill] = useState<UtilityBill | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Payment form state
  const [showPaymentDialog, setShowPaymentDialog] = useState(false);
  const [paymentAmount, setPaymentAmount] = useState<string>('');
  const [paymentDate, setPaymentDate] = useState<string>(new Date().toISOString().split('T')[0]);
  const [paymentMethod, setPaymentMethod] = useState<string>('');
  const [paymentNotes, setPaymentNotes] = useState<string>('');
  const [paymentProcessing, setPaymentProcessing] = useState(false);

  // Reminder form state
  const [showReminderDialog, setShowReminderDialog] = useState(false);
  const [reminderDate, setReminderDate] = useState<string>(new Date().toISOString().split('T')[0]);
  const [reminderMessage, setReminderMessage] = useState<string>('');
  const [reminderProcessing, setReminderProcessing] = useState(false);

  useEffect(() => {
    const fetchBill = async () => {
      try {
        const response = await axios.get(`/api/utility-bills/${id}`);
        setBill(response.data);
      } catch (err) {
        console.error('Error fetching bill details:', err);
        setError('Failed to load bill details. Please try again later.');
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchBill();
    }
  }, [id]);

  const handleGoBack = () => {
    navigate('/utility-bills');
  };

  const handleEdit = () => {
    navigate(`/utility-bills/${id}/edit`);
  };

  const handlePayBill = async (e: React.FormEvent) => {
    e.preventDefault();
    setPaymentProcessing(true);

    try {
      await axios.post(`/api/utility-bills/${id}/pay`, {
        payment_date: paymentDate,
        payment_amount: parseFloat(paymentAmount),
        payment_method: paymentMethod,
        notes: paymentNotes || null,
      });

      // Refresh bill data
      const response = await axios.get(`/api/utility-bills/${id}`);
      setBill(response.data);

      // Reset form and close dialog
      setPaymentAmount('');
      setPaymentDate(new Date().toISOString().split('T')[0]);
      setPaymentMethod('');
      setPaymentNotes('');
      setShowPaymentDialog(false);
    } catch (err) {
      console.error('Error paying bill:', err);
      setError('Failed to record payment. Please try again.');
    } finally {
      setPaymentProcessing(false);
    }
  };

  const handleScheduleReminder = async (e: React.FormEvent) => {
    e.preventDefault();
    setReminderProcessing(true);

    try {
      await axios.post(`/api/utility-bills/${id}/remind`, {
        reminder_date: reminderDate,
        reminder_message: reminderMessage || "Don't forget to pay your bill!",
      });

      // Refresh bill data
      const response = await axios.get(`/api/utility-bills/${id}`);
      setBill(response.data);

      // Reset form and close dialog
      setReminderDate(new Date().toISOString().split('T')[0]);
      setReminderMessage('');
      setShowReminderDialog(false);
    } catch (err) {
      console.error('Error scheduling reminder:', err);
      setError('Failed to schedule reminder. Please try again.');
    } finally {
      setReminderProcessing(false);
    }
  };

  const renderStatusBadge = (status: string) => {
    switch (status) {
      case 'paid':
        return <Badge variant="success">Paid</Badge>;
      case 'pending':
        return <Badge variant="warning">Pending</Badge>;
      default:
        return <Badge>{status}</Badge>;
    }
  };

  const getDueDateStatus = (dueDate: string) => {
    const today = new Date();
    const due = new Date(dueDate);
    const diffTime = due.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return 'overdue';
    if (diffDays <= 7) return 'due-soon';
    return 'upcoming';
  };

  const renderDueDateBadge = (dueDate: string) => {
    const status = getDueDateStatus(dueDate);
    switch (status) {
      case 'overdue':
        return <Badge variant="danger">Overdue</Badge>;
      case 'due-soon':
        return <Badge variant="warning">Due Soon</Badge>;
      case 'upcoming':
        return <Badge variant="outline">Upcoming</Badge>;
      default:
        return null;
    }
  };

  const formatRecurrencePeriod = (period: string | null) => {
    if (!period) return 'None';

    const mapping: Record<string, string> = {
      'monthly': 'Monthly',
      'bimonthly': 'Every 2 Months',
      'quarterly': 'Every 3 Months',
      'annually': 'Yearly'
    };

    return mapping[period] || period;
  };

  if (loading) {
    return (
      <PageContainer title="Bill Details">
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      </PageContainer>
    );
  }

  if (error) {
    return (
      <PageContainer title="Error">
        <Card variant="elevated">
          <CardContent>
            <div className="bg-error/10 text-error p-4 rounded-lg mb-4">
              {error}
            </div>
            <Button variant="outlined" onClick={handleGoBack}>Go Back</Button>
          </CardContent>
        </Card>
      </PageContainer>
    );
  }

  if (!bill) {
    return (
      <PageContainer title="Not Found">
        <Card variant="elevated">
          <CardContent>
            <p className="mb-4">The requested bill could not be found.</p>
            <Button variant="outlined" onClick={handleGoBack}>Go Back</Button>
          </CardContent>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer
      title={bill.name}
      subtitle={`${bill.provider} - ${renderStatusBadge(bill.status)}`}
      actions={
        <div className="flex space-x-2">
          <Button
            variant="outlined"
            icon={<Edit className="h-4 w-4 mr-2" />}
            onClick={handleEdit}
          >
            Edit Bill
          </Button>
          {bill.status === 'pending' && (
            <>
              <Dialog open={showPaymentDialog} onOpenChange={setShowPaymentDialog}>
                <DialogTrigger asChild>
                  <Button variant="filled" icon={<CreditCard className="h-4 w-4 mr-2" />}>
                    Pay Bill
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <form onSubmit={handlePayBill}>
                    <DialogHeader>
                      <DialogTitle>Record Bill Payment</DialogTitle>
                      <DialogDescription>Enter the details of your payment for {bill.name}.</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                      <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="amount" className="text-right">Amount</Label>
                        <Input
                          id="amount"
                          type="number"
                          step="0.01"
                          value={paymentAmount || bill.amount.toString()}
                          onChange={(e) => setPaymentAmount(e.target.value)}
                          required
                          className="col-span-3"
                        />
                      </div>
                      <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="date" className="text-right">Date</Label>
                        <Input
                          id="date"
                          type="date"
                          value={paymentDate}
                          onChange={(e) => setPaymentDate(e.target.value)}
                          required
                          className="col-span-3"
                        />
                      </div>
                      <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="method" className="text-right">Method</Label>
                        <Select value={paymentMethod} onValueChange={setPaymentMethod} required>
                          <SelectTrigger className="col-span-3">
                            <SelectValue placeholder="Select payment method" />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="creditCard">Credit Card</SelectItem>
                            <SelectItem value="bankTransfer">Bank Transfer</SelectItem>
                            <SelectItem value="cash">Cash</SelectItem>
                            <SelectItem value="check">Check</SelectItem>
                            <SelectItem value="other">Other</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="grid grid-cols-4 items-center gap-4">
                        <Label htmlFor="notes" className="text-right">Notes</Label>
                        <Textarea
                          id="notes"
                          value={paymentNotes}
                          onChange={(e) => setPaymentNotes(e.target.value)}
                          className="col-span-3"
                          rows={3}
                        />
                      </div>
                    </div>
                    <DialogFooter>
                      <Button type="button" variant="outlined" onClick={() => setShowPaymentDialog(false)}>Cancel</Button>
                      <Button type="submit" disabled={paymentProcessing}>
                        {paymentProcessing ? 'Processing...' : 'Save Payment'}
                      </Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>
            </>
          )}
          <Dialog open={showReminderDialog} onOpenChange={setShowReminderDialog}>
            <DialogTrigger asChild>
              <Button variant="outlined" icon={<Bell className="h-4 w-4 mr-2" />}>
                Schedule Reminder
              </Button>
            </DialogTrigger>
            <DialogContent>
              <form onSubmit={handleScheduleReminder}>
                <DialogHeader>
                  <DialogTitle>Schedule Bill Reminder</DialogTitle>
                  <DialogDescription>Set a reminder for this bill payment.</DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                  <div className="grid grid-cols-4 items-center gap-4">
                    <Label htmlFor="reminder-date" className="text-right">Date</Label>
                    <Input
                      id="reminder-date"
                      type="date"
                      value={reminderDate}
                      onChange={(e) => setReminderDate(e.target.value)}
                      required
                      className="col-span-3"
                    />
                  </div>
                  <div className="grid grid-cols-4 items-center gap-4">
                    <Label htmlFor="reminder-message" className="text-right">Message</Label>
                    <Textarea
                      id="reminder-message"
                      value={reminderMessage}
                      onChange={(e) => setReminderMessage(e.target.value)}
                      placeholder="Don't forget to pay your bill!"
                      className="col-span-3"
                      rows={3}
                    />
                  </div>
                </div>
                <DialogFooter>
                  <Button type="button" variant="outlined" onClick={() => setShowReminderDialog(false)}>Cancel</Button>
                  <Button type="submit" disabled={reminderProcessing}>
                    {reminderProcessing ? 'Processing...' : 'Schedule Reminder'}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      }
    >
      <PageSection>
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
          <div className="md:col-span-8">
            <Card variant="elevated">
              <CardHeader>
                <CardTitle>Bill Details</CardTitle>
              </CardHeader>
              <CardContent>
                <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <dt className="text-on-surface-variant text-sm">Provider</dt>
                    <dd className="text-on-surface font-medium">{bill.provider}</dd>
                  </div>
                  <div className="space-y-1">
                    <dt className="text-on-surface-variant text-sm">Category</dt>
                    <dd className="text-on-surface font-medium">{bill.category}</dd>
                  </div>
                  <div className="space-y-1">
                    <dt className="text-on-surface-variant text-sm">Amount</dt>
                    <dd className="text-on-surface font-medium">{formatCurrency(bill.amount, bill.currency || 'USD')}</dd>
                  </div>
                  <div className="space-y-1">
                    <dt className="text-on-surface-variant text-sm">Due Date</dt>
                    <dd className="flex items-center space-x-2">
                      <span className="text-on-surface font-medium">{new Date(bill.due_date).toLocaleDateString()}</span>
                      {renderDueDateBadge(bill.due_date)}
                    </dd>
                  </div>
                  <div className="space-y-1">
                    <dt className="text-on-surface-variant text-sm">Recurring</dt>
                    <dd className="text-on-surface font-medium">{bill.is_recurring ? 'Yes' : 'No'}</dd>
                  </div>
                  {bill.is_recurring && (
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm">Recurrence Period</dt>
                      <dd className="text-on-surface font-medium">{formatRecurrencePeriod(bill.recurrence_period)}</dd>
                    </div>
                  )}
                </dl>

                {bill.notes && (
                  <div className="mt-6">
                    <h4 className="text-on-surface-variant text-sm mb-2">Notes</h4>
                    <p className="text-on-surface bg-surface-variant p-3 rounded-md">{bill.notes}</p>
                  </div>
                )}
              </CardContent>
            </Card>

            <Card variant="elevated" className="mt-6">
              <CardHeader>
                <CardTitle>Payment History</CardTitle>
              </CardHeader>
              <CardContent>
                {bill.payments && bill.payments.length > 0 ? (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Date</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead>Notes</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {bill.payments.map((payment) => (
                        <TableRow key={payment.id}>
                          <TableCell>{new Date(payment.payment_date).toLocaleDateString()}</TableCell>
                          <TableCell>{formatCurrency(payment.payment_amount, bill.currency || 'USD')}</TableCell>
                          <TableCell>{payment.payment_method}</TableCell>
                          <TableCell>{payment.notes || '-'}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                ) : (
                  <div className="text-center py-6">
                    <Clock className="h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" />
                    <p className="text-on-surface-variant">No payment history available</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          <div className="md:col-span-4">
            <Card variant="filled">
              <CardHeader>
                <CardTitle>Status</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex flex-col items-center py-4">
                  {bill.status === 'paid' ? (
                    <>
                      <CheckCircle2 className="h-16 w-16 text-success mb-2" />
                      <h3 className="text-lg font-medium">Paid</h3>
                      <p className="text-on-surface-variant text-center mt-2">
                        This bill has been paid in full.
                      </p>
                    </>
                  ) : getDueDateStatus(bill.due_date) === 'overdue' ? (
                    <>
                      <AlertTriangle className="h-16 w-16 text-error mb-2" />
                      <h3 className="text-lg font-medium">Overdue</h3>
                      <p className="text-on-surface-variant text-center mt-2">
                        This bill is past due. Please pay as soon as possible.
                      </p>
                    </>
                  ) : getDueDateStatus(bill.due_date) === 'due-soon' ? (
                    <>
                      <Clock className="h-16 w-16 text-warning mb-2" />
                      <h3 className="text-lg font-medium">Due Soon</h3>
                      <p className="text-on-surface-variant text-center mt-2">
                        This bill is due in the next 7 days.
                      </p>
                    </>
                  ) : (
                    <>
                      <Clock className="h-16 w-16 text-info mb-2" />
                      <h3 className="text-lg font-medium">Upcoming</h3>
                      <p className="text-on-surface-variant text-center mt-2">
                        This bill is scheduled for future payment.
                      </p>
                    </>
                  )}
                </div>
              </CardContent>
            </Card>

            <Card variant="outlined" className="mt-6">
              <CardHeader>
                <CardTitle>Reminders</CardTitle>
              </CardHeader>
              <CardContent>
                {bill.reminders && bill.reminders.length > 0 ? (
                  <ul className="space-y-4">
                    {bill.reminders.map((reminder) => (
                      <li key={reminder.id} className="border-b border-outline border-opacity-20 last:border-b-0 pb-3 last:pb-0">
                        <div className="flex justify-between items-start mb-1">
                          <span className="font-medium">{new Date(reminder.reminder_date).toLocaleDateString()}</span>
                          <Badge variant={reminder.status === 'sent' ? 'success' : 'outline'}>
                            {reminder.status === 'sent' ? 'Sent' : 'Scheduled'}
                          </Badge>
                        </div>
                        <p className="text-sm text-on-surface-variant">{reminder.reminder_message}</p>
                        {reminder.sent_at && (
                          <div className="text-xs text-on-surface-variant mt-1">
                            Sent: {new Date(reminder.sent_at).toLocaleString()}
                          </div>
                        )}
                      </li>
                    ))}
                  </ul>
                ) : (
                  <div className="text-center py-6">
                    <Bell className="h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" />
                    <p className="text-on-surface-variant">No reminders scheduled</p>
                    {bill.status === 'pending' && (
                      <Button
                        variant="text"
                        size="sm"
                        className="mt-2"
                        onClick={() => setShowReminderDialog(true)}
                      >
                        Schedule a reminder
                      </Button>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </PageSection>

      <div className="flex justify-between mt-8">
        <Button variant="outlined" onClick={handleGoBack} icon={<ArrowLeft className="h-4 w-4 mr-2" />}>
          Back to Bills
        </Button>
      </div>
    </PageContainer>
  );
}

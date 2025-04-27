import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../../ui/Card';
import { Button } from '../../ui/Button';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Separator } from '../../ui/Separator';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../../ui/Dialog';
import { Input } from '../../ui/Input';
import { Label } from '../../ui/Label';
import { Textarea } from '../../ui/Textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../ui/Select';
import { formatCurrency } from '../../utils/formatters';
import { ArrowLeft, Edit, AlertTriangle, CreditCard, Bell, CheckCircle2, Clock } from 'lucide-react';

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
        return <Badge variant="destructive">Overdue</Badge>;
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
    return <div className="flex justify-center items-center h-screen">Loading bill details...</div>;
  }

  if (error) {
    return (
      <Card className="max-w-lg mx-auto mt-8">
        <CardHeader>
          <CardTitle>Error</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-red-500">{error}</p>
        </CardContent>
        <CardFooter>
          <Button onClick={handleGoBack}>Go Back</Button>
        </CardFooter>
      </Card>
    );
  }

  if (!bill) {
    return (
      <Card className="max-w-lg mx-auto mt-8">
        <CardHeader>
          <CardTitle>Bill Not Found</CardTitle>
        </CardHeader>
        <CardContent>
          <p>The requested bill could not be found.</p>
        </CardContent>
        <CardFooter>
          <Button onClick={handleGoBack}>Go Back</Button>
        </CardFooter>
      </Card>
    );
  }

  return (
    <div className="container mx-auto p-4">
      <div className="mb-6">
        <Button variant="outline" onClick={handleGoBack} className="mb-4">
          <ArrowLeft className="h-4 w-4 mr-2" />
          Back to Bills
        </Button>

        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold">{bill.name}</h1>
          <div className="flex space-x-2">
            {bill.status === 'pending' && (
              <>
                <Dialog open={showPaymentDialog} onOpenChange={setShowPaymentDialog}>
                  <DialogTrigger asChild>
                    <Button>
                      <CreditCard className="h-4 w-4 mr-2" />
                      Pay Bill
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Pay Bill</DialogTitle>
                      <DialogDescription>
                        Record a payment for this bill.
                      </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handlePayBill}>
                      <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-4 items-center gap-4">
                          <Label htmlFor="payment-amount" className="text-right">
                            Amount
                          </Label>
                          <Input
                            id="payment-amount"
                            type="number"
                            step="0.01"
                            value={paymentAmount}
                            onChange={(e) => setPaymentAmount(e.target.value)}
                            placeholder={bill.amount.toString()}
                            className="col-span-3"
                            required
                          />
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                          <Label htmlFor="payment-date" className="text-right">
                            Payment Date
                          </Label>
                          <Input
                            id="payment-date"
                            type="date"
                            value={paymentDate}
                            onChange={(e) => setPaymentDate(e.target.value)}
                            className="col-span-3"
                            required
                          />
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                          <Label htmlFor="payment-method" className="text-right">
                            Method
                          </Label>
                          <Select value={paymentMethod} onValueChange={setPaymentMethod} required>
                            <SelectTrigger className="col-span-3">
                              <SelectValue placeholder="Select payment method" />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="credit_card">Credit Card</SelectItem>
                              <SelectItem value="debit_card">Debit Card</SelectItem>
                              <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                              <SelectItem value="cash">Cash</SelectItem>
                              <SelectItem value="cheque">Cheque</SelectItem>
                              <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                          </Select>
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                          <Label htmlFor="payment-notes" className="text-right">
                            Notes
                          </Label>
                          <Textarea
                            id="payment-notes"
                            value={paymentNotes}
                            onChange={(e) => setPaymentNotes(e.target.value)}
                            placeholder="Optional notes"
                            className="col-span-3"
                          />
                        </div>
                      </div>

                      <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setShowPaymentDialog(false)}>
                          Cancel
                        </Button>
                        <Button type="submit" disabled={paymentProcessing}>
                          {paymentProcessing ? 'Processing...' : 'Record Payment'}
                        </Button>
                      </DialogFooter>
                    </form>
                  </DialogContent>
                </Dialog>

                <Dialog open={showReminderDialog} onOpenChange={setShowReminderDialog}>
                  <DialogTrigger asChild>
                    <Button variant="outline">
                      <Bell className="h-4 w-4 mr-2" />
                      Add Reminder
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Schedule Reminder</DialogTitle>
                      <DialogDescription>
                        Set a reminder for this bill payment.
                      </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleScheduleReminder}>
                      <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-4 items-center gap-4">
                          <Label htmlFor="reminder-date" className="text-right">
                            Reminder Date
                          </Label>
                          <Input
                            id="reminder-date"
                            type="date"
                            value={reminderDate}
                            onChange={(e) => setReminderDate(e.target.value)}
                            className="col-span-3"
                            required
                          />
                        </div>

                        <div className="grid grid-cols-4 items-center gap-4">
                          <Label htmlFor="reminder-message" className="text-right">
                            Message
                          </Label>
                          <Textarea
                            id="reminder-message"
                            value={reminderMessage}
                            onChange={(e) => setReminderMessage(e.target.value)}
                            placeholder="Don't forget to pay your bill!"
                            className="col-span-3"
                          />
                        </div>
                      </div>

                      <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setShowReminderDialog(false)}>
                          Cancel
                        </Button>
                        <Button type="submit" disabled={reminderProcessing}>
                          {reminderProcessing ? 'Processing...' : 'Schedule Reminder'}
                        </Button>
                      </DialogFooter>
                    </form>
                  </DialogContent>
                </Dialog>
              </>
            )}

            <Button variant="outline" onClick={handleEdit}>
              <Edit className="h-4 w-4 mr-2" />
              Edit
            </Button>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card className="md:col-span-2">
          <CardHeader>
            <CardTitle>Bill Details</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-muted-foreground">Provider</p>
                <p className="font-medium">{bill.provider}</p>
              </div>

              <div>
                <p className="text-sm text-muted-foreground">Category</p>
                <p className="font-medium">{bill.category}</p>
              </div>

              <div>
                <p className="text-sm text-muted-foreground">Amount</p>
                <p className="font-medium">{formatCurrency(bill.amount)}</p>
              </div>

              <div>
                <p className="text-sm text-muted-foreground">Status</p>
                <div className="font-medium flex items-center space-x-2">
                  <span>{bill.status}</span>
                  {renderStatusBadge(bill.status)}
                </div>
              </div>

              <div>
                <p className="text-sm text-muted-foreground">Due Date</p>
                <div className="font-medium flex items-center space-x-2">
                  <span>{new Date(bill.due_date).toLocaleDateString()}</span>
                  {renderDueDateBadge(bill.due_date)}
                </div>
              </div>

              <div>
                <p className="text-sm text-muted-foreground">Recurring</p>
                <p className="font-medium">
                  {bill.is_recurring ? formatRecurrencePeriod(bill.recurrence_period) : 'No'}
                </p>
              </div>
            </div>

            {bill.notes && (
              <div className="mt-4">
                <p className="text-sm text-muted-foreground">Notes</p>
                <p className="font-medium mt-1">{bill.notes}</p>
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Payment Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex flex-col items-center justify-center h-40">
              {bill.status === 'paid' ? (
                <>
                  <CheckCircle2 className="h-16 w-16 text-green-500 mb-4" />
                  <p className="text-center font-medium">Bill has been paid</p>
                  {bill.is_recurring && (
                    <p className="text-center text-sm text-muted-foreground mt-2">
                      Next payment will be due on{' '}
                      <span className="font-medium">
                        {new Date(bill.due_date).toLocaleDateString()}
                      </span>
                    </p>
                  )}
                </>
              ) : (
                <>
                  {getDueDateStatus(bill.due_date) === 'overdue' ? (
                    <AlertTriangle className="h-16 w-16 text-red-500 mb-4" />
                  ) : (
                    <Clock className="h-16 w-16 text-amber-500 mb-4" />
                  )}
                  <p className="text-center font-medium">
                    {getDueDateStatus(bill.due_date) === 'overdue'
                      ? 'Bill is overdue'
                      : 'Payment pending'}
                  </p>
                  <p className="text-center text-sm text-muted-foreground mt-2">
                    Due on{' '}
                    <span className="font-medium">
                      {new Date(bill.due_date).toLocaleDateString()}
                    </span>
                  </p>
                </>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
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
                      <TableCell>{formatCurrency(payment.payment_amount)}</TableCell>
                      <TableCell>{payment.payment_method}</TableCell>
                      <TableCell>{payment.notes || '-'}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <p className="text-center py-8 text-muted-foreground">No payment records found</p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Reminders</CardTitle>
          </CardHeader>
          <CardContent>
            {bill.reminders && bill.reminders.length > 0 ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Date</TableHead>
                    <TableHead>Message</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {bill.reminders.map((reminder) => (
                    <TableRow key={reminder.id}>
                      <TableCell>{new Date(reminder.reminder_date).toLocaleDateString()}</TableCell>
                      <TableCell>{reminder.reminder_message}</TableCell>
                      <TableCell>
                        <Badge variant={reminder.status === 'sent' ? 'success' : 'outline'}>
                          {reminder.status === 'sent' ? 'Sent' : 'Scheduled'}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <p className="text-center py-8 text-muted-foreground">No reminders scheduled</p>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

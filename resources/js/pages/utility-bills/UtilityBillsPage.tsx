import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../ui/Tabs';
import { Badge } from '../../ui/Badge';
import { PlusCircle, AlertTriangle, CheckCircle, CalendarClock } from 'lucide-react';
import { formatCurrency } from '../../utils/format';
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
}

interface PendingBill {
  bill_id: string;
  name: string;
  provider: string;
  amount: number;
  due_date: string;
  category: string;
}

interface PaymentHistory {
  id: number;
  bill_id: string;
  bill_name: string;
  provider: string;
  payment_date: string;
  payment_amount: number;
  payment_method: string;
  category: string;
}

interface UpcomingReminder {
  id: number;
  bill_id: string;
  bill_name: string;
  provider: string;
  due_date: string;
  amount: number;
  reminder_date: string;
  reminder_message: string;
}

export default function UtilityBillsPage() {
  const [bills, setBills] = useState<UtilityBill[]>([]);
  const [pendingBills, setPendingBills] = useState<PendingBill[]>([]);
  const [paymentHistory, setPaymentHistory] = useState<PaymentHistory[]>([]);
  const [upcomingReminders, setUpcomingReminders] = useState<UpcomingReminder[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('all');
  const navigate = useNavigate();

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [billsRes, pendingRes, historyRes, remindersRes] = await Promise.all([
          axios.get('/api/utility-bills'),
          axios.get('/api/utility-bills/pending'),
          axios.get('/api/utility-bills/payments'),
          axios.get('/api/utility-bills/reminders')
        ]);

        setBills(Array.isArray(billsRes.data) ? billsRes.data : []);
        setPendingBills(Array.isArray(pendingRes.data) ? pendingRes.data : []);
        setPaymentHistory(Array.isArray(historyRes.data) ? historyRes.data : []);
        setUpcomingReminders(Array.isArray(remindersRes.data) ? remindersRes.data : []);
      } catch (error) {
        console.error('Error fetching utility bills data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  const handleAddNew = () => {
    navigate('/utility-bills/create');
  };

  const handleViewDetails = (id: string) => {
    navigate(`/utility-bills/${id}`);
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

  return (
    <PageContainer
      title="Utility Bills"
      subtitle="Manage and track your recurring utility bills and payments"
      actions={
        <Button
          onClick={handleAddNew}
          variant="filled"
          icon={<PlusCircle className="h-4 w-4 mr-2" />}
        >
          Add New Bill
        </Button>
      }
    >
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid grid-cols-4">
          <TabsTrigger value="all">All Bills</TabsTrigger>
          <TabsTrigger value="pending">
            Pending
            {pendingBills.length > 0 && (
              <Badge variant="warning" className="ml-2">
                {pendingBills.length}
              </Badge>
            )}
          </TabsTrigger>
          <TabsTrigger value="history">Payment History</TabsTrigger>
          <TabsTrigger value="reminders">
            Reminders
            {upcomingReminders.length > 0 && (
              <Badge variant="outline" className="ml-2">
                {upcomingReminders.length}
              </Badge>
            )}
          </TabsTrigger>
        </TabsList>

        <TabsContent value="all" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>All Utility Bills</CardTitle>
              <CardDescription>View and manage all your utility bills</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="flex justify-center items-center h-64">
                  <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Provider</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Due Date</TableHead>
                      <TableHead>Category</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {bills.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={7} className="text-center py-4">
                          No bills found. Click "Add New Bill" to create one.
                        </TableCell>
                      </TableRow>
                    ) : (
                      bills.map((bill) => (
                        <TableRow key={bill.id}>
                          <TableCell>{bill.name}</TableCell>
                          <TableCell>{bill.provider}</TableCell>
                          <TableCell>{formatCurrency(bill.amount, 'USD')}</TableCell>
                          <TableCell>
                            <div className="flex flex-col">
                              <span>{new Date(bill.due_date).toLocaleDateString()}</span>
                              {renderDueDateBadge(bill.due_date)}
                            </div>
                          </TableCell>
                          <TableCell>{bill.category}</TableCell>
                          <TableCell>{renderStatusBadge(bill.status)}</TableCell>
                          <TableCell>
                            <Button variant="outlined" size="sm" onClick={() => handleViewDetails(bill.id)}>
                              View
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="pending" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Pending Bills</CardTitle>
              <CardDescription>Bills that require your attention</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="flex justify-center items-center h-64">
                  <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Provider</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Due Date</TableHead>
                      <TableHead>Category</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {pendingBills.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={6} className="text-center py-4">
                          <CheckCircle className="h-8 w-8 text-green-500 mx-auto mb-2" />
                          No pending bills! You're all caught up.
                        </TableCell>
                      </TableRow>
                    ) : (
                      pendingBills.map((bill) => (
                        <TableRow key={bill.bill_id}>
                          <TableCell>{bill.name}</TableCell>
                          <TableCell>{bill.provider}</TableCell>
                          <TableCell>{formatCurrency(bill.amount, 'USD')}</TableCell>
                          <TableCell>
                            <div className="flex flex-col">
                              <span>{new Date(bill.due_date).toLocaleDateString()}</span>
                              {renderDueDateBadge(bill.due_date)}
                            </div>
                          </TableCell>
                          <TableCell>{bill.category}</TableCell>
                          <TableCell>
                            <Button variant="outlined" size="sm" onClick={() => handleViewDetails(bill.bill_id)}>
                              View
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="history" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Payment History</CardTitle>
              <CardDescription>Record of your past utility bill payments</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="flex justify-center items-center h-64">
                  <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Bill Name</TableHead>
                      <TableHead>Provider</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Payment Date</TableHead>
                      <TableHead>Method</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {paymentHistory.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={5} className="text-center py-4">
                          No payment history found.
                        </TableCell>
                      </TableRow>
                    ) : (
                      paymentHistory.map((payment) => (
                        <TableRow key={payment.id}>
                          <TableCell>{payment.bill_name}</TableCell>
                          <TableCell>{payment.provider}</TableCell>
                          <TableCell>{formatCurrency(payment.payment_amount, 'USD')}</TableCell>
                          <TableCell>{new Date(payment.payment_date).toLocaleDateString()}</TableCell>
                          <TableCell>{payment.payment_method}</TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="reminders" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Upcoming Reminders</CardTitle>
              <CardDescription>Scheduled notifications for upcoming bill payments</CardDescription>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="flex justify-center items-center h-64">
                  <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Bill Name</TableHead>
                      <TableHead>Provider</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Due Date</TableHead>
                      <TableHead>Reminder Date</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {upcomingReminders.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={5} className="text-center py-4">
                          <CalendarClock className="h-8 w-8 text-gray-400 mx-auto mb-2" />
                          No upcoming reminders scheduled.
                        </TableCell>
                      </TableRow>
                    ) : (
                      upcomingReminders.map((reminder) => (
                        <TableRow key={reminder.id}>
                          <TableCell>{reminder.bill_name}</TableCell>
                          <TableCell>{reminder.provider}</TableCell>
                          <TableCell>{formatCurrency(reminder.amount, 'USD')}</TableCell>
                          <TableCell>{new Date(reminder.due_date).toLocaleDateString()}</TableCell>
                          <TableCell>{new Date(reminder.reminder_date).toLocaleDateString()}</TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {!loading && bills.length > 0 && (
        <div className="mt-8 text-center">
          <p className="text-on-surface-variant mb-2">Want to analyze your utility spending?</p>
          <p className="text-on-surface mb-4">View reports to see trends and patterns in your utility bills.</p>
          <Button variant="outlined" onClick={() => navigate('/reports/utility-bills')}>
            View Reports
          </Button>
        </div>
      )}
    </PageContainer>
  );
}

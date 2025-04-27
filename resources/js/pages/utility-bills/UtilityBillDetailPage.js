import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../../ui/Dialog';
import { Input } from '../../ui/Input';
import { Label } from '../../ui/Label';
import { Textarea } from '../../ui/Textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../ui/Select';
import { formatCurrency } from '../../utils/format';
import { ArrowLeft, Edit, AlertTriangle, CreditCard, Bell, CheckCircle2, Clock } from 'lucide-react';
import { PageContainer, PageSection } from '../../ui/PageContainer';
export default function UtilityBillDetailPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [bill, setBill] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    // Payment form state
    const [showPaymentDialog, setShowPaymentDialog] = useState(false);
    const [paymentAmount, setPaymentAmount] = useState('');
    const [paymentDate, setPaymentDate] = useState(new Date().toISOString().split('T')[0]);
    const [paymentMethod, setPaymentMethod] = useState('');
    const [paymentNotes, setPaymentNotes] = useState('');
    const [paymentProcessing, setPaymentProcessing] = useState(false);
    // Reminder form state
    const [showReminderDialog, setShowReminderDialog] = useState(false);
    const [reminderDate, setReminderDate] = useState(new Date().toISOString().split('T')[0]);
    const [reminderMessage, setReminderMessage] = useState('');
    const [reminderProcessing, setReminderProcessing] = useState(false);
    useEffect(() => {
        const fetchBill = async () => {
            try {
                const response = await axios.get(`/api/utility-bills/${id}`);
                setBill(response.data);
            }
            catch (err) {
                console.error('Error fetching bill details:', err);
                setError('Failed to load bill details. Please try again later.');
            }
            finally {
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
    const handlePayBill = async (e) => {
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
        }
        catch (err) {
            console.error('Error paying bill:', err);
            setError('Failed to record payment. Please try again.');
        }
        finally {
            setPaymentProcessing(false);
        }
    };
    const handleScheduleReminder = async (e) => {
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
        }
        catch (err) {
            console.error('Error scheduling reminder:', err);
            setError('Failed to schedule reminder. Please try again.');
        }
        finally {
            setReminderProcessing(false);
        }
    };
    const renderStatusBadge = (status) => {
        switch (status) {
            case 'paid':
                return _jsx(Badge, { variant: "success", children: "Paid" });
            case 'pending':
                return _jsx(Badge, { variant: "warning", children: "Pending" });
            default:
                return _jsx(Badge, { children: status });
        }
    };
    const getDueDateStatus = (dueDate) => {
        const today = new Date();
        const due = new Date(dueDate);
        const diffTime = due.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        if (diffDays < 0)
            return 'overdue';
        if (diffDays <= 7)
            return 'due-soon';
        return 'upcoming';
    };
    const renderDueDateBadge = (dueDate) => {
        const status = getDueDateStatus(dueDate);
        switch (status) {
            case 'overdue':
                return _jsx(Badge, { variant: "danger", children: "Overdue" });
            case 'due-soon':
                return _jsx(Badge, { variant: "warning", children: "Due Soon" });
            case 'upcoming':
                return _jsx(Badge, { variant: "outline", children: "Upcoming" });
            default:
                return null;
        }
    };
    const formatRecurrencePeriod = (period) => {
        if (!period)
            return 'None';
        const mapping = {
            'monthly': 'Monthly',
            'bimonthly': 'Every 2 Months',
            'quarterly': 'Every 3 Months',
            'annually': 'Yearly'
        };
        return mapping[period] || period;
    };
    if (loading) {
        return (_jsx(PageContainer, { title: "Bill Details", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) }) }));
    }
    if (error) {
        return (_jsx(PageContainer, { title: "Error", children: _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { children: [_jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg mb-4", children: error }), _jsx(Button, { variant: "outlined", onClick: handleGoBack, children: "Go Back" })] }) }) }));
    }
    if (!bill) {
        return (_jsx(PageContainer, { title: "Not Found", children: _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { children: [_jsx("p", { className: "mb-4", children: "The requested bill could not be found." }), _jsx(Button, { variant: "outlined", onClick: handleGoBack, children: "Go Back" })] }) }) }));
    }
    return (_jsxs(PageContainer, { title: bill.name, subtitle: `${bill.provider} - ${renderStatusBadge(bill.status)}`, actions: _jsxs("div", { className: "flex space-x-2", children: [_jsx(Button, { variant: "outlined", icon: _jsx(Edit, { className: "h-4 w-4 mr-2" }), onClick: handleEdit, children: "Edit Bill" }), bill.status === 'pending' && (_jsx(_Fragment, { children: _jsxs(Dialog, { open: showPaymentDialog, onOpenChange: setShowPaymentDialog, children: [_jsx(DialogTrigger, { asChild: true, children: _jsx(Button, { variant: "filled", icon: _jsx(CreditCard, { className: "h-4 w-4 mr-2" }), children: "Pay Bill" }) }), _jsx(DialogContent, { children: _jsxs("form", { onSubmit: handlePayBill, children: [_jsxs(DialogHeader, { children: [_jsx(DialogTitle, { children: "Record Bill Payment" }), _jsxs(DialogDescription, { children: ["Enter the details of your payment for ", bill.name, "."] })] }), _jsxs("div", { className: "grid gap-4 py-4", children: [_jsxs("div", { className: "grid grid-cols-4 items-center gap-4", children: [_jsx(Label, { htmlFor: "amount", className: "text-right", children: "Amount" }), _jsx(Input, { id: "amount", type: "number", step: "0.01", value: paymentAmount || bill.amount.toString(), onChange: (e) => setPaymentAmount(e.target.value), required: true, className: "col-span-3" })] }), _jsxs("div", { className: "grid grid-cols-4 items-center gap-4", children: [_jsx(Label, { htmlFor: "date", className: "text-right", children: "Date" }), _jsx(Input, { id: "date", type: "date", value: paymentDate, onChange: (e) => setPaymentDate(e.target.value), required: true, className: "col-span-3" })] }), _jsxs("div", { className: "grid grid-cols-4 items-center gap-4", children: [_jsx(Label, { htmlFor: "method", className: "text-right", children: "Method" }), _jsxs(Select, { value: paymentMethod, onValueChange: setPaymentMethod, required: true, children: [_jsx(SelectTrigger, { className: "col-span-3", children: _jsx(SelectValue, { placeholder: "Select payment method" }) }), _jsxs(SelectContent, { children: [_jsx(SelectItem, { value: "creditCard", children: "Credit Card" }), _jsx(SelectItem, { value: "bankTransfer", children: "Bank Transfer" }), _jsx(SelectItem, { value: "cash", children: "Cash" }), _jsx(SelectItem, { value: "check", children: "Check" }), _jsx(SelectItem, { value: "other", children: "Other" })] })] })] }), _jsxs("div", { className: "grid grid-cols-4 items-center gap-4", children: [_jsx(Label, { htmlFor: "notes", className: "text-right", children: "Notes" }), _jsx(Textarea, { id: "notes", value: paymentNotes, onChange: (e) => setPaymentNotes(e.target.value), className: "col-span-3", rows: 3 })] })] }), _jsxs(DialogFooter, { children: [_jsx(Button, { type: "button", variant: "outlined", onClick: () => setShowPaymentDialog(false), children: "Cancel" }), _jsx(Button, { type: "submit", disabled: paymentProcessing, children: paymentProcessing ? 'Processing...' : 'Save Payment' })] })] }) })] }) })), _jsxs(Dialog, { open: showReminderDialog, onOpenChange: setShowReminderDialog, children: [_jsx(DialogTrigger, { asChild: true, children: _jsx(Button, { variant: "outlined", icon: _jsx(Bell, { className: "h-4 w-4 mr-2" }), children: "Schedule Reminder" }) }), _jsx(DialogContent, { children: _jsxs("form", { onSubmit: handleScheduleReminder, children: [_jsxs(DialogHeader, { children: [_jsx(DialogTitle, { children: "Schedule Bill Reminder" }), _jsx(DialogDescription, { children: "Set a reminder for this bill payment." })] }), _jsxs("div", { className: "grid gap-4 py-4", children: [_jsxs("div", { className: "grid grid-cols-4 items-center gap-4", children: [_jsx(Label, { htmlFor: "reminder-date", className: "text-right", children: "Date" }), _jsx(Input, { id: "reminder-date", type: "date", value: reminderDate, onChange: (e) => setReminderDate(e.target.value), required: true, className: "col-span-3" })] }), _jsxs("div", { className: "grid grid-cols-4 items-center gap-4", children: [_jsx(Label, { htmlFor: "reminder-message", className: "text-right", children: "Message" }), _jsx(Textarea, { id: "reminder-message", value: reminderMessage, onChange: (e) => setReminderMessage(e.target.value), placeholder: "Don't forget to pay your bill!", className: "col-span-3", rows: 3 })] })] }), _jsxs(DialogFooter, { children: [_jsx(Button, { type: "button", variant: "outlined", onClick: () => setShowReminderDialog(false), children: "Cancel" }), _jsx(Button, { type: "submit", disabled: reminderProcessing, children: reminderProcessing ? 'Processing...' : 'Schedule Reminder' })] })] }) })] })] }), children: [_jsx(PageSection, { children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-12 gap-6", children: [_jsxs("div", { className: "md:col-span-8", children: [_jsxs(Card, { variant: "elevated", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Bill Details" }) }), _jsxs(CardContent, { children: [_jsxs("dl", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Provider" }), _jsx("dd", { className: "text-on-surface font-medium", children: bill.provider })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Category" }), _jsx("dd", { className: "text-on-surface font-medium", children: bill.category })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Amount" }), _jsx("dd", { className: "text-on-surface font-medium", children: formatCurrency(bill.amount, bill.currency || 'USD') })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Due Date" }), _jsxs("dd", { className: "flex items-center space-x-2", children: [_jsx("span", { className: "text-on-surface font-medium", children: new Date(bill.due_date).toLocaleDateString() }), renderDueDateBadge(bill.due_date)] })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Recurring" }), _jsx("dd", { className: "text-on-surface font-medium", children: bill.is_recurring ? 'Yes' : 'No' })] }), bill.is_recurring && (_jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Recurrence Period" }), _jsx("dd", { className: "text-on-surface font-medium", children: formatRecurrencePeriod(bill.recurrence_period) })] }))] }), bill.notes && (_jsxs("div", { className: "mt-6", children: [_jsx("h4", { className: "text-on-surface-variant text-sm mb-2", children: "Notes" }), _jsx("p", { className: "text-on-surface bg-surface-variant p-3 rounded-md", children: bill.notes })] }))] })] }), _jsxs(Card, { variant: "elevated", className: "mt-6", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Payment History" }) }), _jsx(CardContent, { children: bill.payments && bill.payments.length > 0 ? (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Date" }), _jsx(TableHead, { children: "Amount" }), _jsx(TableHead, { children: "Method" }), _jsx(TableHead, { children: "Notes" })] }) }), _jsx(TableBody, { children: bill.payments.map((payment) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: new Date(payment.payment_date).toLocaleDateString() }), _jsx(TableCell, { children: formatCurrency(payment.payment_amount, bill.currency || 'USD') }), _jsx(TableCell, { children: payment.payment_method }), _jsx(TableCell, { children: payment.notes || '-' })] }, payment.id))) })] })) : (_jsxs("div", { className: "text-center py-6", children: [_jsx(Clock, { className: "h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" }), _jsx("p", { className: "text-on-surface-variant", children: "No payment history available" })] })) })] })] }), _jsxs("div", { className: "md:col-span-4", children: [_jsxs(Card, { variant: "filled", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Status" }) }), _jsx(CardContent, { children: _jsx("div", { className: "flex flex-col items-center py-4", children: bill.status === 'paid' ? (_jsxs(_Fragment, { children: [_jsx(CheckCircle2, { className: "h-16 w-16 text-success mb-2" }), _jsx("h3", { className: "text-lg font-medium", children: "Paid" }), _jsx("p", { className: "text-on-surface-variant text-center mt-2", children: "This bill has been paid in full." })] })) : getDueDateStatus(bill.due_date) === 'overdue' ? (_jsxs(_Fragment, { children: [_jsx(AlertTriangle, { className: "h-16 w-16 text-error mb-2" }), _jsx("h3", { className: "text-lg font-medium", children: "Overdue" }), _jsx("p", { className: "text-on-surface-variant text-center mt-2", children: "This bill is past due. Please pay as soon as possible." })] })) : getDueDateStatus(bill.due_date) === 'due-soon' ? (_jsxs(_Fragment, { children: [_jsx(Clock, { className: "h-16 w-16 text-warning mb-2" }), _jsx("h3", { className: "text-lg font-medium", children: "Due Soon" }), _jsx("p", { className: "text-on-surface-variant text-center mt-2", children: "This bill is due in the next 7 days." })] })) : (_jsxs(_Fragment, { children: [_jsx(Clock, { className: "h-16 w-16 text-info mb-2" }), _jsx("h3", { className: "text-lg font-medium", children: "Upcoming" }), _jsx("p", { className: "text-on-surface-variant text-center mt-2", children: "This bill is scheduled for future payment." })] })) }) })] }), _jsxs(Card, { variant: "outlined", className: "mt-6", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Reminders" }) }), _jsx(CardContent, { children: bill.reminders && bill.reminders.length > 0 ? (_jsx("ul", { className: "space-y-4", children: bill.reminders.map((reminder) => (_jsxs("li", { className: "border-b border-outline border-opacity-20 last:border-b-0 pb-3 last:pb-0", children: [_jsxs("div", { className: "flex justify-between items-start mb-1", children: [_jsx("span", { className: "font-medium", children: new Date(reminder.reminder_date).toLocaleDateString() }), _jsx(Badge, { variant: reminder.status === 'sent' ? 'success' : 'outline', children: reminder.status === 'sent' ? 'Sent' : 'Scheduled' })] }), _jsx("p", { className: "text-sm text-on-surface-variant", children: reminder.reminder_message }), reminder.sent_at && (_jsxs("div", { className: "text-xs text-on-surface-variant mt-1", children: ["Sent: ", new Date(reminder.sent_at).toLocaleString()] }))] }, reminder.id))) })) : (_jsxs("div", { className: "text-center py-6", children: [_jsx(Bell, { className: "h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" }), _jsx("p", { className: "text-on-surface-variant", children: "No reminders scheduled" }), bill.status === 'pending' && (_jsx(Button, { variant: "text", size: "sm", className: "mt-2", onClick: () => setShowReminderDialog(true), children: "Schedule a reminder" }))] })) })] })] })] }) }), _jsx("div", { className: "flex justify-between mt-8", children: _jsx(Button, { variant: "outlined", onClick: handleGoBack, icon: _jsx(ArrowLeft, { className: "h-4 w-4 mr-2" }), children: "Back to Bills" }) })] }));
}

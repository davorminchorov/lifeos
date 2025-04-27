import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../ui/Tabs';
import { Badge } from '../../ui/Badge';
import { PlusCircle, CheckCircle, CalendarClock } from 'lucide-react';
import { formatCurrency } from '../../utils/format';
import { PageContainer } from '../../ui/PageContainer';
export default function UtilityBillsPage() {
    const [bills, setBills] = useState([]);
    const [pendingBills, setPendingBills] = useState([]);
    const [paymentHistory, setPaymentHistory] = useState([]);
    const [upcomingReminders, setUpcomingReminders] = useState([]);
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
            }
            catch (error) {
                console.error('Error fetching utility bills data:', error);
            }
            finally {
                setLoading(false);
            }
        };
        fetchData();
    }, []);
    const handleAddNew = () => {
        navigate('/utility-bills/create');
    };
    const handleViewDetails = (id) => {
        navigate(`/utility-bills/${id}`);
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
    return (_jsxs(PageContainer, { title: "Utility Bills", subtitle: "Manage and track your recurring utility bills and payments", actions: _jsx(Button, { onClick: handleAddNew, variant: "filled", icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: "Add New Bill" }), children: [_jsxs(Tabs, { value: activeTab, onValueChange: setActiveTab, className: "w-full", children: [_jsxs(TabsList, { className: "grid grid-cols-4", children: [_jsx(TabsTrigger, { value: "all", children: "All Bills" }), _jsxs(TabsTrigger, { value: "pending", children: ["Pending", pendingBills.length > 0 && (_jsx(Badge, { variant: "warning", className: "ml-2", children: pendingBills.length }))] }), _jsx(TabsTrigger, { value: "history", children: "Payment History" }), _jsxs(TabsTrigger, { value: "reminders", children: ["Reminders", upcomingReminders.length > 0 && (_jsx(Badge, { variant: "outline", className: "ml-2", children: upcomingReminders.length }))] })] }), _jsx(TabsContent, { value: "all", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "All Utility Bills" }), _jsx(CardDescription, { children: "View and manage all your utility bills" })] }), _jsx(CardContent, { children: loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Name" }), _jsx(TableHead, { children: "Provider" }), _jsx(TableHead, { children: "Amount" }), _jsx(TableHead, { children: "Due Date" }), _jsx(TableHead, { children: "Category" }), _jsx(TableHead, { children: "Status" }), _jsx(TableHead, { children: "Actions" })] }) }), _jsx(TableBody, { children: bills.length === 0 ? (_jsx(TableRow, { children: _jsx(TableCell, { colSpan: 7, className: "text-center py-4", children: "No bills found. Click \"Add New Bill\" to create one." }) })) : (bills.map((bill) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: bill.name }), _jsx(TableCell, { children: bill.provider }), _jsx(TableCell, { children: formatCurrency(bill.amount, 'USD') }), _jsx(TableCell, { children: _jsxs("div", { className: "flex flex-col", children: [_jsx("span", { children: new Date(bill.due_date).toLocaleDateString() }), renderDueDateBadge(bill.due_date)] }) }), _jsx(TableCell, { children: bill.category }), _jsx(TableCell, { children: renderStatusBadge(bill.status) }), _jsx(TableCell, { children: _jsx(Button, { variant: "outlined", size: "sm", onClick: () => handleViewDetails(bill.id), children: "View" }) })] }, bill.id)))) })] })) })] }) }), _jsx(TabsContent, { value: "pending", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Pending Bills" }), _jsx(CardDescription, { children: "Bills that require your attention" })] }), _jsx(CardContent, { children: loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Name" }), _jsx(TableHead, { children: "Provider" }), _jsx(TableHead, { children: "Amount" }), _jsx(TableHead, { children: "Due Date" }), _jsx(TableHead, { children: "Category" }), _jsx(TableHead, { children: "Actions" })] }) }), _jsx(TableBody, { children: pendingBills.length === 0 ? (_jsx(TableRow, { children: _jsxs(TableCell, { colSpan: 6, className: "text-center py-4", children: [_jsx(CheckCircle, { className: "h-8 w-8 text-green-500 mx-auto mb-2" }), "No pending bills! You're all caught up."] }) })) : (pendingBills.map((bill) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: bill.name }), _jsx(TableCell, { children: bill.provider }), _jsx(TableCell, { children: formatCurrency(bill.amount, 'USD') }), _jsx(TableCell, { children: _jsxs("div", { className: "flex flex-col", children: [_jsx("span", { children: new Date(bill.due_date).toLocaleDateString() }), renderDueDateBadge(bill.due_date)] }) }), _jsx(TableCell, { children: bill.category }), _jsx(TableCell, { children: _jsx(Button, { variant: "outlined", size: "sm", onClick: () => handleViewDetails(bill.bill_id), children: "View" }) })] }, bill.bill_id)))) })] })) })] }) }), _jsx(TabsContent, { value: "history", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Payment History" }), _jsx(CardDescription, { children: "Record of your past utility bill payments" })] }), _jsx(CardContent, { children: loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Bill Name" }), _jsx(TableHead, { children: "Provider" }), _jsx(TableHead, { children: "Amount" }), _jsx(TableHead, { children: "Payment Date" }), _jsx(TableHead, { children: "Method" })] }) }), _jsx(TableBody, { children: paymentHistory.length === 0 ? (_jsx(TableRow, { children: _jsx(TableCell, { colSpan: 5, className: "text-center py-4", children: "No payment history found." }) })) : (paymentHistory.map((payment) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: payment.bill_name }), _jsx(TableCell, { children: payment.provider }), _jsx(TableCell, { children: formatCurrency(payment.payment_amount, 'USD') }), _jsx(TableCell, { children: new Date(payment.payment_date).toLocaleDateString() }), _jsx(TableCell, { children: payment.payment_method })] }, payment.id)))) })] })) })] }) }), _jsx(TabsContent, { value: "reminders", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Upcoming Reminders" }), _jsx(CardDescription, { children: "Scheduled notifications for upcoming bill payments" })] }), _jsx(CardContent, { children: loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Bill Name" }), _jsx(TableHead, { children: "Provider" }), _jsx(TableHead, { children: "Amount" }), _jsx(TableHead, { children: "Due Date" }), _jsx(TableHead, { children: "Reminder Date" })] }) }), _jsx(TableBody, { children: upcomingReminders.length === 0 ? (_jsx(TableRow, { children: _jsxs(TableCell, { colSpan: 5, className: "text-center py-4", children: [_jsx(CalendarClock, { className: "h-8 w-8 text-gray-400 mx-auto mb-2" }), "No upcoming reminders scheduled."] }) })) : (upcomingReminders.map((reminder) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: reminder.bill_name }), _jsx(TableCell, { children: reminder.provider }), _jsx(TableCell, { children: formatCurrency(reminder.amount, 'USD') }), _jsx(TableCell, { children: new Date(reminder.due_date).toLocaleDateString() }), _jsx(TableCell, { children: new Date(reminder.reminder_date).toLocaleDateString() })] }, reminder.id)))) })] })) })] }) })] }), !loading && bills.length > 0 && (_jsxs("div", { className: "mt-8 text-center", children: [_jsx("p", { className: "text-on-surface-variant mb-2", children: "Want to analyze your utility spending?" }), _jsx("p", { className: "text-on-surface mb-4", children: "View reports to see trends and patterns in your utility bills." }), _jsx(Button, { variant: "outlined", onClick: () => navigate('/reports/utility-bills'), children: "View Reports" })] }))] }));
}

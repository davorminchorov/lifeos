import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import BillSummaryCard from '../../components/utility-bills/BillSummaryCard';
import BillPaymentHistoryCard from '../../components/utility-bills/BillPaymentHistoryCard';
import RecordBillPaymentModal from '../../components/utility-bills/RecordBillPaymentModal';
import ScheduleReminderModal from '../../components/utility-bills/ScheduleReminderModal';
import { useToast } from '../../ui/Toast';
const UtilityBillDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [bill, setBill] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const { toast } = useToast();
    // Payment modal state
    const [showPaymentModal, setShowPaymentModal] = useState(false);
    const [isRecordingPayment, setIsRecordingPayment] = useState(false);
    const [paymentError, setPaymentError] = useState(null);
    // Reminder modal state
    const [showReminderModal, setShowReminderModal] = useState(false);
    const [isSchedulingReminder, setIsSchedulingReminder] = useState(false);
    const [reminderError, setReminderError] = useState(null);
    useEffect(() => {
        fetchBill();
    }, [id]);
    const fetchBill = async () => {
        setLoading(true);
        try {
            const response = await axios.get(`/api/utility-bills/${id}`);
            setBill(response.data);
            setError(null);
        }
        catch (err) {
            setError('Failed to load bill details');
            toast({
                title: "Error",
                description: "Failed to load bill details",
                variant: "destructive",
            });
            console.error(err);
        }
        finally {
            setLoading(false);
        }
    };
    const handleRecordPayment = async (paymentData) => {
        var _a, _b;
        setIsRecordingPayment(true);
        setPaymentError(null);
        try {
            await axios.post(`/api/utility-bills/${id}/pay`, paymentData);
            setShowPaymentModal(false);
            toast({
                title: "Success",
                description: "Payment recorded successfully",
                variant: "success",
            });
            fetchBill(); // Refresh data
        }
        catch (err) {
            const errorMessage = ((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.error) || 'Failed to record payment';
            setPaymentError(errorMessage);
            toast({
                title: "Error",
                description: errorMessage,
                variant: "destructive",
            });
            console.error(err);
        }
        finally {
            setIsRecordingPayment(false);
        }
    };
    const handleScheduleReminder = async (reminderData) => {
        var _a, _b;
        setIsSchedulingReminder(true);
        setReminderError(null);
        try {
            await axios.post(`/api/utility-bills/${id}/remind`, reminderData);
            setShowReminderModal(false);
            toast({
                title: "Success",
                description: "Reminder scheduled successfully",
                variant: "success",
            });
            fetchBill(); // Refresh data
        }
        catch (err) {
            const errorMessage = ((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.error) || 'Failed to schedule reminder';
            setReminderError(errorMessage);
            toast({
                title: "Error",
                description: errorMessage,
                variant: "destructive",
            });
            console.error(err);
        }
        finally {
            setIsSchedulingReminder(false);
        }
    };
    if (loading) {
        return _jsx("div", { className: "flex justify-center items-center h-64", children: "Loading..." });
    }
    if (error || !bill) {
        return (_jsxs("div", { className: "container mx-auto px-4 py-8", children: [_jsx("div", { className: "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded", children: error || 'Bill not found' }), _jsx("div", { className: "mt-4", children: _jsx(Button, { onClick: () => navigate('/utility-bills'), children: "Back to bills" }) })] }));
    }
    // Calculate if the bill has an active reminder
    const hasReminder = bill.reminder_days > 0;
    // Format payments for the BillPaymentHistoryCard component
    const formattedPayments = bill.payments.map(payment => (Object.assign(Object.assign({}, payment), { reference_number: payment.reference_number || undefined, notes: payment.notes || undefined })));
    return (_jsxs("div", { className: "container mx-auto px-4 py-8", children: [_jsxs("div", { className: "flex justify-between items-center mb-6", children: [_jsx("h1", { className: "text-2xl font-bold", children: bill.name }), _jsxs("div", { className: "flex space-x-3", children: [_jsx(Link, { to: `/utility-bills/${id}/edit`, children: _jsx(Button, { variant: "outlined", children: "Edit" }) }), bill.status !== 'paid' && (_jsx(Button, { onClick: () => setShowPaymentModal(true), children: "Record Payment" }))] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-6 mb-6", children: [_jsx(Card, { className: "md:col-span-2", children: _jsx("div", { className: "p-6", children: _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsx("h2", { className: "text-xl font-semibold mb-4", children: "Bill Details" }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: [_jsxs("div", { className: "space-y-3", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Description" }), _jsx("p", { children: bill.description || 'No description provided' })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Account Number" }), _jsx("p", { children: bill.account_number || 'Not provided' })] }), bill.payment_method && (_jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Preferred Payment Method" }), _jsx("p", { className: "capitalize", children: bill.payment_method.replace(/_/g, ' ') })] }))] }), _jsxs("div", { className: "space-y-3", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Billing Period" }), _jsx("p", { children: "Monthly" })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Next Due Date" }), _jsx("p", { className: "font-medium", children: formatDate(bill.next_due_date) })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Total Paid" }), _jsx("p", { className: "font-medium", children: formatCurrency(bill.total_paid, bill.currency) })] })] })] })] }), _jsxs("div", { children: [_jsx("h3", { className: "text-lg font-medium mb-2", children: "Notes" }), _jsx(Card, { className: "bg-gray-50", children: _jsx("div", { className: "p-4", children: _jsx("p", { className: "text-gray-700", children: bill.description || 'No additional notes available.' }) }) })] })] }) }) }), _jsx("div", { className: "md:col-span-1", children: _jsx(BillSummaryCard, { name: bill.name, provider: bill.provider, category: bill.category, dueDate: bill.due_date, amount: bill.amount, currency: bill.currency, status: bill.status, reminderDays: bill.reminder_days, reminderDate: bill.reminder_date, hasReminder: hasReminder, onScheduleReminder: () => setShowReminderModal(true) }) })] }), _jsx("div", { className: "mb-6", children: _jsx(BillPaymentHistoryCard, { payments: formattedPayments, currency: bill.currency, onRecordPayment: () => setShowPaymentModal(true) }) }), _jsx(RecordBillPaymentModal, { isOpen: showPaymentModal, onClose: () => setShowPaymentModal(false), onSubmit: handleRecordPayment, initialAmount: bill.amount, currency: bill.currency, isLoading: isRecordingPayment, error: paymentError }), _jsx(ScheduleReminderModal, { isOpen: showReminderModal, onClose: () => setShowReminderModal(false), onSubmit: handleScheduleReminder, currentReminderDays: bill.reminder_days, dueDate: bill.due_date, isLoading: isSchedulingReminder, error: reminderError })] }));
};
export default UtilityBillDetail;

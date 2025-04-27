import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import PaymentHistoryCard from '../../components/subscriptions/PaymentHistoryCard';
import PaymentSummaryCard from '../../components/subscriptions/PaymentSummaryCard';
import RecordPaymentModal from '../../components/subscriptions/RecordPaymentModal';
import { useToast } from '../../ui/Toast';
import { useSubscriptionDetail, useSubscriptionPayments, useRecordPayment, useCancelSubscription } from '../../queries/subscriptionQueries';
import { useSubscriptionStore } from '../../store/subscriptionStore';
const SubscriptionDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { toast } = useToast();
    // State management with XState Store
    const { setError, setState } = useSubscriptionStore();
    // Local UI state
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [cancelDate, setCancelDate] = useState(new Date().toISOString().split('T')[0]);
    const [showPaymentModal, setShowPaymentModal] = useState(false);
    // TanStack Query hooks
    const { data: subscription, isLoading: isLoadingSubscription, error: subscriptionError } = useSubscriptionDetail(id);
    const { data: payments = [], isLoading: isLoadingPayments } = useSubscriptionPayments(id, !!subscription);
    const { mutate: recordPayment, isPending: isRecordingPayment, error: paymentError } = useRecordPayment();
    const { mutate: cancelSubscription, isPending: isCancelling, error: cancelError } = useCancelSubscription();
    const handleCancelSubscription = async () => {
        if (!id)
            return;
        cancelSubscription({ id, end_date: cancelDate }, {
            onSuccess: () => {
                toast({
                    title: "Success",
                    description: "Subscription cancelled successfully",
                    variant: "success",
                });
                setShowCancelModal(false);
            },
            onError: (err) => {
                toast({
                    title: "Error",
                    description: (err === null || err === void 0 ? void 0 : err.message) || 'Failed to cancel subscription',
                    variant: "destructive",
                });
            }
        });
    };
    const handleRecordPayment = async (paymentData) => {
        if (!id)
            return;
        recordPayment(Object.assign({ subscriptionId: id }, paymentData), {
            onSuccess: () => {
                toast({
                    title: "Success",
                    description: "Payment recorded successfully",
                    variant: "success",
                });
                setShowPaymentModal(false);
            },
            onError: (err) => {
                toast({
                    title: "Error",
                    description: (err === null || err === void 0 ? void 0 : err.message) || 'Failed to record payment',
                    variant: "destructive",
                });
            }
        });
    };
    const renderStatusBadge = (status) => {
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
        return (_jsx("span", { className: `px-2 py-1 text-xs font-medium rounded-full ${className}`, children: status.charAt(0).toUpperCase() + status.slice(1) }));
    };
    // Show loading state if either subscription or payments are loading
    const isLoading = isLoadingSubscription || isLoadingPayments;
    if (isLoading) {
        return (_jsx("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin" }) }) }));
    }
    if (subscriptionError || !subscription) {
        const errorMessage = subscriptionError instanceof Error ? subscriptionError.message : 'Subscription not found';
        return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: [_jsx("div", { className: "bg-error-container border border-error text-on-error-container px-4 py-3 rounded", children: errorMessage }), _jsx("div", { className: "mt-4", children: _jsx(Button, { onClick: () => navigate('/subscriptions'), variant: "filled", children: "Back to subscriptions" }) })] }));
    }
    // Transform payments to the format expected by PaymentHistoryCard
    const formattedPayments = payments.map(payment => ({
        id: payment.id,
        amount: payment.amount,
        date: payment.payment_date,
        status: 'paid', // Assuming all recorded payments are paid
        reference: payment.notes || undefined
    }));
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: [_jsxs("div", { className: "flex justify-between items-center mb-6", children: [_jsx("h1", { className: "text-3xl font-bold text-on-surface", children: subscription.name }), _jsxs("div", { className: "flex space-x-3", children: [_jsx(Link, { to: `/subscriptions/${id}/edit`, children: _jsx(Button, { variant: "outlined", children: "Edit" }) }), subscription.status === 'active' && (_jsx(Button, { variant: "outlined", className: "text-error border-error hover:bg-error-container/10", onClick: () => setShowCancelModal(true), children: "Cancel Subscription" }))] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-6 mb-6", children: [_jsxs(Card, { variant: "elevated", className: "md:col-span-2", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Subscription Details" }) }), _jsxs(CardContent, { children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: [_jsxs("div", { className: "space-y-4", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Status" }), _jsx("p", { className: "mt-1", children: renderStatusBadge(subscription.status) })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Description" }), _jsx("p", { className: "mt-1 text-on-surface", children: subscription.description })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Amount" }), _jsx("p", { className: "mt-1 font-semibold text-on-surface", children: formatCurrency(subscription.amount, subscription.currency) })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Billing Cycle" }), _jsx("p", { className: "mt-1 text-on-surface", children: subscription.billing_cycle.charAt(0).toUpperCase() + subscription.billing_cycle.slice(1) })] })] }), _jsxs("div", { className: "space-y-4", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Category" }), _jsx("p", { className: "mt-1 text-on-surface", children: subscription.category
                                                                    ? subscription.category.charAt(0).toUpperCase() + subscription.category.slice(1)
                                                                    : 'Not categorized' })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Start Date" }), _jsx("p", { className: "mt-1 text-on-surface", children: new Date(subscription.start_date).toLocaleDateString() })] }), subscription.end_date && (_jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "End Date" }), _jsx("p", { className: "mt-1 text-on-surface", children: new Date(subscription.end_date).toLocaleDateString() })] })), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Next Payment" }), _jsx("p", { className: "mt-1 text-on-surface", children: subscription.next_payment_date
                                                                    ? new Date(subscription.next_payment_date).toLocaleDateString()
                                                                    : 'Not scheduled' })] }), subscription.website && (_jsxs("div", { children: [_jsx("p", { className: "text-sm text-on-surface-variant", children: "Website" }), _jsx("a", { href: subscription.website, target: "_blank", rel: "noopener noreferrer", className: "mt-1 block text-primary hover:text-primary/80", children: subscription.website })] }))] })] }), _jsx("div", { className: "mt-6 pt-6 border-t border-outline border-opacity-20", children: _jsx("div", { className: "flex justify-end", children: _jsx(Button, { variant: "filled", onClick: () => setShowPaymentModal(true), disabled: subscription.status !== 'active', children: "Record Payment" }) }) })] })] }), _jsxs(Card, { variant: "elevated", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Payment Summary" }) }), _jsx(CardContent, { children: _jsx(PaymentSummaryCard, { amount: subscription.amount, currency: subscription.currency, billingCycle: subscription.billing_cycle, nextPaymentDate: subscription.next_payment_date, totalPaid: subscription.total_paid || 0, startDate: subscription.start_date, paymentCount: (payments === null || payments === void 0 ? void 0 : payments.length) || 0 }) })] })] }), payments.length > 0 ? (_jsxs(Card, { variant: "elevated", className: "mb-6", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Payment History" }) }), _jsx(CardContent, { children: _jsx(PaymentHistoryCard, { payments: formattedPayments, currency: subscription.currency }) })] })) : (_jsx(Card, { variant: "elevated", className: "mb-6", children: _jsx(CardContent, { className: "py-8", children: _jsx("p", { className: "text-center text-on-surface-variant", children: "No payment history available." }) }) })), showCancelModal && (_jsx("div", { className: "fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4", children: _jsx("div", { className: "bg-surface rounded-xl shadow-elevation-3 max-w-md w-full mx-auto", children: _jsxs("div", { className: "p-6", children: [_jsx("h3", { className: "text-headline-small font-medium text-on-surface mb-2", children: "Cancel Subscription" }), _jsxs("p", { className: "text-body-medium text-on-surface-variant mb-6", children: ["Are you sure you want to cancel your subscription to ", subscription.name, "?"] }), cancelError && (_jsx("div", { className: "mb-4 p-3 bg-error-container text-on-error-container rounded", children: cancelError.message || 'An error occurred' })), _jsxs("div", { className: "mb-6", children: [_jsx("label", { htmlFor: "cancel-date", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Cancellation Date" }), _jsx("input", { type: "date", id: "cancel-date", name: "cancel-date", value: cancelDate, onChange: (e) => setCancelDate(e.target.value), className: "w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface", min: new Date().toISOString().split('T')[0] })] }), _jsxs("div", { className: "flex justify-end space-x-3", children: [_jsx(Button, { variant: "text", onClick: () => setShowCancelModal(false), disabled: isCancelling, children: "Cancel" }), _jsx(Button, { variant: "filled", className: "bg-error text-on-error", onClick: handleCancelSubscription, disabled: isCancelling, children: isCancelling ? 'Cancelling...' : 'Confirm Cancellation' })] })] }) }) })), showPaymentModal && (_jsx(RecordPaymentModal, { onClose: () => setShowPaymentModal(false), onSubmit: handleRecordPayment, isSubmitting: isRecordingPayment, error: paymentError instanceof Error ? paymentError.message : null, defaultCurrency: subscription.currency, defaultAmount: subscription.amount }))] }));
};
export default SubscriptionDetail;

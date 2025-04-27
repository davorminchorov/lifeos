import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import PaymentForm from '../../components/payments/PaymentForm';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { usePaymentStore } from '../../store/paymentStore';
import { useSubscription } from '../../queries/paymentQueries';
const RecordPayment = () => {
    const { subscriptionId } = useParams();
    const navigate = useNavigate();
    const [state, actions] = usePaymentStore();
    // Use React Query to fetch subscription
    const { data: subscription, isLoading, error: queryError, isError } = useSubscription(subscriptionId);
    // Update subscription in store when fetched
    useEffect(() => {
        if (subscription) {
            actions.setSelectedSubscription({
                id: subscription.id,
                name: subscription.name,
                amount: subscription.amount,
                currency: subscription.currency
            });
            // Reset form with new subscription details
            actions.resetForm();
        }
    }, [subscription, actions]);
    // Set error in store if query fails
    useEffect(() => {
        if (queryError) {
            actions.setError('Failed to load subscription details');
            console.error(queryError);
        }
    }, [queryError, actions]);
    const handlePaymentSuccess = () => {
        navigate(`/subscriptions/${subscriptionId}`);
    };
    const handleCancel = () => {
        navigate(`/subscriptions/${subscriptionId}`);
    };
    if (isLoading) {
        return (_jsx("div", { className: "container mx-auto px-4 py-8", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) }) }));
    }
    if (isError || !subscription) {
        return (_jsx("div", { className: "container mx-auto px-4 py-8", children: _jsxs(Card, { className: "p-6", children: [_jsx("div", { className: "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4", children: state.error || 'Subscription not found' }), _jsx("div", { children: _jsx(Button, { onClick: () => navigate('/subscriptions'), children: "Back to subscriptions" }) })] }) }));
    }
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-2xl", children: [_jsx("h1", { className: "text-2xl font-bold mb-6", children: "Record Payment" }), _jsx(PaymentForm, { subscriptionId: subscription.id, subscriptionName: subscription.name, defaultAmount: subscription.amount, currency: subscription.currency, onSuccess: handlePaymentSuccess, onCancel: handleCancel })] }));
};
export default RecordPayment;

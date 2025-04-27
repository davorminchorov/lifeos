import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import SubscriptionCard from './SubscriptionCard';
const SubscriptionManager = ({ subscriptions, onManageSubscription, onCancelSubscription, onRenewSubscription, }) => {
    const [activeSubscriptions, setActiveSubscriptions] = useState(subscriptions.filter(sub => sub.status === 'active' || sub.status === 'trialing'));
    const [inactiveSubscriptions, setInactiveSubscriptions] = useState(subscriptions.filter(sub => sub.status !== 'active' && sub.status !== 'trialing'));
    const handleManageSubscription = (subscription) => {
        if (onManageSubscription) {
            onManageSubscription(subscription);
        }
    };
    const handleCancelSubscription = (subscription) => {
        if (onCancelSubscription) {
            onCancelSubscription(subscription);
        }
    };
    const handleRenewSubscription = (subscription) => {
        if (onRenewSubscription) {
            onRenewSubscription(subscription);
        }
    };
    return (_jsxs("div", { className: "space-y-8", children: [activeSubscriptions.length > 0 && (_jsxs("div", { children: [_jsx("h2", { className: "text-headline-medium font-medium text-on-surface mb-4", children: "Active Subscriptions" }), _jsx("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: activeSubscriptions.map(subscription => (_jsx(SubscriptionCard, { subscription: subscription, onManage: () => handleManageSubscription(subscription), onCancel: () => handleCancelSubscription(subscription) }, subscription.id))) })] })), inactiveSubscriptions.length > 0 && (_jsxs("div", { children: [_jsx("h2", { className: "text-headline-medium font-medium text-on-surface mb-4", children: "Inactive Subscriptions" }), _jsx("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: inactiveSubscriptions.map(subscription => (_jsx(SubscriptionCard, { subscription: subscription, onManage: () => handleManageSubscription(subscription), onRenew: () => handleRenewSubscription(subscription) }, subscription.id))) })] })), activeSubscriptions.length === 0 && inactiveSubscriptions.length === 0 && (_jsxs("div", { className: "text-center py-8 p-10 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1", children: [_jsx("p", { className: "text-headline-small text-on-surface-variant mb-4", children: "You don't have any subscriptions yet." }), _jsx("button", { className: "px-6 py-3 bg-primary text-on-primary rounded-full font-medium shadow-elevation-1 hover:shadow-elevation-2", children: "Browse Plans" })] }))] }));
};
export default SubscriptionManager;

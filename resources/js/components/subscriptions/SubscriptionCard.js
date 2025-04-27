import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
const SubscriptionCard = ({ subscription, onManage, onCancel, onRenew, }) => {
    const { name, description, status, currentPeriodEnd, price, interval, currency, features, } = subscription;
    const formatCurrency = (amount, currencyCode) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currencyCode,
        }).format(amount);
    };
    const getStatusColor = (status) => {
        switch (status) {
            case 'active':
            case 'trialing':
                return 'bg-tertiary-container text-on-tertiary-container';
            case 'past_due':
                return 'bg-secondary-container text-on-secondary-container';
            case 'canceled':
            case 'inactive':
                return 'bg-error-container text-on-error-container';
            default:
                return 'bg-surface-variant text-on-surface-variant';
        }
    };
    const getStatusLabel = (status) => {
        switch (status) {
            case 'active':
                return 'Active';
            case 'trialing':
                return 'Trial';
            case 'past_due':
                return 'Past Due';
            case 'canceled':
                return 'Canceled';
            case 'inactive':
                return 'Inactive';
            default:
                return 'Unknown';
        }
    };
    const formatDate = (dateString) => {
        if (!dateString)
            return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };
    const formatInterval = (price, interval) => {
        if (!interval)
            return formatCurrency(price, currency);
        switch (interval) {
            case 'month':
                return `${formatCurrency(price, currency)}/month`;
            case 'year':
                return `${formatCurrency(price, currency)}/year`;
            case 'week':
                return `${formatCurrency(price, currency)}/week`;
            case 'day':
                return `${formatCurrency(price, currency)}/day`;
            default:
                return formatCurrency(price, currency);
        }
    };
    const isActive = status === 'active' || status === 'trialing';
    return (_jsx("div", { className: "bg-surface rounded-lg shadow-elevation-2 border border-outline/40 overflow-hidden", children: _jsxs("div", { className: "p-6", children: [_jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { children: [_jsx("h3", { className: "text-headline-small font-medium text-on-surface", children: name }), description && _jsx("p", { className: "mt-1 text-body-medium text-on-surface-variant", children: description })] }), _jsx("span", { className: `px-3 py-1 rounded-full text-label-small font-medium shadow-elevation-1 ${getStatusColor(status)}`, children: getStatusLabel(status) })] }), _jsxs("div", { className: "mt-4 flex flex-wrap gap-5", children: [_jsxs("div", { children: [_jsx("p", { className: "text-body-small text-on-surface-variant", children: "Price" }), _jsx("p", { className: "text-body-large font-medium text-on-surface", children: formatInterval(price, interval) })] }), currentPeriodEnd && isActive && (_jsxs("div", { children: [_jsx("p", { className: "text-body-small text-on-surface-variant", children: "Renews on" }), _jsx("p", { className: "text-body-large font-medium text-on-surface", children: formatDate(currentPeriodEnd) })] }))] }), features && features.length > 0 && (_jsxs("div", { className: "mt-5", children: [_jsx("p", { className: "text-body-small font-medium text-on-surface-variant mb-2", children: "Included Features" }), _jsx("ul", { className: "list-disc list-inside text-on-surface space-y-1", children: features.map((feature, index) => (_jsx("li", { className: "text-body-medium", children: feature }, index))) })] })), _jsxs("div", { className: "mt-6 flex flex-wrap gap-3", children: [onManage && (_jsx("button", { onClick: onManage, className: "px-4 py-2 bg-primary text-on-primary rounded-full text-label-large font-medium shadow-elevation-1 hover:shadow-elevation-2", children: "Manage Subscription" })), isActive && onCancel && (_jsx("button", { onClick: onCancel, className: "px-4 py-2 border border-outline/50 text-on-surface rounded-full text-label-large font-medium hover:bg-surface-variant/20", children: "Cancel Subscription" })), !isActive && onRenew && (_jsx("button", { onClick: onRenew, className: "px-4 py-2 bg-primary text-on-primary rounded-full text-label-large font-medium shadow-elevation-1 hover:shadow-elevation-2", children: "Renew Subscription" }))] })] }) }));
};
export default SubscriptionCard;

import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';
const PaymentHistoryCard = ({ payments, currency }) => {
    const getStatusColor = (status) => {
        switch (status.toLowerCase()) {
            case 'paid':
            case 'completed':
                return 'bg-tertiary-container text-on-tertiary-container';
            case 'pending':
                return 'bg-secondary-container text-on-secondary-container';
            case 'failed':
            case 'declined':
                return 'bg-error-container text-on-error-container';
            default:
                return 'bg-surface-variant text-on-surface-variant';
        }
    };
    return (_jsxs(Card, { className: "shadow-elevation-2 border border-outline/40", children: [_jsx("div", { className: "px-6 py-4 border-b border-outline-variant/60", children: _jsx("h3", { className: "text-headline-small font-medium text-on-surface", children: "Payment History" }) }), _jsx("div", { className: "p-6", children: payments.length === 0 ? (_jsxs("div", { className: "py-8 flex flex-col items-center justify-center text-center border-2 border-dashed border-outline/40 rounded-lg bg-surface-container", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-12 w-12 text-on-surface-variant/40 mb-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" }) }), _jsx("p", { className: "text-body-medium text-on-surface-variant", children: "No payment records found" })] })) : (_jsx("div", { className: "divide-y divide-outline/40", children: payments.map((payment) => (_jsx("div", { className: "py-4 first:pt-0 last:pb-0", children: _jsxs("div", { className: "flex justify-between items-center", children: [_jsxs("div", { className: "space-y-1", children: [_jsx("p", { className: "text-body-large font-medium text-on-surface", children: formatDate(payment.date) }), payment.reference && (_jsxs("p", { className: "text-body-small text-on-surface-variant", children: ["Ref: ", payment.reference] }))] }), _jsxs("div", { className: "text-right space-y-2", children: [_jsx("p", { className: "text-body-large font-medium text-on-surface", children: formatCurrency(payment.amount, currency) }), _jsx("span", { className: `text-label-small px-2 py-1 rounded-full font-medium inline-block shadow-elevation-1 ${getStatusColor(payment.status)}`, children: payment.status })] })] }) }, payment.id))) })) })] }));
};
export default PaymentHistoryCard;

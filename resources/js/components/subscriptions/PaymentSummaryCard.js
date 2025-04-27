import { jsxs as _jsxs, jsx as _jsx } from "react/jsx-runtime";
import { formatCurrency } from '../../utils/format';
const PaymentSummaryCard = ({ amount, currency, billingCycle, nextPaymentDate, totalPaid, startDate, paymentCount, }) => {
    const formatBillingCycle = (cycle) => {
        if (!cycle)
            return 'Unknown';
        return cycle.charAt(0).toUpperCase() + cycle.slice(1);
    };
    const getAnnualCost = () => {
        let multiplier = 1;
        switch (billingCycle.toLowerCase()) {
            case 'monthly':
                multiplier = 12;
                break;
            case 'weekly':
                multiplier = 52;
                break;
            case 'quarterly':
                multiplier = 4;
                break;
            case 'biannually':
                multiplier = 2;
                break;
            case 'annually':
            default:
                multiplier = 1;
                break;
        }
        return amount * multiplier;
    };
    const formatStartDate = () => {
        if (!startDate)
            return 'Not specified';
        return new Date(startDate).toLocaleDateString();
    };
    return (_jsxs("div", { className: "space-y-5", children: [_jsxs("div", { children: [_jsxs("h3", { className: "text-sm font-medium text-on-surface-variant", children: ["Per ", formatBillingCycle(billingCycle)] }), _jsx("p", { className: "text-3xl font-bold text-on-surface mt-1", children: formatCurrency(amount, currency) })] }), billingCycle.toLowerCase() !== 'annually' && (_jsxs("div", { children: [_jsx("h3", { className: "text-sm font-medium text-on-surface-variant", children: "Annual Cost" }), _jsx("p", { className: "text-xl font-semibold text-on-surface mt-1", children: formatCurrency(getAnnualCost(), currency) })] })), _jsxs("div", { className: "pt-2 border-t border-outline/20", children: [_jsx("h3", { className: "text-sm font-medium text-on-surface-variant", children: "Next Payment" }), _jsx("p", { className: "text-base text-on-surface mt-1", children: nextPaymentDate
                            ? new Date(nextPaymentDate).toLocaleDateString()
                            : 'Not scheduled' })] }), _jsxs("div", { children: [_jsx("h3", { className: "text-sm font-medium text-on-surface-variant", children: "Since" }), _jsx("p", { className: "text-base text-on-surface mt-1", children: formatStartDate() })] }), _jsxs("div", { className: "pt-2 border-t border-outline/20", children: [_jsx("h3", { className: "text-sm font-medium text-on-surface-variant", children: "Total Paid" }), _jsxs("div", { className: "flex justify-between items-center mt-1", children: [_jsx("p", { className: "text-xl font-semibold text-on-surface", children: formatCurrency(totalPaid, currency) }), _jsxs("span", { className: "text-sm text-on-surface-variant", children: [paymentCount, " payment", paymentCount !== 1 ? 's' : ''] })] })] })] }));
};
export default PaymentSummaryCard;

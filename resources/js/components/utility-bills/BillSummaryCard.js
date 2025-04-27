import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';
const BillSummaryCard = ({ name, provider, category, dueDate, amount, currency, status, reminderDays, reminderDate, hasReminder, onScheduleReminder, }) => {
    const formatCategory = (category) => {
        return category.charAt(0).toUpperCase() + category.slice(1);
    };
    const renderStatusBadge = (status) => {
        let className = '';
        switch (status) {
            case 'paid':
                className = 'bg-green-100 text-green-800';
                break;
            case 'due':
                className = 'bg-yellow-100 text-yellow-800';
                break;
            case 'overdue':
                className = 'bg-red-100 text-red-800';
                break;
            case 'upcoming':
                className = 'bg-blue-100 text-blue-800';
                break;
            default:
                className = 'bg-gray-100 text-gray-800';
        }
        return (_jsx("span", { className: `px-2 py-1 text-xs font-medium rounded-full ${className}`, children: status.charAt(0).toUpperCase() + status.slice(1) }));
    };
    return (_jsxs(Card, { children: [_jsx("div", { className: "px-6 py-4 border-b border-gray-200", children: _jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Bill Summary" }) }), _jsxs("div", { className: "p-6 space-y-4", children: [_jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Status" }), _jsx("div", { className: "mt-1", children: renderStatusBadge(status) })] }), _jsxs("div", { className: "text-right", children: [_jsx("p", { className: "text-sm text-gray-500", children: "Amount" }), _jsx("p", { className: "text-xl font-semibold", children: amount !== null ? formatCurrency(amount, currency) : 'Variable' })] })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Provider" }), _jsx("p", { children: provider })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Category" }), _jsx("p", { children: formatCategory(category) })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Due Date" }), _jsx("p", { className: "font-medium", children: formatDate(dueDate) })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500", children: "Reminder" }), hasReminder ? (_jsxs("div", { children: [_jsxs("p", { children: [reminderDays, " days before due date"] }), reminderDate && (_jsxs("p", { className: "text-sm text-gray-500", children: ["Reminder scheduled for ", formatDate(reminderDate)] }))] })) : (_jsx("p", { className: "text-gray-500", children: "No reminder set" }))] }), _jsx("div", { className: "pt-2", children: _jsx(Button, { size: "sm", variant: hasReminder ? "outlined" : "contained", onClick: onScheduleReminder, children: hasReminder ? 'Edit Reminder' : 'Schedule Reminder' }) })] })] }));
};
export default BillSummaryCard;

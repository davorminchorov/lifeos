import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency } from '../../utils/format';
import { Card } from '../../ui/Card';
const SubscriptionSummaryWidget = () => {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        const fetchSummary = async () => {
            try {
                setLoading(true);
                const response = await axios.get('/api/dashboard/subscriptions-summary');
                setSummary(response.data);
            }
            catch (err) {
                console.error('Failed to fetch subscription summary', err);
                setError('Failed to load subscription data');
            }
            finally {
                setLoading(false);
            }
        };
        fetchSummary();
    }, []);
    if (loading) {
        return (_jsx(Card, { className: "h-full", children: _jsx("div", { className: "p-6", children: _jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-6 bg-gray-200 rounded w-1/3" }), _jsx("div", { className: "h-10 bg-gray-200 rounded w-1/2" }), _jsx("div", { className: "h-4 bg-gray-200 rounded w-3/4" }), _jsx("div", { className: "h-4 bg-gray-200 rounded w-2/3" }), _jsxs("div", { className: "space-y-2", children: [_jsx("div", { className: "h-4 bg-gray-200 rounded" }), _jsx("div", { className: "h-4 bg-gray-200 rounded" }), _jsx("div", { className: "h-4 bg-gray-200 rounded" })] })] }) }) }));
    }
    if (error || !summary) {
        return (_jsx(Card, { className: "h-full", children: _jsxs("div", { className: "p-6", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900 mb-2", children: "Subscriptions" }), _jsx("p", { className: "text-red-500", children: error || 'Failed to load data' })] }) }));
    }
    return (_jsxs(Card, { className: "h-full", children: [_jsxs("div", { className: "px-6 py-4 border-b border-gray-200 flex justify-between items-center", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Subscriptions" }), _jsx(Link, { to: "/subscriptions", className: "text-sm text-indigo-600 hover:text-indigo-800", children: "View all" })] }), _jsxs("div", { className: "p-6", children: [_jsxs("div", { className: "grid grid-cols-2 gap-4 mb-6", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Active Subscriptions" }), _jsx("p", { className: "text-2xl font-bold", children: summary.active_count })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Monthly Cost" }), _jsx("p", { className: "text-2xl font-bold text-indigo-600", children: formatCurrency(summary.monthly_total, summary.currency) })] }), _jsxs("div", { className: "col-span-2", children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Annual Cost" }), _jsx("p", { className: "text-xl font-semibold", children: formatCurrency(summary.annual_total, summary.currency) })] })] }), summary.upcoming_payments.length > 0 ? (_jsxs("div", { children: [_jsx("h4", { className: "text-sm font-medium text-gray-700 mb-2", children: "Upcoming Payments" }), _jsx("ul", { className: "divide-y divide-gray-200", children: summary.upcoming_payments.slice(0, 3).map((payment) => (_jsx("li", { className: "py-2", children: _jsxs(Link, { to: `/subscriptions/${payment.id}`, className: "block hover:bg-gray-50 -mx-2 px-2 py-1 rounded", children: [_jsxs("div", { className: "flex justify-between items-center", children: [_jsx("p", { className: "text-sm font-medium text-gray-900", children: payment.name }), _jsx("p", { className: "text-sm font-medium text-gray-700", children: formatCurrency(payment.amount, payment.currency) })] }), _jsxs("p", { className: "text-xs text-gray-500", children: ["Due on ", new Date(payment.due_date).toLocaleDateString()] })] }) }, payment.id))) }), summary.upcoming_payments.length > 3 && (_jsx("div", { className: "mt-3 text-center", children: _jsxs(Link, { to: "/subscriptions", className: "text-xs text-indigo-600 hover:text-indigo-800", children: ["View ", summary.upcoming_payments.length - 3, " more"] }) }))] })) : (_jsx("p", { className: "text-sm text-gray-500 text-center py-2", children: "No upcoming payments scheduled" }))] })] }));
};
export default SubscriptionSummaryWidget;

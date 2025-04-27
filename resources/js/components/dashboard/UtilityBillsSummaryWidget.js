import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';
const UtilityBillsSummaryWidget = () => {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        const fetchSummary = async () => {
            try {
                setLoading(true);
                const response = await axios.get('/api/dashboard/utility-bills-summary');
                setSummary(response.data);
            }
            catch (err) {
                console.error('Failed to fetch utility bills summary', err);
                setError('Failed to load utility bills data');
            }
            finally {
                setLoading(false);
            }
        };
        fetchSummary();
    }, []);
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
    if (loading) {
        return (_jsx(Card, { className: "h-full", children: _jsx("div", { className: "p-6", children: _jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-6 bg-gray-200 rounded w-1/3" }), _jsx("div", { className: "h-10 bg-gray-200 rounded w-1/2" }), _jsx("div", { className: "h-4 bg-gray-200 rounded w-3/4" }), _jsx("div", { className: "h-4 bg-gray-200 rounded w-2/3" }), _jsxs("div", { className: "space-y-2", children: [_jsx("div", { className: "h-4 bg-gray-200 rounded" }), _jsx("div", { className: "h-4 bg-gray-200 rounded" }), _jsx("div", { className: "h-4 bg-gray-200 rounded" })] })] }) }) }));
    }
    if (error || !summary) {
        return (_jsx(Card, { className: "h-full", children: _jsxs("div", { className: "p-6", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900 mb-2", children: "Utility Bills" }), _jsx("p", { className: "text-red-500", children: error || 'Failed to load data' })] }) }));
    }
    return (_jsxs(Card, { className: "h-full", children: [_jsxs("div", { className: "px-6 py-4 border-b border-gray-200 flex justify-between items-center", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Utility Bills" }), _jsx(Link, { to: "/utility-bills", className: "text-sm text-indigo-600 hover:text-indigo-800", children: "View all" })] }), _jsxs("div", { className: "p-6", children: [_jsxs("div", { className: "grid grid-cols-2 gap-4 mb-6", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Total Bills" }), _jsx("p", { className: "text-2xl font-bold", children: summary.total_count })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Monthly Cost" }), _jsx("p", { className: "text-2xl font-bold text-indigo-600", children: formatCurrency(summary.monthly_total, summary.currency) })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Overdue" }), _jsx("p", { className: `text-lg font-semibold ${summary.overdue_count > 0 ? 'text-red-600' : 'text-gray-700'}`, children: summary.overdue_count })] }), _jsxs("div", { children: [_jsx("p", { className: "text-sm text-gray-500 mb-1", children: "Due Soon" }), _jsx("p", { className: `text-lg font-semibold ${summary.due_soon_count > 0 ? 'text-yellow-600' : 'text-gray-700'}`, children: summary.due_soon_count })] })] }), summary.upcoming_bills.length > 0 ? (_jsxs("div", { children: [_jsx("h4", { className: "text-sm font-medium text-gray-700 mb-2", children: "Upcoming Bills" }), _jsx("ul", { className: "divide-y divide-gray-200", children: summary.upcoming_bills.slice(0, 3).map((bill) => (_jsx("li", { className: "py-2", children: _jsxs(Link, { to: `/utility-bills/${bill.id}`, className: "block hover:bg-gray-50 -mx-2 px-2 py-1 rounded", children: [_jsxs("div", { className: "flex justify-between items-center", children: [_jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-900", children: bill.name }), _jsx("p", { className: "text-xs text-gray-500", children: bill.provider })] }), _jsxs("div", { className: "text-right", children: [_jsx("p", { className: "text-sm font-medium text-gray-700", children: bill.amount !== null ? formatCurrency(bill.amount, bill.currency) : 'Variable' }), _jsx("div", { className: "mt-1", children: renderStatusBadge(bill.status) })] })] }), _jsxs("p", { className: "text-xs text-gray-500 mt-1", children: ["Due on ", formatDate(bill.due_date)] })] }) }, bill.id))) }), summary.upcoming_bills.length > 3 && (_jsx("div", { className: "mt-3 text-center", children: _jsxs(Link, { to: "/utility-bills", className: "text-xs text-indigo-600 hover:text-indigo-800", children: ["View ", summary.upcoming_bills.length - 3, " more"] }) }))] })) : (_jsx("p", { className: "text-sm text-gray-500 text-center py-2", children: "No upcoming bills due" }))] })] }));
};
export default UtilityBillsSummaryWidget;

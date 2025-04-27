import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
const UtilityBillsList = () => {
    const [bills, setBills] = useState([]);
    const [meta, setMeta] = useState({
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filters, setFilters] = useState({
        status: '',
        category: '',
        search: '',
    });
    const fetchBills = async (page = 1) => {
        setLoading(true);
        try {
            const params = new URLSearchParams(Object.assign({ page: page.toString() }, filters));
            const response = await axios.get(`/api/utility-bills?${params}`);
            setBills(response.data.data || []);
            setMeta(response.data.meta || {
                current_page: 1,
                per_page: 10,
                total: 0,
                last_page: 1,
            });
            setError(null);
        }
        catch (err) {
            setError('Failed to load utility bills');
            console.error(err);
        }
        finally {
            setLoading(false);
        }
    };
    useEffect(() => {
        fetchBills();
    }, [filters]);
    const handlePageChange = (page) => {
        fetchBills(page);
    };
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => (Object.assign(Object.assign({}, prev), { [name]: value })));
    };
    const handleSearch = (e) => {
        e.preventDefault();
        fetchBills();
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
    const categoryOptions = [
        { value: '', label: 'All Categories' },
        { value: 'electricity', label: 'Electricity' },
        { value: 'water', label: 'Water' },
        { value: 'gas', label: 'Gas' },
        { value: 'internet', label: 'Internet' },
        { value: 'phone', label: 'Phone' },
        { value: 'rent', label: 'Rent' },
        { value: 'mortgage', label: 'Mortgage' },
        { value: 'other', label: 'Other' },
    ];
    const statusOptions = [
        { value: '', label: 'All Statuses' },
        { value: 'paid', label: 'Paid' },
        { value: 'due', label: 'Due' },
        { value: 'overdue', label: 'Overdue' },
        { value: 'upcoming', label: 'Upcoming' },
    ];
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: [_jsxs("div", { className: "flex flex-col space-y-4 mb-8", children: [_jsxs("div", { className: "flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4", children: [_jsx("div", { children: _jsx("h1", { className: "text-3xl font-bold mb-2 sm:mb-0", children: "Utility Bills" }) }), _jsx(Link, { to: "/utility-bills/create", children: _jsx(Button, { children: "Add Utility Bill" }) })] }), _jsx("p", { className: "text-gray-600", children: "Track and manage your recurring utility bills and payment history." })] }), _jsxs(Card, { className: "mb-6 border border-gray-200 shadow-sm", children: [_jsxs("form", { onSubmit: handleSearch, className: "grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-t-lg border-b border-gray-200", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "status", className: "block text-sm font-medium text-gray-700 mb-1", children: "Status" }), _jsx("select", { id: "status", name: "status", value: filters.status, onChange: handleFilterChange, className: "w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white", children: statusOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value))) })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "category", className: "block text-sm font-medium text-gray-700 mb-1", children: "Category" }), _jsx("select", { id: "category", name: "category", value: filters.category, onChange: handleFilterChange, className: "w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white", children: categoryOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value))) })] }), _jsxs("div", { className: "flex flex-col", children: [_jsx("label", { htmlFor: "search", className: "block text-sm font-medium text-gray-700 mb-1", children: "Search" }), _jsxs("div", { className: "flex", children: [_jsx("input", { type: "text", id: "search", name: "search", value: filters.search, onChange: handleFilterChange, placeholder: "Search bills...", className: "w-full border border-gray-300 rounded-l-md shadow-sm p-2 bg-white" }), _jsx(Button, { type: "submit", className: "rounded-l-none", children: "Filter" })] })] })] }), error && (_jsx("div", { className: "bg-red-100 border-y border-red-400 text-red-700 px-4 py-3", children: error })), loading && bills.length === 0 ? (_jsxs("div", { className: "p-8 flex flex-col items-center justify-center", children: [_jsxs("div", { className: "animate-pulse space-y-4 w-full max-w-3xl", children: [_jsx("div", { className: "h-8 bg-gray-200 rounded w-1/3" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" })] }), _jsx("p", { className: "text-gray-500 mt-4", children: "Loading bills..." })] })) : bills.length === 0 ? (_jsxs("div", { className: "flex flex-col items-center justify-center p-10", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-16 w-16 text-gray-300 mb-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" }) }), _jsx("p", { className: "text-lg font-medium text-gray-600 mb-1", children: "No utility bills found" }), _jsx("p", { className: "text-gray-500 text-center mb-4", children: "Start tracking your recurring bills by adding your first utility bill." }), _jsx(Link, { to: "/utility-bills/create", children: _jsx(Button, { size: "sm", children: "Add Your First Bill" }) })] })) : (_jsx("div", { className: "overflow-x-auto", children: _jsxs("table", { className: "min-w-full", children: [_jsx("thead", { className: "bg-gray-50", children: _jsxs("tr", { children: [_jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Name" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Provider" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Category" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Amount" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Due Date" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Status" }), _jsx("th", { className: "px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Actions" })] }) }), _jsx("tbody", { className: "bg-white divide-y divide-gray-200", children: bills.map((bill) => (_jsxs("tr", { className: "hover:bg-gray-50", children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: _jsx("div", { className: "font-medium text-gray-900", children: bill.name }) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: bill.provider }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: _jsx("span", { className: "capitalize", children: bill.category }) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: bill.amount !== null ? (_jsx("div", { className: "text-sm font-medium text-gray-900", children: formatCurrency(bill.amount, bill.currency) })) : (_jsx("div", { className: "text-sm text-gray-500", children: "Variable" })) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: formatDate(bill.next_due_date) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: renderStatusBadge(bill.status) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-right text-sm font-medium", children: _jsxs("div", { className: "flex justify-end space-x-2", children: [_jsx(Link, { to: `/utility-bills/${bill.id}`, className: "text-indigo-600 hover:text-indigo-900", children: "View" }), _jsx(Link, { to: `/utility-bills/${bill.id}/edit`, className: "text-indigo-600 hover:text-indigo-900", children: "Edit" })] }) })] }, bill.id))) })] }) })), meta.last_page > 1 && (_jsxs("div", { className: "px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6", children: [_jsxs("div", { className: "flex-1 flex justify-between sm:hidden", children: [_jsx("button", { onClick: () => handlePageChange(meta.current_page - 1), disabled: meta.current_page === 1, className: `relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md ${meta.current_page === 1
                                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                            : 'bg-white text-gray-700 hover:bg-gray-50'}`, children: "Previous" }), _jsx("button", { onClick: () => handlePageChange(meta.current_page + 1), disabled: meta.current_page === meta.last_page, className: `relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md ${meta.current_page === meta.last_page
                                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                            : 'bg-white text-gray-700 hover:bg-gray-50'}`, children: "Next" })] }), _jsxs("div", { className: "hidden sm:flex-1 sm:flex sm:items-center sm:justify-between", children: [_jsx("div", { children: _jsxs("p", { className: "text-sm text-gray-700", children: ["Showing ", _jsx("span", { className: "font-medium", children: (meta.current_page - 1) * meta.per_page + 1 }), " to", ' ', _jsx("span", { className: "font-medium", children: Math.min(meta.current_page * meta.per_page, meta.total) }), ' ', "of ", _jsx("span", { className: "font-medium", children: meta.total }), " results"] }) }), _jsx("div", { children: _jsxs("nav", { className: "relative z-0 inline-flex rounded-md shadow-sm -space-x-px", "aria-label": "Pagination", children: [_jsxs("button", { onClick: () => handlePageChange(meta.current_page - 1), disabled: meta.current_page === 1, className: `relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium ${meta.current_page === 1
                                                        ? 'text-gray-300 cursor-not-allowed'
                                                        : 'text-gray-500 hover:bg-gray-50'}`, children: [_jsx("span", { className: "sr-only", children: "Previous" }), _jsx("svg", { className: "h-5 w-5", xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true", children: _jsx("path", { fillRule: "evenodd", d: "M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z", clipRule: "evenodd" }) })] }), _jsxs("button", { onClick: () => handlePageChange(meta.current_page + 1), disabled: meta.current_page === meta.last_page, className: `relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium ${meta.current_page === meta.last_page
                                                        ? 'text-gray-300 cursor-not-allowed'
                                                        : 'text-gray-500 hover:bg-gray-50'}`, children: [_jsx("span", { className: "sr-only", children: "Next" }), _jsx("svg", { className: "h-5 w-5", xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true", children: _jsx("path", { fillRule: "evenodd", d: "M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z", clipRule: "evenodd" }) })] })] }) })] })] }))] })] }));
};
export default UtilityBillsList;

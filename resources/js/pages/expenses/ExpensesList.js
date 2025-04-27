import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { useToast } from '../../ui/Toast';
const ExpensesList = () => {
    const { toast } = useToast();
    const [expenses, setExpenses] = useState([]);
    const [categories, setCategories] = useState([]);
    const [meta, setMeta] = useState({
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    });
    const [loading, setLoading] = useState(true);
    const [categoriesLoading, setCategoriesLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filters, setFilters] = useState({
        category_id: '',
        date_from: '',
        date_to: '',
        search: '',
        sort_by: 'date',
        sort_order: 'desc',
    });
    useEffect(() => {
        fetchCategories();
        fetchExpenses();
    }, []);
    useEffect(() => {
        fetchExpenses();
    }, [filters]);
    const fetchCategories = async () => {
        setCategoriesLoading(true);
        try {
            const response = await axios.get('/api/categories');
            const categoriesData = response.data.data || [];
            setCategories(categoriesData);
        }
        catch (err) {
            console.error('Failed to load categories:', err);
            toast({
                title: "Error",
                description: "Failed to load categories",
                variant: "destructive",
            });
        }
        finally {
            setCategoriesLoading(false);
        }
    };
    const fetchExpenses = async (page = 1) => {
        setLoading(true);
        try {
            const params = new URLSearchParams(Object.assign({ page: page.toString(), per_page: '10' }, filters)).toString();
            const response = await axios.get(`/api/expenses?${params}`);
            setExpenses(response.data.data || []);
            setMeta(response.data.meta || {
                current_page: 1,
                per_page: 10,
                total: 0,
                last_page: 1,
            });
            setError(null);
        }
        catch (err) {
            setError('Failed to load expenses');
            console.error(err);
            toast({
                title: "Error",
                description: "Failed to load expenses",
                variant: "destructive",
            });
        }
        finally {
            setLoading(false);
        }
    };
    const handlePageChange = (page) => {
        fetchExpenses(page);
    };
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => (Object.assign(Object.assign({}, prev), { [name]: value })));
    };
    const handleSortChange = (sortField) => {
        setFilters(prev => {
            if (prev.sort_by === sortField) {
                // Toggle sort order if clicking the same field
                return Object.assign(Object.assign({}, prev), { sort_order: prev.sort_order === 'asc' ? 'desc' : 'asc' });
            }
            else {
                // Default to descending for a new sort field
                return Object.assign(Object.assign({}, prev), { sort_by: sortField, sort_order: 'desc' });
            }
        });
    };
    const handleSearch = (e) => {
        e.preventDefault();
        fetchExpenses();
    };
    const formatPaymentMethod = (method) => {
        if (!method)
            return '-';
        return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };
    const getSortIndicator = (field) => {
        if (filters.sort_by !== field)
            return null;
        return filters.sort_order === 'asc'
            ? '↑'
            : '↓';
    };
    const getCategoryStyle = (category) => {
        if (!category) {
            return {
                backgroundColor: '#e5e7eb',
                color: '#374151'
            };
        }
        return {
            backgroundColor: category.color || '#e5e7eb',
            color: getContrastColor(category.color || '#e5e7eb')
        };
    };
    // Helper function to determine text color based on background color
    const getContrastColor = (hexColor) => {
        // Convert hex to RGB
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);
        // Calculate luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        // Return black or white based on luminance
        return luminance > 0.5 ? '#000000' : '#ffffff';
    };
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: [_jsxs("div", { className: "flex flex-col space-y-4 mb-8", children: [_jsxs("div", { className: "flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4", children: [_jsx("div", { children: _jsx("h1", { className: "text-3xl font-bold mb-2 sm:mb-0", children: "Expenses" }) }), _jsx(Link, { to: "/expenses/create", children: _jsx(Button, { children: "Add Expense" }) })] }), _jsx("p", { className: "text-gray-600", children: "Track and manage your expenses." })] }), _jsxs(Card, { className: "mb-6 border border-gray-200 shadow-sm", children: [_jsxs("form", { onSubmit: handleSearch, className: "grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-t-lg border-b border-gray-200", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "category_id", className: "block text-sm font-medium text-gray-700 mb-1", children: "Category" }), _jsxs("select", { id: "category_id", name: "category_id", value: filters.category_id, onChange: handleFilterChange, className: "w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white", disabled: categoriesLoading, children: [_jsx("option", { value: "", children: "All Categories" }), categoriesLoading ? (_jsx("option", { disabled: true, children: "Loading categories..." })) : (categories.map(category => (_jsx("option", { value: category.id, children: category.name }, category.id))))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "date_from", className: "block text-sm font-medium text-gray-700 mb-1", children: "From Date" }), _jsx("input", { type: "date", id: "date_from", name: "date_from", value: filters.date_from, onChange: handleFilterChange, className: "w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "date_to", className: "block text-sm font-medium text-gray-700 mb-1", children: "To Date" }), _jsx("input", { type: "date", id: "date_to", name: "date_to", value: filters.date_to, onChange: handleFilterChange, className: "w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white" })] }), _jsxs("div", { className: "flex flex-col", children: [_jsx("label", { htmlFor: "search", className: "block text-sm font-medium text-gray-700 mb-1", children: "Search" }), _jsxs("div", { className: "flex", children: [_jsx("input", { type: "text", id: "search", name: "search", value: filters.search, onChange: handleFilterChange, placeholder: "Search expenses...", className: "w-full border border-gray-300 rounded-l-md shadow-sm p-2 bg-white" }), _jsx(Button, { type: "submit", className: "rounded-l-none", children: "Filter" })] })] })] }), error && (_jsx("div", { className: "bg-red-100 border-y border-red-400 text-red-700 px-4 py-3", children: error })), loading ? (_jsxs("div", { className: "p-8 flex flex-col items-center justify-center", children: [_jsxs("div", { className: "animate-pulse space-y-4 w-full max-w-3xl", children: [_jsx("div", { className: "h-8 bg-gray-200 rounded w-1/3" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-full" })] }), _jsx("p", { className: "text-gray-500 mt-4", children: "Loading expenses..." })] })) : expenses.length === 0 ? (_jsxs("div", { className: "flex flex-col items-center justify-center p-10", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-16 w-16 text-gray-300 mb-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" }) }), _jsx("p", { className: "text-lg font-medium text-gray-600 mb-1", children: "No expenses found" }), _jsx("p", { className: "text-gray-500 text-center mb-4", children: "Start tracking your expenses by adding your first record." }), _jsx(Link, { to: "/expenses/create", children: _jsx(Button, { size: "sm", children: "Add Your First Expense" }) })] })) : (_jsx("div", { className: "overflow-x-auto", children: _jsxs("table", { className: "min-w-full", children: [_jsx("thead", { className: "bg-gray-50", children: _jsxs("tr", { children: [_jsxs("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer", onClick: () => handleSortChange('date'), children: ["Date ", getSortIndicator('date')] }), _jsxs("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer", onClick: () => handleSortChange('title'), children: ["Title ", getSortIndicator('title')] }), _jsxs("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer", onClick: () => handleSortChange('category_id'), children: ["Category ", getSortIndicator('category_id')] }), _jsxs("th", { className: "px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer", onClick: () => handleSortChange('amount'), children: ["Amount ", getSortIndicator('amount')] }), _jsxs("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer", onClick: () => handleSortChange('payment_method'), children: ["Payment Method ", getSortIndicator('payment_method')] }), _jsx("th", { className: "px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Actions" })] }) }), _jsx("tbody", { className: "bg-white divide-y divide-gray-200", children: expenses.map((expense) => (_jsxs("tr", { className: "hover:bg-gray-50", children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: new Date(expense.date).toLocaleDateString() }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap", children: [_jsx("div", { className: "text-sm font-medium text-gray-900", children: expense.title }), expense.description && (_jsx("div", { className: "text-xs text-gray-500 truncate max-w-xs", children: expense.description }))] }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: expense.category ? (_jsx("span", { className: "px-2 py-1 text-xs rounded-full", style: getCategoryStyle(expense.category), children: expense.category.name })) : (_jsx("span", { className: "px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800", children: "Uncategorized" })) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-right font-medium", children: formatCurrency(expense.amount, expense.currency) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: formatPaymentMethod(expense.payment_method) }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap text-right text-sm font-medium", children: [_jsx(Link, { to: `/expenses/${expense.id}`, className: "text-indigo-600 hover:text-indigo-900 mr-4", children: "View" }), _jsx(Link, { to: `/expenses/${expense.id}/edit`, className: "text-indigo-600 hover:text-indigo-900", children: "Edit" })] })] }, expense.id))) })] }) })), !loading && meta.total > 0 && meta.last_page > 1 && (_jsx("div", { className: "bg-white px-4 py-3 flex items-center justify-center sm:px-6", children: _jsxs("nav", { className: "relative z-0 inline-flex rounded-md shadow-sm -space-x-px", "aria-label": "Pagination", children: [_jsxs("button", { onClick: () => meta.current_page > 1 && handlePageChange(meta.current_page - 1), disabled: meta.current_page === 1, className: `relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium ${meta.current_page === 1
                                        ? 'text-gray-300 cursor-not-allowed'
                                        : 'text-gray-500 hover:bg-gray-50'}`, children: [_jsx("span", { className: "sr-only", children: "Previous" }), _jsx("svg", { className: "h-5 w-5", xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true", children: _jsx("path", { fillRule: "evenodd", d: "M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z", clipRule: "evenodd" }) })] }), Array.from({ length: meta.last_page }, (_, i) => i + 1).map((page) => (_jsx("button", { onClick: () => handlePageChange(page), "aria-current": meta.current_page === page ? 'page' : undefined, className: `relative inline-flex items-center px-4 py-2 border text-sm font-medium ${meta.current_page === page
                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'}`, children: page }, page))), _jsxs("button", { onClick: () => meta.current_page < meta.last_page && handlePageChange(meta.current_page + 1), disabled: meta.current_page === meta.last_page, className: `relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium ${meta.current_page === meta.last_page
                                        ? 'text-gray-300 cursor-not-allowed'
                                        : 'text-gray-500 hover:bg-gray-50'}`, children: [_jsx("span", { className: "sr-only", children: "Next" }), _jsx("svg", { className: "h-5 w-5", xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true", children: _jsx("path", { fillRule: "evenodd", d: "M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z", clipRule: "evenodd" }) })] })] }) }))] })] }));
};
export default ExpensesList;

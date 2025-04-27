import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
import { format } from 'date-fns';
import { Button } from '../../ui';
export const ExpensesList = ({ refreshTrigger = 0 }) => {
    const [expenses, setExpenses] = useState([]);
    const [categories, setCategories] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [filterCategory, setFilterCategory] = useState('');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    useEffect(() => {
        // Fetch categories first to display names
        const fetchCategories = async () => {
            try {
                const response = await axios.get('/api/categories');
                const categoriesMap = {};
                if (response.data && Array.isArray(response.data.data)) {
                    response.data.data.forEach((category) => {
                        categoriesMap[category.category_id] = category;
                    });
                }
                setCategories(categoriesMap);
            }
            catch (err) {
                console.error('Failed to fetch categories', err);
            }
        };
        fetchCategories();
    }, []);
    useEffect(() => {
        fetchExpenses();
    }, [refreshTrigger, filterCategory, startDate, endDate]);
    const fetchExpenses = async () => {
        setLoading(true);
        setError('');
        try {
            const params = {};
            if (filterCategory) {
                params.category_id = filterCategory;
            }
            if (startDate) {
                params.start_date = startDate;
            }
            if (endDate) {
                params.end_date = endDate;
            }
            const response = await axios.get('/api/expenses', { params });
            if (response.data && Array.isArray(response.data.data)) {
                // Add category names to expenses
                const expensesWithCategories = response.data.data.map((expense) => {
                    var _a;
                    return (Object.assign(Object.assign({}, expense), { category_name: expense.category_id ? (_a = categories[expense.category_id]) === null || _a === void 0 ? void 0 : _a.name : 'Uncategorized' }));
                });
                setExpenses(expensesWithCategories);
            }
            else {
                setExpenses([]);
            }
        }
        catch (err) {
            setError('Failed to load expenses');
            console.error('Failed to fetch expenses', err);
            setExpenses([]);
        }
        finally {
            setLoading(false);
        }
    };
    const handleCategorize = async (expenseId, categoryId) => {
        try {
            await axios.post(`/api/expenses/${expenseId}/categorize`, {
                category_id: categoryId,
            });
            // Update the local state
            setExpenses(expenses.map(expense => expense.expense_id === expenseId
                ? Object.assign(Object.assign({}, expense), { category_id: categoryId, category_name: categories[categoryId].name }) : expense));
        }
        catch (err) {
            console.error('Failed to categorize expense', err);
        }
    };
    const formatAmount = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };
    const formatDate = (dateString) => {
        return format(new Date(dateString), 'MMM d, yyyy');
    };
    // Add export function
    const handleExport = () => {
        let url = '/api/expense-exports/csv';
        // Add any filters that are currently applied
        const params = [];
        if (filterCategory) {
            params.push(`category_id=${filterCategory}`);
        }
        if (startDate) {
            params.push(`start_date=${startDate}`);
        }
        if (endDate) {
            params.push(`end_date=${endDate}`);
        }
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        // Open in a new tab
        window.open(url, '_blank');
    };
    return (_jsxs("div", { children: [_jsxs("div", { className: "flex justify-between items-center mb-4", children: [_jsx("h2", { className: "text-title-medium font-medium text-on-surface", children: "Your Expenses" }), _jsxs("div", { className: "flex items-center space-x-2", children: [_jsx(Button, { onClick: fetchExpenses, variant: "text", icon: _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z", clipRule: "evenodd" }) }), size: "sm", children: "Refresh" }), _jsx(Button, { onClick: handleExport, variant: "text", size: "sm", icon: _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z", clipRule: "evenodd" }) }), children: "Export CSV" })] })] }), _jsx("div", { className: "mb-6 bg-surface-container rounded-lg p-4 border border-outline/40 shadow-elevation-1", children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-4", children: [_jsxs("div", { children: [_jsx("label", { className: "block text-body-small font-medium text-on-surface-variant mb-1", children: "Category" }), _jsxs("select", { value: filterCategory, onChange: (e) => setFilterCategory(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", children: [_jsx("option", { value: "", children: "All Categories" }), Object.values(categories).map((category) => (_jsx("option", { value: category.category_id, children: category.name }, category.category_id)))] })] }), _jsxs("div", { children: [_jsx("label", { className: "block text-body-small font-medium text-on-surface-variant mb-1", children: "Start Date" }), _jsx("input", { type: "date", value: startDate, onChange: (e) => setStartDate(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary" })] }), _jsxs("div", { children: [_jsx("label", { className: "block text-body-small font-medium text-on-surface-variant mb-1", children: "End Date" }), _jsx("input", { type: "date", value: endDate, onChange: (e) => setEndDate(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary" })] })] }) }), loading ? (_jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-8 bg-surface-variant/40 rounded w-full" }), _jsx("div", { className: "h-8 bg-surface-variant/40 rounded w-full" }), _jsx("div", { className: "h-8 bg-surface-variant/40 rounded w-full" })] })) : error ? (_jsxs("div", { className: "p-8 text-center text-error bg-error-container/50 rounded-lg border border-error/50 shadow-elevation-1", children: [error, _jsx("button", { onClick: fetchExpenses, className: "block mx-auto mt-2 text-body-small text-primary hover:text-primary/80", children: "Try Again" })] })) : expenses.length === 0 ? (_jsxs("div", { className: "flex flex-col items-center justify-center p-10 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-16 w-16 text-on-surface-variant/40 mb-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" }) }), _jsx("p", { className: "text-title-medium font-medium text-on-surface mb-1", children: "No expenses found" }), _jsx("p", { className: "text-body-medium text-on-surface-variant text-center mb-4 max-w-md", children: "Create your first expense record to start tracking your spending or adjust your filters to see more results." })] })) : (_jsx("div", { className: "overflow-x-auto border border-outline/40 rounded-lg shadow-elevation-1", children: _jsxs("table", { className: "min-w-full divide-y divide-outline/40", children: [_jsx("thead", { className: "bg-surface-container", children: _jsxs("tr", { children: [_jsx("th", { scope: "col", className: "px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Date" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Description" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Category" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Amount" })] }) }), _jsx("tbody", { className: "bg-surface divide-y divide-outline/40", children: expenses.map((expense) => (_jsxs("tr", { className: "hover:bg-surface-variant/20", children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium text-on-surface", children: formatDate(expense.date) }), _jsxs("td", { className: "px-6 py-4 text-body-medium text-on-surface", children: [expense.description, expense.notes && (_jsx("p", { className: "text-body-small text-on-surface-variant mt-1", children: expense.notes }))] }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: expense.category_id ? (_jsx("span", { className: "px-2 py-1 text-label-small font-medium bg-tertiary-container text-on-tertiary-container rounded-full shadow-elevation-1", children: expense.category_name })) : (_jsxs("select", { className: "text-label-small rounded border border-outline/50 px-2 py-1 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", onChange: (e) => handleCategorize(expense.expense_id, e.target.value), value: "", children: [_jsx("option", { value: "", disabled: true, children: "Categorize" }), Object.values(categories).map((category) => (_jsx("option", { value: category.category_id, children: category.name }, category.category_id)))] })) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium text-right font-medium text-on-surface", children: formatAmount(expense.amount) })] }, expense.expense_id))) })] }) }))] }));
};

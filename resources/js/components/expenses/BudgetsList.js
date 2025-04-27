import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
import { BudgetForm } from './BudgetForm';
import { Button } from '../../ui';
export const BudgetsList = ({ refreshTrigger = 0 }) => {
    const [budgets, setBudgets] = useState([]);
    const [categories, setCategories] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [editingBudget, setEditingBudget] = useState(null);
    const [showAddForm, setShowAddForm] = useState(false);
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
        fetchBudgets();
    }, [refreshTrigger, categories]);
    const fetchBudgets = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await axios.get('/api/budgets');
            if (response.data && Array.isArray(response.data.data)) {
                // Add category names to budgets
                const budgetsWithCategories = response.data.data.map((budget) => {
                    var _a;
                    return (Object.assign(Object.assign({}, budget), { category_name: budget.category_id ? (_a = categories[budget.category_id]) === null || _a === void 0 ? void 0 : _a.name : 'Overall Budget' }));
                });
                setBudgets(budgetsWithCategories);
            }
            else {
                setBudgets([]);
            }
        }
        catch (err) {
            setError('Failed to load budgets');
            console.error('Failed to fetch budgets', err);
        }
        finally {
            setLoading(false);
        }
    };
    const handleFormSuccess = () => {
        setEditingBudget(null);
        setShowAddForm(false);
        fetchBudgets();
    };
    const formatAmount = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };
    if (editingBudget) {
        return (_jsx(BudgetForm, { initialData: {
                budget_id: editingBudget.budget_id,
                category_id: editingBudget.category_id,
                amount: editingBudget.budget_amount,
                start_date: editingBudget.start_date,
                end_date: editingBudget.end_date,
                notes: editingBudget.notes,
            }, onSuccess: handleFormSuccess }));
    }
    if (showAddForm) {
        return _jsx(BudgetForm, { onSuccess: handleFormSuccess });
    }
    return (_jsxs("div", { children: [_jsxs("div", { className: "flex justify-between items-center mb-4", children: [_jsx("h2", { className: "text-title-medium font-medium text-on-surface", children: "Your Budgets" }), _jsx(Button, { onClick: () => setShowAddForm(true), variant: "filled", icon: _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z", clipRule: "evenodd" }) }), children: "Add New Budget" })] }), loading ? (_jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-8 bg-surface-variant/40 rounded w-full" }), _jsx("div", { className: "h-8 bg-surface-variant/40 rounded w-full" }), _jsx("div", { className: "h-8 bg-surface-variant/40 rounded w-full" })] })) : error ? (_jsxs("div", { className: "p-8 text-center text-error bg-error-container/50 rounded-lg border border-error/30", children: [error, _jsx("button", { onClick: fetchBudgets, className: "block mx-auto mt-2 text-body-small text-primary hover:text-primary/80", children: "Try Again" })] })) : budgets.length === 0 ? (_jsxs("div", { className: "flex flex-col items-center justify-center p-10 bg-surface-variant/20 rounded-lg border border-outline/20", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-16 w-16 text-on-surface-variant/30 mb-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" }) }), _jsx("p", { className: "text-title-medium font-medium text-on-surface mb-1", children: "No budgets found" }), _jsx("p", { className: "text-body-medium text-on-surface-variant text-center mb-4 max-w-md", children: "Start by adding a new budget to track your spending limits." }), _jsx(Button, { onClick: () => setShowAddForm(true), variant: "filled", icon: _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z", clipRule: "evenodd" }) }), children: "Add Your First Budget" })] })) : (_jsx("div", { className: "bg-surface rounded-lg shadow-elevation-1 border border-outline/10 overflow-hidden", children: _jsx("div", { className: "overflow-x-auto", children: _jsxs("table", { className: "min-w-full divide-y divide-outline/20", children: [_jsx("thead", { className: "bg-surface-variant/20", children: _jsxs("tr", { children: [_jsx("th", { scope: "col", className: "px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Category" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Period" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Budget" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Spent" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Remaining" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-center text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Status" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider", children: "Actions" })] }) }), _jsx("tbody", { className: "bg-surface divide-y divide-outline/20", children: budgets.map((budget) => (_jsxs("tr", { className: "hover:bg-surface-variant/10", children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium font-medium text-on-surface", children: budget.category_name }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium text-on-surface-variant", children: [formatDate(budget.start_date), " - ", formatDate(budget.end_date)] }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium text-right text-on-surface", children: formatAmount(budget.budget_amount) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium text-right text-on-surface", children: formatAmount(budget.current_spending) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-body-medium text-right text-on-surface", children: formatAmount(budget.remaining) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-center", children: _jsx("span", { className: `px-2 py-1 text-label-small leading-5 font-medium rounded-full ${budget.status === 'active'
                                                    ? 'bg-tertiary-container text-on-tertiary-container'
                                                    : 'bg-error-container text-on-error-container'}`, children: budget.status === 'active' ? 'Active' : 'Exceeded' }) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-right text-body-medium font-medium", children: _jsx(Button, { onClick: () => setEditingBudget(budget), variant: "text", size: "sm", icon: _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { d: "M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" }) }), children: "Edit" }) })] }, budget.budget_id))) })] }) }) }))] }));
};

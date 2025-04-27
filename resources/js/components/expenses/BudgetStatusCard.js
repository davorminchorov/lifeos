import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { Button } from '../../ui';
export const BudgetStatusCard = () => {
    const [budgets, setBudgets] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    useEffect(() => {
        const fetchBudgets = async () => {
            setLoading(true);
            setError('');
            try {
                const response = await axios.get('/api/budgets');
                // Add null check and ensure we have an array
                if (response.data && Array.isArray(response.data.data)) {
                    // Sort by budget with lowest percentage remaining first
                    const sortedBudgets = [...response.data.data].sort((a, b) => {
                        const aRemaining = (a.budget_amount - a.current_spending) / a.budget_amount;
                        const bRemaining = (b.budget_amount - b.current_spending) / b.budget_amount;
                        return aRemaining - bRemaining;
                    }).slice(0, 3); // Only show top 3
                    setBudgets(sortedBudgets);
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
        fetchBudgets();
    }, []);
    const formatAmount = (amount) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    };
    if (loading) {
        return (_jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-20 bg-surface-variant/40 rounded w-full" }), _jsx("div", { className: "h-20 bg-surface-variant/40 rounded w-full" }), _jsx("div", { className: "h-20 bg-surface-variant/40 rounded w-full" })] }));
    }
    if (error) {
        return (_jsx("div", { className: "text-error bg-error-container/60 p-4 rounded-lg border border-error/50 shadow-elevation-1", children: error }));
    }
    if (budgets.length === 0) {
        return (_jsxs("div", { className: "text-on-surface-variant p-4 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1", children: ["No budgets found. ", _jsx(Link, { to: "/budgets", className: "text-primary hover:text-primary/80", children: "Create a budget" }), "."] }));
    }
    return (_jsxs("div", { className: "space-y-4", children: [budgets.map((budget) => (_jsxs("div", { className: "bg-surface-container rounded-lg p-4 border border-outline/40 shadow-elevation-1", children: [_jsxs("div", { className: "flex justify-between items-center mb-2", children: [_jsx("h3", { className: "font-medium text-title-medium text-on-surface", children: budget.category_name || 'Overall Budget' }), _jsx("span", { className: `px-2 py-1 rounded-full text-label-small font-medium shadow-elevation-1 ${budget.status === 'active'
                                    ? 'bg-tertiary-container text-on-tertiary-container'
                                    : 'bg-error-container text-on-error-container'}`, children: budget.status === 'active' ? 'Active' : 'Exceeded' })] }), _jsxs("div", { className: "flex justify-between text-body-small text-on-surface-variant mb-2", children: [_jsxs("span", { children: ["Ends ", formatDate(budget.end_date)] }), _jsxs("span", { children: [formatAmount(budget.current_spending), " / ", formatAmount(budget.budget_amount)] })] }), _jsx("div", { className: "w-full bg-surface-variant rounded-full h-2.5 shadow-elevation-1", children: _jsx("div", { className: `h-2.5 rounded-full ${budget.percentage_used > 90 ? 'bg-error' :
                                budget.percentage_used > 70 ? 'bg-tertiary' :
                                    'bg-primary'}`, style: { width: `${Math.min(100, budget.percentage_used)}%` } }) }), _jsx("div", { className: "mt-2 text-body-small", children: _jsxs("span", { className: budget.remaining < 0 ? 'text-error' : 'text-on-surface', children: [budget.remaining < 0 ? 'Over by ' : 'Remaining: ', formatAmount(Math.abs(budget.remaining))] }) })] }, budget.budget_id))), _jsx("div", { className: "text-center mt-6", children: _jsx(Link, { to: "/budgets", children: _jsx(Button, { variant: "text", size: "sm", children: "View all budgets" }) }) })] }));
};

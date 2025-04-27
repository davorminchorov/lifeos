import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Link } from 'react-router-dom';
import ExpenseForm from '../components/expenses/ExpenseForm';
import { ExpensesList } from '../components/expenses/ExpensesList';
import { MonthlySummaryCard } from '../components/expenses/MonthlySummaryCard';
import { BudgetStatusCard } from '../components/expenses/BudgetStatusCard';
import { Button } from '../ui';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/Card';
import { PageContainer, PageSection } from '../ui/PageContainer';
export const ExpensesPage = () => {
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    const [showAddForm, setShowAddForm] = useState(false);
    const handleExpenseAdded = () => {
        setRefreshTrigger(prev => prev + 1);
        setShowAddForm(false);
    };
    return (_jsxs(PageContainer, { title: "Expense Tracking", subtitle: "Track, categorize and analyze your expenses to manage your finances better.", actions: _jsx(Button, { onClick: () => setShowAddForm(!showAddForm), variant: "filled", icon: showAddForm
                ? _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z", clipRule: "evenodd" }) })
                : _jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z", clipRule: "evenodd" }) }), children: showAddForm ? 'Cancel' : 'Add New Expense' }), children: [_jsxs("div", { className: "flex space-x-4 mb-6", children: [_jsx(Link, { to: "/budgets", children: _jsx(Button, { variant: "text", children: "Manage Budgets" }) }), _jsx(Link, { to: "/categories", children: _jsx(Button, { variant: "text", children: "Manage Categories" }) })] }), showAddForm && (_jsx(PageSection, { title: "Add New Expense", children: _jsx(Card, { variant: "elevated", children: _jsx(CardContent, { children: _jsx(ExpenseForm, { onSuccess: handleExpenseAdded }) }) }) })), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-12 gap-6", children: [_jsx("div", { className: "md:col-span-8", children: _jsxs(Card, { variant: "elevated", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Expenses List" }) }), _jsx(CardContent, { children: _jsx(ExpensesList, { refreshTrigger: refreshTrigger }) })] }) }), _jsxs("div", { className: "md:col-span-4 space-y-6", children: [_jsxs(Card, { variant: "filled", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Monthly Summary" }) }), _jsx(CardContent, { children: _jsx(MonthlySummaryCard, {}) })] }), _jsxs(Card, { variant: "outlined", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Budget Status" }) }), _jsx(CardContent, { children: _jsx(BudgetStatusCard, {}) })] })] })] }), !showAddForm && (_jsxs("div", { className: "mt-8 text-center", children: [_jsx("p", { className: "text-on-surface-variant mb-2", children: "Want to see more detailed analytics?" }), _jsx("p", { className: "text-on-surface mb-4", children: "Visit the reports section for comprehensive spending analysis." }), _jsx(Link, { to: "/reports", children: _jsx(Button, { variant: "outlined", children: "View Reports" }) })] }))] }));
};

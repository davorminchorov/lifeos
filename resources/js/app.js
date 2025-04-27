var _a;
import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// App.js
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import axios from 'axios';
import Dashboard from './pages/Dashboard';
import DashboardPage from './pages/DashboardPage';
import NotFound from './pages/NotFound';
// Import auth components
import { AuthProvider } from './store/authContext';
import { ProtectedRoute } from './components/auth/ProtectedRoute';
import { LoginPage } from './auth/routes/LoginPage';
import { ForgotPasswordPage } from './auth/routes/ForgotPasswordPage';
import { ResetPasswordPage } from './auth/routes/ResetPasswordPage';
import { AuthLayout } from './auth/components/AuthLayout';
// Import layouts and theme
import { ThemeProvider } from './ui/ThemeProvider';
import { ToastProvider } from './ui/Toast';
import UnifiedLayout from './components/layouts/UnifiedLayout';
import { QueryProvider } from './providers/QueryProvider';
// Subscription Pages
import SubscriptionsList from './pages/subscriptions/SubscriptionsList';
import SubscriptionDetail from './pages/subscriptions/SubscriptionDetail';
import CreateSubscription from './pages/subscriptions/CreateSubscription';
import EditSubscription from './pages/subscriptions/EditSubscription';
// Payment Pages
import RecordPayment from './pages/payments/RecordPayment';
import PaymentHistoryPage from './pages/payments/PaymentHistoryPage';
// Report Pages
import PaymentReports from './pages/reports/PaymentReports';
// Expenses Pages
import { ExpensesPage } from './pages/ExpensesPage';
import { BudgetsPage } from './pages/BudgetsPage';
import { CategoriesPage } from './pages/CategoriesPage';
import ExpenseDetail from './pages/expenses/ExpenseDetail';
import CreateExpense from './pages/expenses/CreateExpense';
import EditExpense from './pages/expenses/EditExpense';
// Utility Bills Pages
import UtilityBillsPage from './pages/utility-bills/UtilityBillsPage';
import UtilityBillDetailPage from './pages/utility-bills/UtilityBillDetailPage';
import CreateUtilityBill from './pages/utility-bills/CreateUtilityBill';
import EditUtilityBill from './pages/utility-bills/EditUtilityBill';
// Job Applications Pages
import JobApplicationsPage from './pages/job-applications/JobApplicationsPage';
import JobApplicationDetailPage from './pages/job-applications/JobApplicationDetailPage';
import CreateJobApplication from './pages/job-applications/CreateJobApplication';
import EditJobApplication from './pages/job-applications/EditJobApplication';
// Investments Pages
import InvestmentsPage from './pages/investments/InvestmentsPage';
import InvestmentDetailPage from './pages/investments/InvestmentDetailPage';
import InvestmentCreatePage from './pages/investments/InvestmentCreatePage';
import InvestmentEditPage from './pages/investments/InvestmentEditPage';
import TransactionsPage from './pages/investments/TransactionsPage';
console.log('LifeOS app initialized with React');
// Setup CSRF token for all requests
const token = (_a = document.querySelector('meta[name="csrf-token"]')) === null || _a === void 0 ? void 0 : _a.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
// Set up axios defaults
axios.defaults.baseURL = '/';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
/**
 * Root App Component
 */
const App = () => {
    return (_jsx(BrowserRouter, { children: _jsx(QueryProvider, { children: _jsx(ThemeProvider, { children: _jsx(ToastProvider, { children: _jsx(AuthProvider, { children: _jsxs(Routes, { children: [_jsx(Route, { path: "/login", element: _jsx(Navigate, { to: "/auth/login", replace: true }) }), _jsxs(Route, { path: "/auth", element: _jsx(AuthLayout, {}), children: [_jsx(Route, { index: true, element: _jsx(Navigate, { to: "/auth/login", replace: true }) }), _jsx(Route, { path: "login", element: _jsx(LoginPage, {}) }), _jsx(Route, { path: "forgot-password", element: _jsx(ForgotPasswordPage, {}) }), _jsx(Route, { path: "reset-password", element: _jsx(ResetPasswordPage, {}) })] }), _jsx(Route, { element: _jsx(ProtectedRoute, {}), children: _jsxs(Route, { path: "/", element: _jsx(UnifiedLayout, {}), children: [_jsx(Route, { index: true, element: _jsx(Navigate, { to: "/dashboard", replace: true }) }), _jsxs(Route, { path: "dashboard", children: [_jsx(Route, { index: true, element: _jsx(Dashboard, {}) }), _jsx(Route, { path: "financial", element: _jsx(DashboardPage, {}) }), _jsx(Route, { path: "customize", element: _jsx(Dashboard, {}) }), _jsx(Route, { path: "settings", element: _jsx(Dashboard, {}) })] }), _jsxs(Route, { path: "subscriptions", children: [_jsx(Route, { index: true, element: _jsx(SubscriptionsList, {}) }), _jsx(Route, { path: "create", element: _jsx(CreateSubscription, {}) }), _jsx(Route, { path: ":id", element: _jsx(SubscriptionDetail, {}) }), _jsx(Route, { path: ":id/edit", element: _jsx(EditSubscription, {}) })] }), _jsxs(Route, { path: "payments", children: [_jsx(Route, { index: true, element: _jsx(PaymentHistoryPage, {}) }), _jsx(Route, { path: "record/:subscriptionId", element: _jsx(RecordPayment, {}) }), _jsx(Route, { path: "history", element: _jsx(PaymentHistoryPage, {}) })] }), _jsx(Route, { path: "reports", children: _jsx(Route, { path: "payments", element: _jsx(PaymentReports, {}) }) }), _jsxs(Route, { path: "expenses", children: [_jsx(Route, { index: true, element: _jsx(ExpensesPage, {}) }), _jsx(Route, { path: "create", element: _jsx(CreateExpense, {}) }), _jsx(Route, { path: ":id", element: _jsx(ExpenseDetail, {}) }), _jsx(Route, { path: ":id/edit", element: _jsx(EditExpense, {}) })] }), _jsx(Route, { path: "budgets", element: _jsx(BudgetsPage, {}) }), _jsx(Route, { path: "categories", element: _jsx(CategoriesPage, {}) }), _jsxs(Route, { path: "utility-bills", children: [_jsx(Route, { index: true, element: _jsx(UtilityBillsPage, {}) }), _jsx(Route, { path: "create", element: _jsx(CreateUtilityBill, {}) }), _jsx(Route, { path: ":id", element: _jsx(UtilityBillDetailPage, {}) }), _jsx(Route, { path: ":id/edit", element: _jsx(EditUtilityBill, {}) })] }), _jsxs(Route, { path: "job-applications", children: [_jsx(Route, { index: true, element: _jsx(JobApplicationsPage, {}) }), _jsx(Route, { path: "create", element: _jsx(CreateJobApplication, {}) }), _jsx(Route, { path: ":id", element: _jsx(JobApplicationDetailPage, {}) }), _jsx(Route, { path: ":id/edit", element: _jsx(EditJobApplication, {}) })] }), _jsxs(Route, { path: "investments", children: [_jsx(Route, { index: true, element: _jsx(InvestmentsPage, {}) }), _jsx(Route, { path: "create", element: _jsx(InvestmentCreatePage, {}) }), _jsx(Route, { path: ":id", element: _jsx(InvestmentDetailPage, {}) }), _jsx(Route, { path: ":id/edit", element: _jsx(InvestmentEditPage, {}) }), _jsx(Route, { path: ":id/transactions", element: _jsx(TransactionsPage, {}) })] })] }) }), _jsx(Route, { path: "*", element: _jsx(NotFound, {}) })] }) }) }) }) }) }));
};
// Initialize the application
const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(_jsx(React.StrictMode, { children: _jsx(App, {}) }));
}
else {
    console.error('Root element not found');
}

// App.js
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import axios from 'axios';

// Import pages
import { Login } from './pages/auth/Login';
import Dashboard from './pages/Dashboard';
import DashboardPage from './pages/DashboardPage';
import NotFound from './pages/NotFound';

// Import auth components
import { AuthProvider } from './store/authContext';
import { ProtectedRoute } from './components/auth/ProtectedRoute';
import { LoginPage } from './auth/routes/LoginPage';
import { ForgotPasswordPage } from './auth/routes/ForgotPasswordPage';
import { ResetPasswordPage } from './auth/routes/ResetPasswordPage';

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
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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
const App: React.FC = () => {
    return (
        <BrowserRouter>
            <QueryProvider>
                <ThemeProvider>
                    <ToastProvider>
                        <AuthProvider>
                            <Routes>
                                {/* Auth Routes */}
                                <Route path="/login" element={<Navigate to="/auth/login" replace />} />
                                <Route path="/auth/login" element={<LoginPage />} />
                                <Route path="/auth/forgot-password" element={<ForgotPasswordPage />} />
                                <Route path="/auth/reset-password" element={<ResetPasswordPage />} />

                                {/* Protected Routes */}
                                <Route element={<ProtectedRoute />}>
                                    <Route path="/" element={<UnifiedLayout />}>
                                        <Route index element={<Navigate to="/dashboard" replace />} />

                                        {/* Dashboard Routes */}
                                        <Route path="dashboard">
                                            <Route index element={<Dashboard />} />
                                            <Route path="financial" element={<DashboardPage />} />
                                            <Route path="customize" element={<Dashboard />} />
                                            <Route path="settings" element={<Dashboard />} />
                                        </Route>

                                        {/* Subscription Routes */}
                                        <Route path="subscriptions">
                                            <Route index element={<SubscriptionsList />} />
                                            <Route path="create" element={<CreateSubscription />} />
                                            <Route path=":id" element={<SubscriptionDetail />} />
                                            <Route path=":id/edit" element={<EditSubscription />} />
                                        </Route>

                                        {/* Payment Routes */}
                                        <Route path="payments">
                                            <Route index element={<PaymentHistoryPage />} />
                                            <Route path="record/:subscriptionId" element={<RecordPayment />} />
                                            <Route path="history" element={<PaymentHistoryPage />} />
                                        </Route>

                                        {/* Report Routes */}
                                        <Route path="reports">
                                            <Route path="payments" element={<PaymentReports />} />
                                        </Route>

                                        {/* Expenses Routes */}
                                        <Route path="expenses">
                                            <Route index element={<ExpensesPage />} />
                                            <Route path="create" element={<CreateExpense />} />
                                            <Route path=":id" element={<ExpenseDetail />} />
                                            <Route path=":id/edit" element={<EditExpense />} />
                                        </Route>
                                        <Route path="budgets" element={<BudgetsPage />} />
                                        <Route path="categories" element={<CategoriesPage />} />

                                        {/* Utility Bills Routes */}
                                        <Route path="utility-bills">
                                            <Route index element={<UtilityBillsPage />} />
                                            <Route path="create" element={<CreateUtilityBill />} />
                                            <Route path=":id" element={<UtilityBillDetailPage />} />
                                            <Route path=":id/edit" element={<EditUtilityBill />} />
                                        </Route>

                                        {/* Job Applications Routes */}
                                        <Route path="job-applications">
                                            <Route index element={<JobApplicationsPage />} />
                                            <Route path="create" element={<CreateJobApplication />} />
                                            <Route path=":id" element={<JobApplicationDetailPage />} />
                                            <Route path=":id/edit" element={<EditJobApplication />} />
                                        </Route>

                                        {/* Investments Routes */}
                                        <Route path="investments">
                                            <Route index element={<InvestmentsPage />} />
                                            <Route path="create" element={<InvestmentCreatePage />} />
                                            <Route path=":id" element={<InvestmentDetailPage />} />
                                            <Route path=":id/edit" element={<InvestmentEditPage />} />
                                            <Route path=":id/transactions" element={<TransactionsPage />} />
                                        </Route>
                                    </Route>
                                </Route>

                                {/* 404 page */}
                                <Route path="*" element={<NotFound />} />
                            </Routes>
                        </AuthProvider>
                    </ToastProvider>
                </ThemeProvider>
            </QueryProvider>
        </BrowserRouter>
    );
};

// Initialize the application
const container = document.getElementById('app');

if (container) {
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
} else {
    console.error('Root element not found');
}

// App.js
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import axios from 'axios';

// Import pages
import { Login } from './pages/auth/Login';
import Dashboard from './pages/Dashboard';
import NotFound from './pages/NotFound';

// Import auth components
import { AuthProvider } from './store/authContext';
import { ProtectedRoute } from './components/auth/ProtectedRoute';

// Components
import AppLayout from './components/layouts/AppLayout';

// Subscription Pages
import SubscriptionsList from './pages/subscriptions/SubscriptionsList';
import SubscriptionDetail from './pages/subscriptions/SubscriptionDetail';
import CreateSubscription from './pages/subscriptions/CreateSubscription';
import EditSubscription from './pages/subscriptions/EditSubscription';

// Payment Pages
import RecordPayment from './pages/payments/RecordPayment';
import PaymentsList from './pages/payments/PaymentsList';

// Report Pages
import PaymentReports from './pages/reports/PaymentReports';

// Expenses Pages
import { ExpensesPage } from './pages/ExpensesPage';
import { BudgetsPage } from './pages/BudgetsPage';
import { CategoriesPage } from './pages/CategoriesPage';

// Utility Bills Pages
import UtilityBillsPage from './pages/utility-bills/UtilityBillsPage';
import UtilityBillDetailPage from './pages/utility-bills/UtilityBillDetailPage';

// Job Applications Pages
import JobApplicationsPage from './pages/job-applications/JobApplicationsPage';
import JobApplicationDetailPage from './pages/job-applications/JobApplicationDetailPage';

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
            <AuthProvider>
                <Routes>
                    {/* Auth Routes */}
                    <Route path="/login" element={<Login />} />

                    {/* Protected Routes */}
                    <Route path="/" element={<AppLayout />}>
                        <Route index element={<Navigate to="/dashboard" replace />} />
                        <Route path="dashboard" element={<Dashboard />} />

                        {/* Subscription Routes */}
                        <Route path="subscriptions">
                            <Route index element={<SubscriptionsList />} />
                            <Route path="create" element={<CreateSubscription />} />
                            <Route path=":id" element={<SubscriptionDetail />} />
                            <Route path=":id/edit" element={<EditSubscription />} />
                        </Route>

                        {/* Payment Routes */}
                        <Route path="payments">
                            <Route index element={<PaymentsList />} />
                            <Route path="record/:subscriptionId" element={<RecordPayment />} />
                        </Route>

                        {/* Report Routes */}
                        <Route path="reports">
                            <Route path="payments" element={<PaymentReports />} />
                        </Route>

                        {/* Expenses Routes */}
                        <Route path="expenses" element={<ExpensesPage />} />
                        <Route path="budgets" element={<BudgetsPage />} />
                        <Route path="categories" element={<CategoriesPage />} />

                        {/* Utility Bills Routes */}
                        <Route path="utility-bills">
                            <Route index element={<UtilityBillsPage />} />
                            <Route path=":id" element={<UtilityBillDetailPage />} />
                        </Route>

                        {/* Job Applications Routes */}
                        <Route path="job-applications">
                            <Route index element={<JobApplicationsPage />} />
                            <Route path=":id" element={<JobApplicationDetailPage />} />
                        </Route>
                    </Route>

                    {/* 404 page */}
                    <Route path="*" element={<NotFound />} />
                </Routes>
            </AuthProvider>
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

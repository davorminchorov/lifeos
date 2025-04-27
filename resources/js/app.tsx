// App.js
import '../css/app.css';
import * as React from 'react';
import * as ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import axios from 'axios';

// Import pages
import { Login } from './pages/auth/Login';
import Dashboard from './dashboard/routes/Dashboard';
import NotFound from './pages/NotFound';

// Import auth components
import { AuthProvider } from './store/authContext';
import { ProtectedRoute } from './components/auth/ProtectedRoute';

console.log('LifeOS app initialized with React');

// Setup CSRF token for all requests
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}

/**
 * Root App Component
 */
function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    {/* Public Routes */}
                    <Route path="/login" element={<Login />} />

                    {/* Protected Routes */}
                    <Route element={<ProtectedRoute />}>
                        <Route path="/dashboard" element={<Dashboard />} />
                        {/* Add other protected routes here */}
                    </Route>

                    {/* Redirect root to login for now */}
                    <Route path="/" element={<Navigate to="/login" replace />} />

                    {/* 404 page */}
                    <Route path="*" element={<NotFound />} />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}

// Initialize the application
const container = document.getElementById('app');

if (container) {
    const root = ReactDOM.createRoot(container);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
} else {
    console.error('Root element not found');
}

// App.js
import '../css/app.css';
import * as React from 'react';
import * as ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import axios from 'axios';

// Import pages
import Login from './pages/auth/Login';
import NotFound from './pages/NotFound';

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
            <Routes>
                {/* Auth Routes */}
                <Route path="/login" element={<Login />} />

                {/* Temporarily using a placeholder dashboard redirect */}
                <Route
                  path="/dashboard"
                  element={
                    <div className="container">
                      <div className="w-full max-w-4xl">
                        <div className="w-full h-12 bg-teal-600 flex items-center pl-4 text-white text-lg font-bold">
                          LifeOS
                        </div>
                        <div className="login-box">
                          <h1 className="text-slate-800">Dashboard</h1>
                          <p className="text-slate-600 mt-2">
                            You have successfully logged in. The dashboard will be implemented in the next phase.
                          </p>
                          <button
                            onClick={() => {
                              axios.post("/api/logout").then(() => {
                                window.location.href = "/login";
                              });
                            }}
                            className="btn btn-primary mt-4"
                          >
                            Logout
                          </button>
                        </div>
                      </div>
                    </div>
                  }
                />

                {/* Redirect root to login for now */}
                <Route path="/" element={<Navigate to="/login" replace />} />

                {/* 404 page */}
                <Route path="*" element={<NotFound />} />
            </Routes>
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

// App.js
import '../css/app.css';
import * as React from 'react';
import * as ReactDOM from 'react-dom/client';
// @ts-ignore - React Router type issues with React 19
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import axios from 'axios';
// @ts-ignore - Type issues with ShadCN components
import { Button } from './components/ui/button';

console.log('LifeOS app initialized with React 19');

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
        <div className="flex min-h-screen flex-col justify-center items-center p-4" style={{ backgroundColor: 'hsl(210, 20%, 98%)' }}>
            <div className="bg-white p-8 shadow-md max-w-md w-full" style={{ borderRadius: '0.5rem', color: 'hsl(221, 39%, 11%)' }}>
                <h1 className="text-2xl font-bold text-center mb-4" style={{ color: 'hsl(221, 83%, 40%)' }}>LifeOS</h1>
                <p className="text-center mb-6" style={{ color: 'hsl(215, 16%, 47%)' }}>
                    Welcome to LifeOS - built with React 19 and Tailwind CSS 4
                </p>
                <div className="mt-4">
                    <Button className="w-full">Login</Button>
                </div>
            </div>
        </div>
    );
}

// Initialize the application
const container = document.getElementById('app');

if (container) {
    // @ts-ignore - React 19 type issues
    const root = ReactDOM.createRoot(container);
    root.render(
        // @ts-ignore - React 19 type issues
        <React.StrictMode>
            {/* @ts-ignore - React Router type issues with React 19 */}
            <BrowserRouter>
                {/* @ts-ignore - React Router type issues with React 19 */}
                <Routes>
                    {/* @ts-ignore - React Router type issues with React 19 */}
                    <Route path="*" element={<App />} />
                </Routes>
            </BrowserRouter>
        </React.StrictMode>
    );
} else {
    console.error('Root element not found');
}

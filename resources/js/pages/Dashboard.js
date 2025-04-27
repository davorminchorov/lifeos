import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import PageContainer, { PageSection, PageGrid } from '../ui/PageContainer';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../ui/Card';
import { Button } from '../ui/Button';
import { Tabs, TabsList, TabsTrigger } from '../ui/Tabs';
import { BarChart, Settings, PieChart } from 'lucide-react';
const Dashboard = () => {
    const navigate = useNavigate();
    const [summary, setSummary] = useState({
        totalSubscriptions: 0,
        activeSubscriptions: 0,
        upcomingPayments: 0,
        monthlyCost: 0,
        pendingBills: 0,
        upcomingReminders: 0
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    useEffect(() => {
        const fetchDashboardData = async () => {
            try {
                setLoading(true);
                const response = await axios.get('/api/dashboard/summary');
                setSummary(response.data);
                setError('');
            }
            catch (err) {
                console.error('Failed to fetch dashboard data', err);
                setError('Failed to load dashboard data. Please try again later.');
            }
            finally {
                setLoading(false);
            }
        };
        fetchDashboardData();
    }, []);
    if (loading) {
        return (_jsx(PageContainer, { title: "Dashboard", children: _jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-4 bg-surface-variant rounded w-3/4" }), _jsxs("div", { className: "space-y-2", children: [_jsx("div", { className: "h-4 bg-surface-variant rounded" }), _jsx("div", { className: "h-4 bg-surface-variant rounded w-5/6" })] })] }) }));
    }
    return (_jsxs(PageContainer, { title: "Dashboard", subtitle: "Welcome to your personal finance dashboard", children: [error && (_jsx("div", { className: "mb-6 p-4 rounded-md bg-error-container text-on-error-container", role: "alert", children: error })), _jsx("div", { className: "mb-6", children: _jsx(Tabs, { defaultValue: "overview", className: "w-full", children: _jsxs(TabsList, { className: "mb-4", children: [_jsx(TabsTrigger, { value: "overview", onClick: () => navigate('/dashboard'), children: "Overview" }), _jsxs(TabsTrigger, { value: "financial", onClick: () => navigate('/dashboard/financial'), children: [_jsx(BarChart, { className: "h-4 w-4 mr-2" }), "Financial"] }), _jsxs(TabsTrigger, { value: "customize", onClick: () => navigate('/dashboard/customize'), children: [_jsx(PieChart, { className: "h-4 w-4 mr-2" }), "Customize"] }), _jsxs(TabsTrigger, { value: "settings", onClick: () => navigate('/dashboard/settings'), children: [_jsx(Settings, { className: "h-4 w-4 mr-2" }), "Settings"] })] }) }) }), _jsx(PageSection, { title: "Overview", children: _jsxs(PageGrid, { columns: 4, children: [_jsxs(Card, { children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Total Subscriptions" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: "w-12 h-12 flex items-center justify-center rounded-full bg-primary-container text-on-primary-container shadow-elevation-1", children: _jsx("span", { className: "font-bold text-lg", children: summary.totalSubscriptions || '0' }) }), _jsx("div", { className: "ml-4", children: _jsx("span", { className: "text-2xl font-medium", children: summary.totalSubscriptions || '0' }) })] }) }), _jsx(CardFooter, { className: "justify-end", children: _jsx(Button, { variant: "text", size: "sm", asChild: true, children: _jsx(Link, { to: "/subscriptions", children: "View all" }) }) })] }), _jsxs(Card, { children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Active Subscriptions" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: "w-12 h-12 flex items-center justify-center rounded-full bg-tertiary-container text-on-tertiary-container shadow-elevation-1", children: _jsx("span", { className: "font-bold text-lg", children: summary.activeSubscriptions || '0' }) }), _jsx("div", { className: "ml-4", children: _jsx("span", { className: "text-2xl font-medium", children: summary.activeSubscriptions || '0' }) })] }) }), _jsx(CardFooter, { className: "justify-end", children: _jsx(Button, { variant: "text", size: "sm", asChild: true, children: _jsx(Link, { to: "/subscriptions", children: "View active" }) }) })] }), _jsxs(Card, { children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Pending Bills" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: "w-12 h-12 flex items-center justify-center rounded-full bg-secondary-container text-on-secondary-container shadow-elevation-1", children: _jsx("span", { className: "font-bold text-lg", children: summary.pendingBills || '0' }) }), _jsx("div", { className: "ml-4", children: _jsx("span", { className: "text-2xl font-medium", children: summary.pendingBills || '0' }) })] }) }), _jsx(CardFooter, { className: "justify-end", children: _jsx(Button, { variant: "text", size: "sm", asChild: true, children: _jsx(Link, { to: "/utility-bills", children: "View bills" }) }) })] }), _jsxs(Card, { children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Monthly Cost" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: "w-12 h-12 flex items-center justify-center rounded-full bg-error-container text-on-error-container shadow-elevation-1", children: _jsx("span", { className: "font-bold text-sm", children: "$" }) }), _jsx("div", { className: "ml-4", children: _jsxs("span", { className: "text-2xl font-medium", children: ["$", (summary.monthlyCost || 0).toFixed(2)] }) })] }) }), _jsx(CardFooter, { className: "justify-end", children: _jsx(Button, { variant: "text", size: "sm", asChild: true, children: _jsx(Link, { to: "/subscriptions", children: "View breakdown" }) }) })] })] }) }), _jsx(PageSection, { title: "Quick Actions", children: _jsxs(PageGrid, { columns: 3, children: [_jsxs(Card, { children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Manage Subscriptions" }), _jsx(CardDescription, { children: "Keep track of your recurring subscriptions and payments." })] }), _jsx(CardFooter, { children: _jsx(Button, { variant: "tonal", asChild: true, children: _jsx(Link, { to: "/subscriptions", children: "Go to Subscriptions" }) }) })] }), _jsxs(Card, { children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Utility Bills" }), _jsx(CardDescription, { children: "Manage your utility bills and set up payment reminders." })] }), _jsx(CardFooter, { children: _jsx(Button, { variant: "tonal", asChild: true, children: _jsx(Link, { to: "/utility-bills", children: "Go to Utility Bills" }) }) })] }), _jsxs(Card, { children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Track Expenses" }), _jsx(CardDescription, { children: "Record and categorize your daily expenses." })] }), _jsx(CardFooter, { children: _jsx(Button, { variant: "tonal", asChild: true, children: _jsx(Link, { to: "/expenses", children: "Go to Expenses" }) }) })] }), _jsxs(Card, { children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Investments" }), _jsx(CardDescription, { children: "Monitor your investment portfolio and track performance." })] }), _jsx(CardFooter, { children: _jsx(Button, { variant: "tonal", asChild: true, children: _jsx(Link, { to: "/investments", children: "Go to Investments" }) }) })] }), _jsxs(Card, { children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Job Applications" }), _jsx(CardDescription, { children: "Track your job applications and interview processes." })] }), _jsx(CardFooter, { children: _jsx(Button, { variant: "tonal", asChild: true, children: _jsx(Link, { to: "/job-applications", children: "Go to Job Applications" }) }) })] })] }) })] }));
};
export default Dashboard;

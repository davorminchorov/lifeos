import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../store/authContext';
import ThemeToggle from '../../ui/ThemeToggle';
import { useTheme } from '../../ui/ThemeProvider';
export function LoginForm({ onSuccess }) {
    // Use the actual auth context instead of simulating login
    const { login, error: authError, isLoading: authLoading } = useAuth();
    const [isLoading, setIsLoading] = useState(false);
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [remember, setRemember] = useState(false);
    const [error, setError] = useState('');
    const { isDark } = useTheme();
    // Update local error state when auth error changes
    useEffect(() => {
        if (authError) {
            setError(authError);
        }
    }, [authError]);
    // Update local loading state when auth loading changes
    useEffect(() => {
        setIsLoading(authLoading);
    }, [authLoading]);
    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError('');
        // Validate email
        if (!email) {
            setError('Email is required');
            setIsLoading(false);
            return;
        }
        // Validate password
        if (!password) {
            setError('Password is required');
            setIsLoading(false);
            return;
        }
        try {
            // Use the login function from the auth context
            await login(email, password, remember);
            if (onSuccess) {
                onSuccess();
            }
        }
        catch (err) {
            // Error handling is now done via the useEffect that watches authError
        }
    };
    // Custom input style classes based on theme
    const containerClass = isDark ? "bg-gray-900 text-white" : "bg-surface";
    const inputClass = isDark
        ? "h-14 bg-blue-50/10 border border-transparent rounded-md text-white"
        : "h-14 bg-blue-50 border border-transparent rounded-md";
    const labelClass = isDark ? "text-gray-300" : "text-on-surface-variant";
    const buttonClass = isDark
        ? "bg-teal-500 hover:bg-teal-600 text-white"
        : "bg-primary hover:bg-primary-dark text-on-primary";
    return (_jsxs("div", { className: `w-full max-w-md p-6 ${containerClass} rounded-xl shadow-elevation-2`, children: [_jsxs("div", { className: "flex justify-between items-center mb-6", children: [_jsx("h2", { className: "text-title-large font-bold", children: "Sign in" }), _jsx(ThemeToggle, {})] }), error && (_jsx("div", { className: "mb-6 p-3 rounded-md bg-error-container text-on-error-container text-sm", children: error })), _jsxs("form", { onSubmit: handleSubmit, className: "space-y-6", children: [_jsxs("div", { className: "space-y-1", children: [_jsx("label", { htmlFor: "email", className: `block text-sm font-medium ${labelClass}`, children: "Email" }), _jsx("input", { id: "email", type: "email", value: email, onChange: (e) => setEmail(e.target.value), className: `${inputClass} w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary`, placeholder: "example@example.com" })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("label", { htmlFor: "password", className: `block text-sm font-medium ${labelClass}`, children: "Password" }), _jsxs("div", { className: "relative", children: [_jsx("input", { id: "password", type: showPassword ? "text" : "password", value: password, onChange: (e) => setPassword(e.target.value), className: `${inputClass} w-full px-4 py-3 pr-10 focus:outline-none focus:ring-2 focus:ring-primary`, placeholder: "\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022" }), _jsx("button", { type: "button", className: "absolute right-3 top-1/2 -translate-y-1/2 text-gray-400", onClick: () => setShowPassword(!showPassword), children: showPassword ? (_jsx("svg", { width: "20", height: "20", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg", children: _jsx("path", { d: "M12 4.5C7 4.5 2.73 7.61 1 12C2.73 16.39 7 19.5 12 19.5C17 19.5 21.27 16.39 23 12C21.27 7.61 17 4.5 12 4.5ZM12 17C9.24 17 7 14.76 7 12C7 9.24 9.24 7 12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17ZM12 9C10.34 9 9 10.34 9 12C9 13.66 10.34 15 12 15C13.66 15 15 13.66 15 12C15 10.34 13.66 9 12 9Z", fill: "currentColor" }) })) : (_jsx("svg", { width: "20", height: "20", viewBox: "0 0 24 24", fill: "none", xmlns: "http://www.w3.org/2000/svg", children: _jsx("path", { d: "M12 6.5C15.79 6.5 19.17 8.63 20.82 12C20.23 13.27 19.4 14.36 18.41 15.21L20.54 17.34C21.86 16.02 22.93 14.36 23.64 12.45C23.75 12.17 23.75 11.84 23.64 11.55C21.86 7.32 17.14 4.5 12 4.5C10.44 4.5 8.96 4.79 7.59 5.33L9.45 7.19C10.22 6.77 11.08 6.5 12 6.5ZM2.71 3.16L4.37 4.82L4.96 5.41C3.44 6.8 2.2 8.62 1.36 10.55C1.25 10.83 1.25 11.16 1.36 11.45C3.14 15.68 7.86 18.5 13 18.5C14.81 18.5 16.56 18.12 18.13 17.47L18.19 17.53L20.31 19.65L21.66 18.3L4.06 0.700001L2.71 2.05L2.71 3.16ZM12 16.5C8.21 16.5 4.83 14.37 3.18 11C3.88 9.52 4.92 8.25 6.21 7.32L8.57 9.68C8.4 10.1 8.3 10.54 8.3 11C8.3 13.16 10.05 14.9 12.2 14.9C12.67 14.9 13.11 14.8 13.53 14.63L15.89 16.99C14.69 17.27 13.36 17.5 12 17.5V16.5ZM15.24 12.89L11.3 8.95C12.94 8.78 14.41 10.2 14.24 11.89L15.24 12.89Z", fill: "currentColor" }) })) })] })] }), _jsxs("div", { className: "flex items-center justify-between", children: [_jsxs("div", { className: "flex items-center", children: [_jsx("input", { id: "remember-me", name: "remember-me", type: "checkbox", className: "h-4 w-4 text-primary focus:ring-primary-container border-outline rounded", checked: remember, onChange: (e) => setRemember(e.target.checked) }), _jsx("label", { htmlFor: "remember-me", className: `ml-2 block text-sm ${isDark ? 'text-gray-300' : 'text-on-surface'}`, children: "Remember me" })] }), _jsx("div", { className: "text-sm", children: _jsx(Link, { to: "/auth/forgot-password", className: `font-medium ${isDark ? 'text-teal-400 hover:text-teal-300' : 'text-primary hover:text-primary-dark'}`, children: "Forgot your password?" }) })] }), _jsxs("button", { type: "submit", className: `w-full flex justify-center items-center h-12 px-4 border border-transparent rounded-md shadow-sm text-body-medium font-medium ${buttonClass} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition-colors`, disabled: isLoading, children: [isLoading ? (_jsxs("svg", { className: "animate-spin -ml-1 mr-3 h-5 w-5 text-white", xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 24 24", children: [_jsx("circle", { className: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", strokeWidth: "4" }), _jsx("path", { className: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })] })) : null, "Sign in"] })] })] }));
}

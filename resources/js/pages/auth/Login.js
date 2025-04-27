import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { LoginForm } from '../../components/auth/LoginForm';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../store/authContext';
import { useTheme } from '../../ui/ThemeProvider';
export function Login() {
    var _a, _b;
    const { login, isAuthenticated, isLoading: authLoading, error: authError } = useAuth();
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState();
    const navigate = useNavigate();
    const location = useLocation();
    const { isDark } = useTheme();
    // Get the intended destination from location state, or default to dashboard
    const from = ((_b = (_a = location.state) === null || _a === void 0 ? void 0 : _a.from) === null || _b === void 0 ? void 0 : _b.pathname) || '/dashboard';
    // Redirect to intended destination if already authenticated
    useEffect(() => {
        if (isAuthenticated && !authLoading) {
            navigate(from, { replace: true });
        }
    }, [isAuthenticated, authLoading, navigate, from]);
    // Set error from auth store if available
    useEffect(() => {
        if (authError) {
            setError(authError);
        }
    }, [authError]);
    const handleLogin = async (data) => {
        var _a, _b, _c, _d;
        setIsLoading(true);
        setError(undefined);
        try {
            // Use the login function from our auth hook
            await login(data.email, data.password, data.remember);
            // Navigation will happen in the useEffect above when auth state changes
        }
        catch (err) {
            setError(((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) ||
                ((_d = (_c = err.response) === null || _c === void 0 ? void 0 : _c.data) === null || _d === void 0 ? void 0 : _d.error) ||
                "We couldn't sign you in. Please check your credentials and try again.");
        }
        finally {
            setIsLoading(false);
        }
    };
    return (_jsxs("div", { className: "min-h-screen flex flex-col items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8", children: [_jsx("div", { className: "fixed top-0 left-0 right-0 bg-surface shadow-elevation-1 h-16 flex items-center px-4 sm:px-6 lg:px-8 z-10", children: _jsxs("div", { className: "flex items-center space-x-3", children: [_jsx("div", { className: "w-10 h-10 rounded-full bg-primary flex items-center justify-center text-on-primary text-lg font-medium", children: "L" }), _jsx("h1", { className: "text-xl font-medium text-on-surface", children: "LifeOS" })] }) }), _jsxs("div", { className: "w-full max-w-md", children: [_jsxs("div", { className: "flex flex-col items-center mb-12", children: [_jsx("div", { className: "w-20 h-20 rounded-full bg-primary flex items-center justify-center text-on-primary text-3xl font-medium mb-4 shadow-elevation-2", children: "L" }), _jsx("h1", { className: "text-3xl font-medium text-on-surface mb-2", children: "LifeOS" }), _jsx("p", { className: "text-on-surface-variant text-center", children: "Manage your life in one place" })] }), _jsx("div", { className: "mb-8 bg-surface rounded-xl shadow-elevation-2 transition-all hover:shadow-elevation-3", children: _jsx(LoginForm, { onSubmit: handleLogin, isLoading: isLoading, error: error }) }), _jsxs("div", { className: "mt-8 text-center text-sm text-on-surface-variant", children: [_jsx("p", { children: "Trouble signing in? Contact your administrator" }), _jsxs("p", { className: "mt-4 text-on-surface-variant/70", children: ["\u00A9 ", new Date().getFullYear(), " LifeOS. All rights reserved."] })] })] })] }));
}

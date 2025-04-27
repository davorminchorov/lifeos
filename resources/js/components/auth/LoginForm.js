import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { Eye, EyeOff, Lock, Mail } from 'lucide-react';
import { Button } from '../ui/Button';
// Utility to merge tailwind classes
const cn = (...inputs) => {
    return twMerge(clsx(inputs));
};
export function LoginForm({ onSubmit, isLoading = false, error }) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [remember, setRemember] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    // Material Design states for input fields
    const [emailFocused, setEmailFocused] = useState(false);
    const [passwordFocused, setPasswordFocused] = useState(false);
    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit({ email, password, remember });
    };
    return (_jsx("div", { className: "w-full max-w-md mx-auto", children: _jsxs("div", { className: "bg-white p-6 sm:p-8 rounded-lg shadow-elevation-1", children: [_jsxs("div", { className: "mb-8 text-center", children: [_jsx("h1", { className: "text-2xl font-medium text-slate-800 mb-2", children: "Welcome back" }), _jsx("p", { className: "text-slate-600", children: "Sign in to your LifeOS account" })] }), error && (_jsx("div", { className: "mb-6 p-4 bg-red-50 border border-red-100 rounded-md text-red-600 text-sm", children: error })), _jsxs("form", { onSubmit: handleSubmit, className: "space-y-6", children: [_jsx("div", { className: "relative", children: _jsxs("div", { className: cn("border rounded-md transition-colors relative", emailFocused || email ? "border-teal-600" : "border-slate-300", "h-14"), children: [_jsx("label", { htmlFor: "email", className: cn("absolute left-9 transition-all duration-150 pointer-events-none", emailFocused || email
                                            ? "text-xs text-teal-600 top-2"
                                            : "text-slate-500 text-base top-1/2 -translate-y-1/2"), children: "Email address" }), _jsx("div", { className: "absolute inset-y-0 left-0 pl-3 flex items-center", children: _jsx(Mail, { size: 18, className: cn("text-slate-400", emailFocused || email ? "text-teal-600" : "") }) }), _jsx("input", { id: "email", name: "email", type: "email", autoComplete: "email", required: true, value: email, onChange: (e) => setEmail(e.target.value), onFocus: () => setEmailFocused(true), onBlur: () => setEmailFocused(false), className: cn("block w-full h-full pl-9 pr-3 pt-6 pb-2 rounded-md", "text-slate-800 bg-transparent", "focus:outline-none placeholder:text-transparent"), placeholder: "Email address", disabled: isLoading })] }) }), _jsx("div", { className: "relative", children: _jsxs("div", { className: cn("border rounded-md transition-colors relative", passwordFocused || password ? "border-teal-600" : "border-slate-300", "h-14"), children: [_jsx("label", { htmlFor: "password", className: cn("absolute left-9 transition-all duration-150 pointer-events-none", passwordFocused || password
                                            ? "text-xs text-teal-600 top-2"
                                            : "text-slate-500 text-base top-1/2 -translate-y-1/2"), children: "Password" }), _jsx("div", { className: "absolute inset-y-0 left-0 pl-3 flex items-center", children: _jsx(Lock, { size: 18, className: cn("text-slate-400", passwordFocused || password ? "text-teal-600" : "") }) }), _jsx("input", { id: "password", name: "password", type: showPassword ? "text" : "password", autoComplete: "current-password", required: true, value: password, onChange: (e) => setPassword(e.target.value), onFocus: () => setPasswordFocused(true), onBlur: () => setPasswordFocused(false), className: cn("block w-full h-full pl-9 pr-10 pt-6 pb-2 rounded-md", "text-slate-800 bg-transparent", "focus:outline-none placeholder:text-transparent"), placeholder: "Password", disabled: isLoading }), _jsx("button", { type: "button", className: "absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600", onClick: () => setShowPassword(!showPassword), "aria-label": showPassword ? "Hide password" : "Show password", children: showPassword ? _jsx(EyeOff, { size: 18 }) : _jsx(Eye, { size: 18 }) })] }) }), _jsx("div", { className: "flex items-center", children: _jsxs("div", { className: "relative inline-flex items-center", children: [_jsx("input", { id: "remember", name: "remember", type: "checkbox", className: "peer sr-only", checked: remember, onChange: (e) => setRemember(e.target.checked), disabled: isLoading }), _jsx("label", { htmlFor: "remember", className: cn("relative cursor-pointer flex items-center justify-center w-5 h-5 border rounded-sm mr-2 transition-colors", remember ? "bg-teal-600 border-teal-600" : "border-slate-400 hover:border-teal-600", "after:content-[''] after:absolute after:hidden peer-checked:after:block", "after:w-1.5 after:h-3 after:border-r-2 after:border-b-2 after:border-white", "after:rotate-45 after:-translate-y-[2px]") }), _jsx("span", { className: "text-sm text-slate-600", children: "Remember me" })] }) }), _jsx("div", { className: "pt-4", children: _jsx(Button, { type: "submit", variant: "contained", isLoading: isLoading, fullWidth: true, children: "Sign in" }) }), _jsx("div", { className: "text-center pt-2", children: _jsx("button", { type: "button", className: "text-sm text-teal-600 hover:text-teal-700 font-medium", children: "Forgot password?" }) })] })] }) }));
}

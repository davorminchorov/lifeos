import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { createContext, useContext, useState } from 'react';
import { cn } from '../utils/cn';
import { X } from 'lucide-react';
const ToastContext = createContext(undefined);
export function useToast() {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
}
export const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);
    const toast = ({ title, description, variant = 'default', duration = 5000 }) => {
        const id = Math.random().toString(36).substring(2, 9);
        const newToast = { id, title, description, variant, duration };
        setToasts((prev) => [...prev, newToast]);
        if (duration > 0) {
            setTimeout(() => {
                dismiss(id);
            }, duration);
        }
        return id;
    };
    const dismiss = (id) => {
        setToasts((prev) => prev.filter((toast) => toast.id !== id));
    };
    return (_jsxs(ToastContext.Provider, { value: { toasts, toast, dismiss }, children: [children, toasts.length > 0 && (_jsx("div", { className: "fixed bottom-0 right-0 p-4 z-50 flex flex-col gap-2", children: toasts.map((toast) => (_jsx(ToastComponent, { toast: toast, onDismiss: () => dismiss(toast.id) }, toast.id))) }))] }));
};
const ToastComponent = ({ toast, onDismiss }) => {
    const { title, description, variant = 'default' } = toast;
    return (_jsxs("div", { className: cn('pointer-events-auto flex w-full max-w-md rounded-lg shadow-elevation-3 border transition-all animate-in slide-in-from-bottom-5 duration-300', {
            'bg-surface text-on-surface border-outline/20': variant === 'default',
            'bg-error-container text-on-error-container border-error': variant === 'destructive',
            'bg-tertiary-container text-on-tertiary-container border-tertiary': variant === 'success',
        }), children: [_jsxs("div", { className: "flex-1 p-4", children: [title && _jsx("div", { className: "text-label-large font-medium", children: title }), description && _jsx("div", { className: "text-body-medium mt-1", children: description })] }), _jsx("button", { onClick: onDismiss, className: cn('flex-shrink-0 p-2 rounded-full my-2 mr-2 hover:bg-surface-variant/30', {
                    'text-on-surface-variant': variant === 'default',
                    'text-on-error-container': variant === 'destructive',
                    'text-on-tertiary-container': variant === 'success',
                }), children: _jsx(X, { className: "h-4 w-4" }) })] }));
};
export default ToastProvider;

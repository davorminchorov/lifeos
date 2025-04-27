import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Button } from '../../ui';
const RecordPaymentModal = ({ onClose, onSubmit, defaultAmount, defaultCurrency, isSubmitting = false, error = null, }) => {
    const [formData, setFormData] = useState({
        amount: defaultAmount,
        payment_date: new Date().toISOString().split('T')[0],
        notes: '',
    });
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: name === 'amount' ? parseFloat(value) : value })));
    };
    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit(formData);
    };
    return (_jsx("div", { className: "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4", children: _jsxs("div", { className: "bg-surface rounded-lg shadow-elevation-3 w-full max-w-md", children: [_jsxs("div", { className: "flex justify-between items-center px-6 py-4 border-b border-outline border-opacity-20", children: [_jsx("h3", { className: "text-lg font-medium text-on-surface", children: "Record Payment" }), _jsx("button", { onClick: onClose, className: "text-on-surface-variant hover:text-on-surface focus:outline-none", children: _jsx("svg", { className: "h-6 w-6", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M6 18L18 6M6 6l12 12" }) }) })] }), _jsxs("form", { onSubmit: handleSubmit, className: "p-6", children: [error && (_jsx("div", { className: "mb-4 p-3 bg-error-container border border-error text-on-error-container rounded", children: error })), _jsxs("div", { className: "space-y-4", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "amount", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Payment Amount ", defaultCurrency && `(${defaultCurrency})`] }), _jsx("input", { type: "number", id: "amount", name: "amount", value: formData.amount, onChange: handleChange, min: "0.01", step: "0.01", className: "w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface", required: true })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "payment_date", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Payment Date" }), _jsx("input", { type: "date", id: "payment_date", name: "payment_date", value: formData.payment_date, onChange: handleChange, className: "w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface", required: true })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "notes", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Notes (Optional)" }), _jsx("textarea", { id: "notes", name: "notes", value: formData.notes, onChange: handleChange, rows: 3, className: "w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface", placeholder: "Add any notes about this payment" })] })] }), _jsxs("div", { className: "mt-6 flex justify-end space-x-3", children: [_jsx(Button, { variant: "text", onClick: onClose, disabled: isSubmitting, children: "Cancel" }), _jsx(Button, { variant: "filled", type: "submit", disabled: isSubmitting, children: isSubmitting ? 'Recording...' : 'Record Payment' })] })] })] }) }));
};
export default RecordPaymentModal;

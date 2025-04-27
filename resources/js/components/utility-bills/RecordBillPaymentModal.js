import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Button } from '../../ui/Button/Button';
const RecordBillPaymentModal = ({ isOpen, onClose, onSubmit, initialAmount, currency, isLoading, error, }) => {
    const [formData, setFormData] = useState({
        amount: initialAmount || 0,
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: '',
        reference_number: '',
        notes: '',
    });
    if (!isOpen)
        return null;
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: name === 'amount' ? parseFloat(value) : value })));
    };
    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit(formData);
    };
    const paymentMethodOptions = [
        { value: 'bank_transfer', label: 'Bank Transfer' },
        { value: 'credit_card', label: 'Credit Card' },
        { value: 'debit_card', label: 'Debit Card' },
        { value: 'direct_debit', label: 'Direct Debit' },
        { value: 'cash', label: 'Cash' },
        { value: 'check', label: 'Check' },
        { value: 'other', label: 'Other' },
    ];
    return (_jsx("div", { className: "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4", children: _jsxs("div", { className: "bg-white rounded-lg shadow-xl w-full max-w-md", children: [_jsxs("div", { className: "flex justify-between items-center px-6 py-4 border-b border-gray-200", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Record Bill Payment" }), _jsx("button", { onClick: onClose, className: "text-gray-400 hover:text-gray-500 focus:outline-none", children: _jsx("svg", { className: "h-6 w-6", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M6 18L18 6M6 6l12 12" }) }) })] }), _jsxs("form", { onSubmit: handleSubmit, className: "p-6", children: [error && (_jsx("div", { className: "mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded", children: error })), _jsxs("div", { className: "space-y-4", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "amount", className: "block text-sm font-medium text-gray-700 mb-1", children: ["Payment Amount (", currency, ")"] }), _jsx("input", { type: "number", id: "amount", name: "amount", value: formData.amount, onChange: handleChange, min: "0.01", step: "0.01", className: "w-full rounded-md border border-gray-300 shadow-sm p-2", required: true })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "payment_date", className: "block text-sm font-medium text-gray-700 mb-1", children: "Payment Date" }), _jsx("input", { type: "date", id: "payment_date", name: "payment_date", value: formData.payment_date, onChange: handleChange, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", required: true })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "payment_method", className: "block text-sm font-medium text-gray-700 mb-1", children: "Payment Method" }), _jsxs("select", { id: "payment_method", name: "payment_method", value: formData.payment_method, onChange: handleChange, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", required: true, children: [_jsx("option", { value: "", children: "Select a payment method" }), paymentMethodOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value)))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "reference_number", className: "block text-sm font-medium text-gray-700 mb-1", children: "Reference Number (Optional)" }), _jsx("input", { type: "text", id: "reference_number", name: "reference_number", value: formData.reference_number, onChange: handleChange, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", placeholder: "e.g. transaction ID, confirmation code" })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "notes", className: "block text-sm font-medium text-gray-700 mb-1", children: "Notes (Optional)" }), _jsx("textarea", { id: "notes", name: "notes", value: formData.notes, onChange: handleChange, rows: 3, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", placeholder: "Add any notes about this payment" })] })] }), _jsxs("div", { className: "mt-6 flex justify-end space-x-3", children: [_jsx(Button, { variant: "outlined", onClick: onClose, disabled: isLoading, children: "Cancel" }), _jsx(Button, { type: "submit", disabled: isLoading, children: isLoading ? 'Recording...' : 'Record Payment' })] })] })] }) }));
};
export default RecordBillPaymentModal;

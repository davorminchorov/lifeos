import { jsxs as _jsxs, jsx as _jsx } from "react/jsx-runtime";
import React from 'react';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { usePaymentStore } from '../../store/paymentStore';
import { useRecordPayment } from '../../queries/paymentQueries';
const PaymentForm = ({ subscriptionId, subscriptionName, defaultAmount, currency, onSuccess, onCancel }) => {
    const [state, actions] = usePaymentStore();
    const { formData, formErrors, isSubmitting, submitError } = state;
    // Initialize form data if needed
    React.useEffect(() => {
        if (formData.amount === 0) {
            actions.updateFormField({ name: 'amount', value: defaultAmount });
        }
    }, [defaultAmount, formData.amount, actions]);
    // Use mutation hook
    const recordPayment = useRecordPayment();
    const handleChange = (e) => {
        const { name, value } = e.target;
        actions.updateFormField({ name, value });
        // Clear error for this field when user updates it
        if (formErrors[name]) {
            actions.clearFormError(name);
        }
    };
    const validate = () => {
        const newErrors = {};
        if (!formData.amount || formData.amount <= 0) {
            newErrors.amount = 'Amount must be greater than zero';
        }
        if (!formData.payment_date) {
            newErrors.payment_date = 'Payment date is required';
        }
        actions.setFormErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!validate()) {
            return;
        }
        actions.setIsSubmitting(true);
        actions.setSubmitError(null);
        recordPayment.mutate({ subscriptionId, formData }, {
            onSuccess: () => {
                if (onSuccess) {
                    onSuccess();
                }
            },
            onError: (error) => {
                var _a, _b, _c, _d;
                console.error('Payment submission error:', error);
                if ((_b = (_a = error.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.errors) {
                    // Handle validation errors from the server
                    const serverErrors = error.response.data.errors;
                    const formattedErrors = {};
                    Object.entries(serverErrors).forEach(([key, messages]) => {
                        formattedErrors[key] = Array.isArray(messages) ? messages[0] : messages;
                    });
                    actions.setFormErrors(formattedErrors);
                }
                else {
                    actions.setSubmitError(((_d = (_c = error.response) === null || _c === void 0 ? void 0 : _c.data) === null || _d === void 0 ? void 0 : _d.error) || 'An unexpected error occurred. Please try again.');
                }
            },
            onSettled: () => {
                actions.setIsSubmitting(false);
            }
        });
    };
    return (_jsxs(Card, { children: [_jsx("div", { className: "px-6 py-4 border-b border-gray-200", children: _jsxs("h2", { className: "text-lg font-medium text-gray-900", children: ["Record Payment for ", subscriptionName] }) }), _jsxs("form", { onSubmit: handleSubmit, className: "p-6", children: [submitError && (_jsx("div", { className: "mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded", children: submitError })), _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "amount", className: "block text-sm font-medium text-gray-700 mb-1", children: ["Amount ", _jsx("span", { className: "text-red-500", children: "*" })] }), _jsxs("div", { className: "relative rounded-md shadow-sm", children: [_jsx("div", { className: "absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none", children: _jsx("span", { className: "text-gray-500 sm:text-sm", children: currency === 'USD' ? '$' : currency }) }), _jsx("input", { type: "number", id: "amount", name: "amount", value: formData.amount, onChange: handleChange, className: `pl-7 w-full rounded-md border ${formErrors.amount ? 'border-red-500' : 'border-gray-300'} shadow-sm p-2`, placeholder: "0.00", min: "0.01", step: "0.01" })] }), formErrors.amount && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: formErrors.amount }))] }), _jsxs("div", { children: [_jsxs("label", { htmlFor: "payment_date", className: "block text-sm font-medium text-gray-700 mb-1", children: ["Payment Date ", _jsx("span", { className: "text-red-500", children: "*" })] }), _jsx("input", { type: "date", id: "payment_date", name: "payment_date", value: formData.payment_date, onChange: handleChange, className: `w-full rounded-md border ${formErrors.payment_date ? 'border-red-500' : 'border-gray-300'} shadow-sm p-2` }), formErrors.payment_date && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: formErrors.payment_date }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "notes", className: "block text-sm font-medium text-gray-700 mb-1", children: "Notes (Optional)" }), _jsx("textarea", { id: "notes", name: "notes", value: formData.notes, onChange: handleChange, rows: 3, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", placeholder: "Add any additional details about this payment" })] })] }), _jsxs("div", { className: "mt-8 flex justify-end space-x-3", children: [_jsx(Button, { variant: "outlined", type: "button", onClick: onCancel, children: "Cancel" }), _jsx(Button, { type: "submit", isLoading: isSubmitting, disabled: isSubmitting, children: "Record Payment" })] })] })] }));
};
export default PaymentForm;

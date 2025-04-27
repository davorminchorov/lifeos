import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { useToast } from '../../ui/Toast';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardFooter } from '../../ui/Card';
const ValuationForm = ({ investmentId, onSuccess, onCancel, initialValue = 0 }) => {
    var _a, _b;
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [serverError, setServerError] = useState(null);
    const { toast } = useToast();
    const { register, handleSubmit, formState: { errors } } = useForm({
        defaultValues: {
            value: initialValue ? initialValue.toString() : '',
            date: new Date().toISOString().split('T')[0],
            notes: '',
        }
    });
    const onSubmit = async (data) => {
        var _a, _b;
        setIsSubmitting(true);
        setServerError(null);
        try {
            await axios.post(`/api/investments/${investmentId}/valuations`, Object.assign(Object.assign({}, data), { value: parseFloat(data.value) }));
            toast({
                title: "Success",
                description: "Valuation added successfully",
                variant: "success",
            });
            if (onSuccess) {
                onSuccess();
            }
        }
        catch (error) {
            console.error('Error adding valuation:', error);
            if ((_b = (_a = error.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.errors) {
                // Handle field-specific errors if needed
                setServerError('Please check the form for errors and try again.');
                toast({
                    title: "Validation Error",
                    description: "Please check the form for errors and try again.",
                    variant: "destructive",
                });
            }
            else {
                setServerError('Failed to add valuation. Please try again.');
                toast({
                    title: "Error",
                    description: "Failed to add valuation. Please try again.",
                    variant: "destructive",
                });
            }
        }
        finally {
            setIsSubmitting(false);
        }
    };
    return (_jsx(Card, { className: "bg-white shadow-md rounded-xl overflow-hidden border border-gray-200", children: _jsxs("form", { onSubmit: handleSubmit(onSubmit), children: [_jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "space-y-6", children: [serverError && (_jsx("div", { className: "bg-red-50 border-l-4 border-red-400 p-4 rounded", children: _jsx("p", { className: "text-sm text-red-700", children: serverError }) })), _jsxs("div", { children: [_jsx("label", { htmlFor: "value", className: "block text-sm font-medium text-gray-700 mb-1", children: "Current Value" }), _jsxs("div", { className: "relative rounded-md shadow-sm", children: [_jsx("div", { className: "absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none", children: _jsx("span", { className: "text-gray-500 sm:text-sm", children: "$" }) }), _jsx("input", Object.assign({ type: "number", id: "value", step: "0.01", min: "0.01", placeholder: "0.00", className: `block w-full pl-8 pr-12 py-3 border ${errors.value ? 'border-red-300' : 'border-gray-300'} rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm` }, register('value', {
                                                required: 'Value is required',
                                                min: { value: 0.01, message: 'Value must be greater than 0' },
                                                validate: (value) => !isNaN(Number(value)) || 'Value must be a number'
                                            }))), _jsx("div", { className: "absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none", children: _jsx("span", { className: "text-gray-500 sm:text-sm", children: "USD" }) })] }), errors.value && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: ((_a = errors.value.message) === null || _a === void 0 ? void 0 : _a.toString()) || 'Please enter a valid value' }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "date", className: "block text-sm font-medium text-gray-700 mb-1", children: "Valuation Date" }), _jsx("input", Object.assign({ type: "date", id: "date", className: `block w-full py-3 px-4 border ${errors.date ? 'border-red-300' : 'border-gray-300'} rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm` }, register('date', { required: 'Date is required' }))), errors.date && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: ((_b = errors.date.message) === null || _b === void 0 ? void 0 : _b.toString()) || 'Please select a date' }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "notes", className: "block text-sm font-medium text-gray-700 mb-1", children: "Notes (Optional)" }), _jsx("textarea", Object.assign({ id: "notes", rows: 3, className: "block w-full py-3 px-4 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm", placeholder: "Add any notes about this valuation" }, register('notes')))] })] }) }), _jsxs(CardFooter, { className: "bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100", children: [_jsx(Button, { type: "button", variant: "outlined", onClick: onCancel, disabled: isSubmitting, children: "Cancel" }), _jsx(Button, { type: "submit", variant: "filled", disabled: isSubmitting, children: isSubmitting ? 'Saving...' : 'Save Valuation' })] })] }) }));
};
export default ValuationForm;

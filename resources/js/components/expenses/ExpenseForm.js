import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { useToast } from '../../ui/Toast';
const ExpenseForm = ({ initialData, isEditing = false, categories = [], onSuccess }) => {
    const navigate = useNavigate();
    const { toast } = useToast();
    const [formData, setFormData] = useState({
        title: (initialData === null || initialData === void 0 ? void 0 : initialData.title) || '',
        amount: (initialData === null || initialData === void 0 ? void 0 : initialData.amount) || 0,
        currency: (initialData === null || initialData === void 0 ? void 0 : initialData.currency) || 'USD',
        date: (initialData === null || initialData === void 0 ? void 0 : initialData.date) || new Date().toISOString().split('T')[0],
        category_id: (initialData === null || initialData === void 0 ? void 0 : initialData.category_id) || '',
        description: (initialData === null || initialData === void 0 ? void 0 : initialData.description) || '',
        payment_method: (initialData === null || initialData === void 0 ? void 0 : initialData.payment_method) || '',
        receipt_url: (initialData === null || initialData === void 0 ? void 0 : initialData.receipt_url) || '',
        notes: (initialData === null || initialData === void 0 ? void 0 : initialData.notes) || '',
    });
    const [availableCategories, setAvailableCategories] = useState(categories);
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);
    const [isLoadingCategories, setIsLoadingCategories] = useState(categories.length === 0);
    useEffect(() => {
        // Fetch categories if not provided
        if (categories.length === 0) {
            fetchCategories();
        }
    }, [categories]);
    const fetchCategories = async () => {
        var _a;
        try {
            setIsLoadingCategories(true);
            const response = await axios.get('/api/categories');
            setAvailableCategories(Array.isArray(response.data) ? response.data :
                (((_a = response.data) === null || _a === void 0 ? void 0 : _a.data) ? response.data.data : []));
        }
        catch (error) {
            console.error('Failed to fetch categories:', error);
            setSubmitError('Failed to load expense categories. Please try again.');
            toast({
                title: "Error",
                description: "Failed to load expense categories",
                variant: "destructive",
            });
        }
        finally {
            setIsLoadingCategories(false);
        }
    };
    const handleChange = (e) => {
        const { name, value } = e.target;
        // Handle numeric input for the amount field
        if (name === 'amount') {
            setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: value === '' ? 0 : parseFloat(value) })));
        }
        else {
            setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: value })));
        }
        // Clear error for this field when user updates it
        if (errors[name]) {
            setErrors((prev) => {
                const newErrors = Object.assign({}, prev);
                delete newErrors[name];
                return newErrors;
            });
        }
    };
    const validate = () => {
        const newErrors = {};
        if (!formData.title.trim()) {
            newErrors.title = 'Title is required';
        }
        if (formData.amount <= 0) {
            newErrors.amount = 'Amount must be greater than 0';
        }
        if (!formData.date) {
            newErrors.date = 'Date is required';
        }
        if (!formData.currency) {
            newErrors.currency = 'Currency is required';
        }
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };
    const handleSubmit = async (e) => {
        var _a, _b, _c, _d, _e, _f;
        e.preventDefault();
        if (!validate()) {
            return;
        }
        setIsSubmitting(true);
        setSubmitError(null);
        try {
            if (isEditing && (initialData === null || initialData === void 0 ? void 0 : initialData.id)) {
                // Update existing expense
                await axios.put(`/api/expenses/${initialData.id}`, formData);
                toast({
                    title: "Success",
                    description: "Expense updated successfully",
                    variant: "success",
                });
                navigate(`/expenses/${initialData.id}`);
            }
            else {
                // Create new expense
                const response = await axios.post('/api/expenses', formData);
                toast({
                    title: "Success",
                    description: "Expense created successfully",
                    variant: "success",
                });
                navigate(`/expenses/${response.data.id}`);
            }
            if (onSuccess) {
                onSuccess();
            }
        }
        catch (error) {
            console.error('Submission error:', error);
            if ((_b = (_a = error.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.errors) {
                // Handle validation errors from the server
                const serverErrors = error.response.data.errors;
                const formattedErrors = {};
                Object.entries(serverErrors).forEach(([key, messages]) => {
                    formattedErrors[key] = Array.isArray(messages) ? messages[0] : messages;
                });
                setErrors(formattedErrors);
                toast({
                    title: "Validation Error",
                    description: "Please correct the errors in the form",
                    variant: "destructive",
                });
            }
            else {
                setSubmitError(((_d = (_c = error.response) === null || _c === void 0 ? void 0 : _c.data) === null || _d === void 0 ? void 0 : _d.error) || 'An unexpected error occurred. Please try again.');
                toast({
                    title: "Error",
                    description: ((_f = (_e = error.response) === null || _e === void 0 ? void 0 : _e.data) === null || _f === void 0 ? void 0 : _f.error) || 'An unexpected error occurred. Please try again.',
                    variant: "destructive",
                });
            }
        }
        finally {
            setIsSubmitting(false);
        }
    };
    const currencyOptions = [
        { value: 'USD', label: 'USD - US Dollar' },
        { value: 'EUR', label: 'EUR - Euro' },
        { value: 'GBP', label: 'GBP - British Pound' },
        { value: 'CAD', label: 'CAD - Canadian Dollar' },
        { value: 'AUD', label: 'AUD - Australian Dollar' },
        { value: 'JPY', label: 'JPY - Japanese Yen' },
    ];
    const paymentMethodOptions = [
        { value: 'credit_card', label: 'Credit Card' },
        { value: 'debit_card', label: 'Debit Card' },
        { value: 'cash', label: 'Cash' },
        { value: 'bank_transfer', label: 'Bank Transfer' },
        { value: 'mobile_payment', label: 'Mobile Payment' },
        { value: 'other', label: 'Other' },
    ];
    return (_jsxs(Card, { className: "max-w-2xl mx-auto shadow-elevation-2 border border-outline/40", children: [_jsx("div", { className: "px-6 py-4 border-b border-outline-variant/60", children: _jsx("h2", { className: "text-headline-small font-medium text-on-surface", children: isEditing ? 'Edit Expense' : 'Add New Expense' }) }), _jsxs("form", { onSubmit: handleSubmit, className: "p-6", children: [submitError && (_jsx("div", { className: "mb-4 p-3 bg-error-container border border-error/50 text-on-error-container rounded shadow-elevation-1", children: submitError })), _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "title", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: ["Title ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "text", id: "title", name: "title", value: formData.title, onChange: handleChange, className: `w-full rounded-md border ${errors.title ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, placeholder: "e.g. Groceries, Restaurant, Taxi" }), errors.title && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.title }))] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "amount", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: ["Amount ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "number", id: "amount", name: "amount", value: formData.amount, onChange: handleChange, min: "0.01", step: "0.01", className: `w-full rounded-md border ${errors.amount ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, placeholder: "0.00" }), errors.amount && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.amount }))] }), _jsxs("div", { children: [_jsxs("label", { htmlFor: "currency", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: ["Currency ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("select", { id: "currency", name: "currency", value: formData.currency, onChange: handleChange, className: `w-full rounded-md border ${errors.currency ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, children: currencyOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value))) }), errors.currency && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.currency }))] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "date", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: ["Date ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "date", id: "date", name: "date", value: formData.date, onChange: handleChange, className: `w-full rounded-md border ${errors.date ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary` }), errors.date && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.date }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "category_id", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Category" }), _jsxs("select", { id: "category_id", name: "category_id", value: formData.category_id, onChange: handleChange, className: `w-full rounded-md border ${errors.category_id ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, disabled: isLoadingCategories, children: [_jsx("option", { value: "", children: "Select a category" }), isLoadingCategories ? (_jsx("option", { disabled: true, children: "Loading categories..." })) : (availableCategories.map(category => (_jsx("option", { value: category.id, children: category.name }, category.id))))] }), errors.category_id && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.category_id }))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "payment_method", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Payment Method" }), _jsxs("select", { id: "payment_method", name: "payment_method", value: formData.payment_method, onChange: handleChange, className: `w-full rounded-md border ${errors.payment_method ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, children: [_jsx("option", { value: "", children: "Select payment method" }), paymentMethodOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value)))] }), errors.payment_method && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.payment_method }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "description", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Description" }), _jsx("textarea", { id: "description", name: "description", value: formData.description, onChange: handleChange, rows: 3, className: `w-full rounded-md border ${errors.description ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, placeholder: "Detailed description of the expense" }), errors.description && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.description }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "notes", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Notes" }), _jsx("textarea", { id: "notes", name: "notes", value: formData.notes, onChange: handleChange, rows: 2, className: `w-full rounded-md border ${errors.notes ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, placeholder: "Any additional notes" }), errors.notes && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.notes }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "receipt_url", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Receipt URL" }), _jsx("input", { type: "url", id: "receipt_url", name: "receipt_url", value: formData.receipt_url, onChange: handleChange, className: `w-full rounded-md border ${errors.receipt_url ? 'border-error' : 'border-outline/50'} shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`, placeholder: "https://example.com/receipt.pdf" }), errors.receipt_url && (_jsx("p", { className: "mt-1 text-body-small text-error", children: errors.receipt_url })), _jsx("p", { className: "mt-1 text-body-small text-on-surface-variant", children: "Link to an online receipt if available" })] }), _jsxs("div", { className: "flex justify-end space-x-3 pt-4", children: [_jsx(Button, { type: "button", onClick: () => navigate('/expenses'), disabled: isSubmitting, children: "Cancel" }), _jsx(Button, { type: "submit", disabled: isSubmitting, children: isSubmitting
                                            ? (isEditing ? 'Updating...' : 'Creating...')
                                            : (isEditing ? 'Update Expense' : 'Create Expense') })] })] })] })] }));
};
export default ExpenseForm;

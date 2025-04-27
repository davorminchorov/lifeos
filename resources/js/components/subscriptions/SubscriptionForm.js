import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import { useToast } from '../../ui/Toast';
const SubscriptionForm = ({ initialData, isEditing = false }) => {
    const navigate = useNavigate();
    const { toast } = useToast();
    const [formData, setFormData] = useState({
        name: (initialData === null || initialData === void 0 ? void 0 : initialData.name) || '',
        description: (initialData === null || initialData === void 0 ? void 0 : initialData.description) || '',
        amount: (initialData === null || initialData === void 0 ? void 0 : initialData.amount) || 0,
        currency: (initialData === null || initialData === void 0 ? void 0 : initialData.currency) || 'USD',
        billing_cycle: (initialData === null || initialData === void 0 ? void 0 : initialData.billing_cycle) || 'monthly',
        start_date: (initialData === null || initialData === void 0 ? void 0 : initialData.start_date) || new Date().toISOString().split('T')[0],
        website: (initialData === null || initialData === void 0 ? void 0 : initialData.website) || '',
        category: (initialData === null || initialData === void 0 ? void 0 : initialData.category) || '',
    });
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState(null);
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
        if (!formData.name.trim()) {
            newErrors.name = 'Name is required';
        }
        if (!formData.description.trim()) {
            newErrors.description = 'Description is required';
        }
        if (!formData.amount || formData.amount <= 0) {
            newErrors.amount = 'Amount must be greater than zero';
        }
        if (!formData.currency) {
            newErrors.currency = 'Currency is required';
        }
        if (!formData.billing_cycle) {
            newErrors.billing_cycle = 'Billing cycle is required';
        }
        if (!formData.start_date) {
            newErrors.start_date = 'Start date is required';
        }
        if (formData.website && !/^https?:\/\/.*/.test(formData.website)) {
            newErrors.website = 'Website must be a valid URL starting with http:// or https://';
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
                // Update existing subscription
                await axios.put(`/api/subscriptions/${initialData.id}`, formData);
                toast({
                    title: "Success",
                    description: "Subscription updated successfully",
                    variant: "success",
                });
                navigate(`/subscriptions/${initialData.id}`);
            }
            else {
                // Create new subscription
                const response = await axios.post('/api/subscriptions', formData);
                toast({
                    title: "Success",
                    description: "Subscription created successfully",
                    variant: "success",
                });
                navigate(`/subscriptions/${response.data.subscription_id}`);
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
    const billingCycleOptions = [
        { value: 'daily', label: 'Daily' },
        { value: 'weekly', label: 'Weekly' },
        { value: 'biweekly', label: 'Biweekly' },
        { value: 'monthly', label: 'Monthly' },
        { value: 'bimonthly', label: 'Bimonthly' },
        { value: 'quarterly', label: 'Quarterly' },
        { value: 'semiannually', label: 'Semiannually' },
        { value: 'annually', label: 'Annually' },
    ];
    const currencyOptions = [
        { value: 'USD', label: 'USD - US Dollar' },
        { value: 'EUR', label: 'EUR - Euro' },
        { value: 'GBP', label: 'GBP - British Pound' },
        { value: 'CAD', label: 'CAD - Canadian Dollar' },
        { value: 'AUD', label: 'AUD - Australian Dollar' },
        { value: 'JPY', label: 'JPY - Japanese Yen' },
    ];
    const categoryOptions = [
        { value: 'streaming', label: 'Streaming Services' },
        { value: 'software', label: 'Software & Apps' },
        { value: 'hosting', label: 'Web Hosting' },
        { value: 'utilities', label: 'Utilities' },
        { value: 'memberships', label: 'Memberships' },
        { value: 'other', label: 'Other' },
    ];
    return (_jsxs(Card, { variant: "elevated", className: "max-w-2xl mx-auto", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: isEditing ? 'Edit Subscription' : 'Add New Subscription' }) }), _jsx(CardContent, { children: _jsxs("form", { onSubmit: handleSubmit, children: [submitError && (_jsx("div", { className: "mb-6 p-3 bg-error-container border border-error text-on-error-container rounded", children: submitError })), _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "name", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Name ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "text", id: "name", name: "name", value: formData.name, onChange: handleChange, className: `w-full rounded-md border ${errors.name ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, placeholder: "e.g. Netflix, Spotify, etc." }), errors.name && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.name }))] }), _jsxs("div", { children: [_jsxs("label", { htmlFor: "description", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Description ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("textarea", { id: "description", name: "description", value: formData.description, onChange: handleChange, rows: 3, className: `w-full rounded-md border ${errors.description ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, placeholder: "Brief description of the subscription" }), errors.description && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.description }))] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "amount", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Amount ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "number", id: "amount", name: "amount", value: formData.amount, onChange: handleChange, min: "0.01", step: "0.01", className: `w-full rounded-md border ${errors.amount ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, placeholder: "0.00" }), errors.amount && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.amount }))] }), _jsxs("div", { children: [_jsxs("label", { htmlFor: "currency", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Currency ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("select", { id: "currency", name: "currency", value: formData.currency, onChange: handleChange, className: `w-full rounded-md border ${errors.currency ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, children: currencyOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value))) }), errors.currency && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.currency }))] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "billing_cycle", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Billing Cycle ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("select", { id: "billing_cycle", name: "billing_cycle", value: formData.billing_cycle, onChange: handleChange, className: `w-full rounded-md border ${errors.billing_cycle ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, children: billingCycleOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value))) }), errors.billing_cycle && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.billing_cycle }))] }), _jsxs("div", { children: [_jsxs("label", { htmlFor: "start_date", className: "block text-sm font-medium text-on-surface-variant mb-1", children: ["Start Date ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "date", id: "start_date", name: "start_date", value: formData.start_date, onChange: handleChange, className: `w-full rounded-md border ${errors.start_date ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface` }), errors.start_date && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.start_date }))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "category", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Category" }), _jsxs("select", { id: "category", name: "category", value: formData.category, onChange: handleChange, className: `w-full rounded-md border ${errors.category ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, children: [_jsx("option", { value: "", children: "Select a category" }), categoryOptions.map(option => (_jsx("option", { value: option.value, children: option.label }, option.value)))] }), errors.category && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.category }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "website", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Website" }), _jsx("input", { type: "url", id: "website", name: "website", value: formData.website, onChange: handleChange, className: `w-full rounded-md border ${errors.website ? 'border-error' : 'border-outline border-opacity-30'} shadow-sm p-2 bg-surface text-on-surface`, placeholder: "https://example.com" }), errors.website && (_jsx("p", { className: "mt-1 text-sm text-error", children: errors.website })), _jsx("p", { className: "mt-1 text-xs text-on-surface-variant", children: "Optional: Enter the website URL for this subscription service" })] }), _jsxs("div", { className: "flex justify-end space-x-3 pt-4", children: [_jsx(Button, { type: "button", variant: "text", onClick: () => navigate('/subscriptions'), disabled: isSubmitting, children: "Cancel" }), _jsx(Button, { type: "submit", variant: "filled", disabled: isSubmitting, children: isSubmitting
                                                ? (isEditing ? 'Updating...' : 'Creating...')
                                                : (isEditing ? 'Update Subscription' : 'Create Subscription') })] })] })] }) })] }));
};
export default SubscriptionForm;

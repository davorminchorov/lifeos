import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
export const BudgetForm = ({ onSuccess, initialData }) => {
    var _a;
    const [categoryId, setCategoryId] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.category_id) || null);
    const [amount, setAmount] = useState(((_a = initialData === null || initialData === void 0 ? void 0 : initialData.amount) === null || _a === void 0 ? void 0 : _a.toString()) || '');
    const [startDate, setStartDate] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.start_date) || '');
    const [endDate, setEndDate] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.end_date) || '');
    const [notes, setNotes] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.notes) || '');
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    useEffect(() => {
        // Set default dates if not editing
        if (!initialData) {
            const today = new Date();
            const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            setStartDate(firstOfMonth.toISOString().split('T')[0]);
            setEndDate(lastOfMonth.toISOString().split('T')[0]);
        }
        // Fetch categories
        const fetchCategories = async () => {
            try {
                const response = await axios.get('/api/categories');
                if (response.data && Array.isArray(response.data.data)) {
                    setCategories(response.data.data);
                }
                else {
                    setCategories([]);
                }
            }
            catch (err) {
                console.error('Failed to fetch categories', err);
                setCategories([]);
            }
        };
        fetchCategories();
    }, [initialData]);
    const handleSubmit = async (e) => {
        var _a, _b;
        e.preventDefault();
        setLoading(true);
        setError('');
        try {
            const payload = {
                category_id: categoryId,
                amount: parseFloat(amount),
                start_date: startDate,
                end_date: endDate,
                notes: notes || null,
            };
            if (initialData === null || initialData === void 0 ? void 0 : initialData.budget_id) {
                // Update existing budget (this would require a backend endpoint)
                await axios.put(`/api/budgets/${initialData.budget_id}`, payload);
            }
            else {
                // Create new budget
                await axios.post('/api/budgets', payload);
            }
            onSuccess();
        }
        catch (err) {
            setError('Failed to save budget. ' + (((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) || ''));
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsxs(Card, { className: "p-6 shadow-elevation-2 border border-outline/40", children: [_jsx("h2", { className: "text-headline-small font-medium text-on-surface mb-4", children: initialData ? 'Edit Budget' : 'Create New Budget' }), error && (_jsx("div", { className: "bg-error-container text-on-error-container p-3 rounded mb-4 border border-error/50 shadow-elevation-1", children: error })), _jsxs("form", { onSubmit: handleSubmit, children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { children: [_jsx("label", { className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Category" }), _jsxs("select", { value: categoryId || '', onChange: (e) => setCategoryId(e.target.value || null), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", children: [_jsx("option", { value: "", children: "All Categories (Overall Budget)" }), Array.isArray(categories) && categories.map((category) => (_jsx("option", { value: category.category_id, children: category.name }, category.category_id)))] })] }), _jsxs("div", { children: [_jsx("label", { className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Budget Amount" }), _jsx("input", { type: "number", min: "0", step: "0.01", value: amount, onChange: (e) => setAmount(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", required: true })] }), _jsxs("div", { children: [_jsx("label", { className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Start Date" }), _jsx("input", { type: "date", value: startDate, onChange: (e) => setStartDate(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", required: true })] }), _jsxs("div", { children: [_jsx("label", { className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "End Date" }), _jsx("input", { type: "date", value: endDate, onChange: (e) => setEndDate(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", required: true })] }), _jsxs("div", { className: "col-span-2", children: [_jsx("label", { className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Notes" }), _jsx("textarea", { value: notes, onChange: (e) => setNotes(e.target.value), className: "w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary", rows: 3 })] })] }), _jsxs("div", { className: "mt-6 flex justify-end space-x-3", children: [_jsx(Button, { type: "button", onClick: onSuccess, variant: "outlined", children: "Cancel" }), _jsx(Button, { type: "submit", disabled: loading, children: loading ? 'Saving...' : 'Save Budget' })] })] })] }));
};

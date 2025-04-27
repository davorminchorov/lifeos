import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
export const CategoryForm = ({ onSuccess, initialData }) => {
    const [name, setName] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.name) || '');
    const [description, setDescription] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.description) || '');
    const [color, setColor] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.color) || '#3b82f6');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const handleSubmit = async (e) => {
        var _a, _b;
        e.preventDefault();
        setLoading(true);
        setError('');
        try {
            const payload = {
                name,
                description: description || null,
                color,
            };
            if (initialData === null || initialData === void 0 ? void 0 : initialData.category_id) {
                // Update existing category
                await axios.put(`/api/categories/${initialData.category_id}`, payload);
            }
            else {
                // Create new category
                await axios.post('/api/categories', payload);
            }
            onSuccess();
        }
        catch (err) {
            setError('Failed to save category. ' + (((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) || ''));
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsxs(Card, { className: "shadow-elevation-2 border border-outline/40", children: [_jsx("div", { className: "px-6 py-4 border-b border-outline-variant/60", children: _jsx("h2", { className: "text-headline-small font-medium text-on-surface", children: initialData ? 'Edit Category' : 'Create New Category' }) }), _jsxs("form", { onSubmit: handleSubmit, className: "p-6", children: [error && (_jsx("div", { className: "mb-4 p-3 bg-error-container border border-error/50 text-on-error-container rounded shadow-elevation-1", children: error })), _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsxs("label", { htmlFor: "name", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: ["Name ", _jsx("span", { className: "text-error", children: "*" })] }), _jsx("input", { type: "text", id: "name", value: name, onChange: (e) => setName(e.target.value), className: "w-full rounded-md border border-outline/50 shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary", required: true })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "description", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Description" }), _jsx("textarea", { id: "description", value: description, onChange: (e) => setDescription(e.target.value), className: "w-full rounded-md border border-outline/50 shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary", rows: 3, placeholder: "What is this category used for?" })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "color", className: "block text-body-medium font-medium text-on-surface-variant mb-1", children: "Color" }), _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("input", { type: "color", id: "colorPicker", value: color, onChange: (e) => setColor(e.target.value), className: "h-10 w-20 rounded border-0" }), _jsx("input", { type: "text", id: "color", value: color, onChange: (e) => setColor(e.target.value), className: "w-32 rounded-md border border-outline/50 shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary", pattern: "^#[0-9A-Fa-f]{6}$", title: "Hex color code (e.g. #3b82f6)" }), _jsx("div", { className: "ml-4", children: _jsx("div", { className: "w-8 h-8 rounded-full border border-outline/50 shadow-elevation-1", style: { backgroundColor: color } }) })] }), _jsx("p", { className: "mt-1 text-body-small text-on-surface-variant", children: "Choose a color to visually identify this category" })] }), _jsxs("div", { className: "flex justify-end space-x-3 pt-4 border-t border-outline-variant/60", children: [_jsx(Button, { type: "button", onClick: onSuccess, variant: "outlined", children: "Cancel" }), _jsxs(Button, { type: "submit", disabled: loading, children: [loading && (_jsxs("svg", { className: "animate-spin -ml-1 mr-2 h-4 w-4", xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 24 24", children: [_jsx("circle", { className: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", strokeWidth: "4" }), _jsx("path", { className: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })] })), loading ? 'Saving...' : 'Save Category'] })] })] })] })] }));
};

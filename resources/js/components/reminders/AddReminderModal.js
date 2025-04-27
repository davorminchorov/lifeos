import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import { Button } from '../../ui/Button/Button';
const AddReminderModal = ({ isOpen, onClose, onSave, initialData, isLoading = false, }) => {
    const [formData, setFormData] = useState({
        reminder_date: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Tomorrow
        reminder_message: '',
        entity_type: (initialData === null || initialData === void 0 ? void 0 : initialData.entity_type) || '',
        entity_id: (initialData === null || initialData === void 0 ? void 0 : initialData.entity_id) || '',
    });
    useEffect(() => {
        if (initialData) {
            setFormData({
                reminder_date: initialData.reminder_date || new Date(Date.now() + 86400000).toISOString().split('T')[0],
                reminder_message: initialData.reminder_message || '',
                entity_type: initialData.entity_type || '',
                entity_id: initialData.entity_id || '',
            });
        }
    }, [initialData, isOpen]);
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: value })));
    };
    const handleSubmit = (e) => {
        e.preventDefault();
        onSave(formData);
    };
    if (!isOpen)
        return null;
    return (_jsx("div", { className: "fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50", children: _jsxs("div", { className: "bg-surface rounded-lg shadow-elevation-3 p-6 max-w-md w-full", children: [_jsxs("div", { className: "flex justify-between items-center mb-4", children: [_jsx("h2", { className: "text-headline-medium font-medium text-on-surface", children: (initialData === null || initialData === void 0 ? void 0 : initialData.id) ? 'Edit Reminder' : 'New Reminder' }), _jsx("button", { onClick: onClose, className: "p-2 rounded-full hover:bg-surface-variant/20 text-on-surface-variant", "aria-label": "Close", children: _jsx(X, { className: "h-5 w-5" }) })] }), _jsxs("form", { onSubmit: handleSubmit, children: [_jsxs("div", { className: "space-y-4", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "reminder_date", className: "block text-label-large font-medium text-on-surface mb-1", children: "Reminder Date" }), _jsx("input", { type: "date", id: "reminder_date", name: "reminder_date", value: formData.reminder_date, onChange: handleChange, className: "w-full p-3 rounded-md border border-outline/50 bg-surface focus:border-primary focus:ring-1 focus:ring-primary", min: new Date().toISOString().split('T')[0], required: true })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "reminder_message", className: "block text-label-large font-medium text-on-surface mb-1", children: "Reminder Message" }), _jsx("textarea", { id: "reminder_message", name: "reminder_message", value: formData.reminder_message, onChange: handleChange, rows: 3, className: "w-full p-3 rounded-md border border-outline/50 bg-surface focus:border-primary focus:ring-1 focus:ring-primary", placeholder: "What do you need to remember?", required: true })] }), formData.entity_type && (_jsx("input", { type: "hidden", name: "entity_type", value: formData.entity_type })), formData.entity_id && (_jsx("input", { type: "hidden", name: "entity_id", value: formData.entity_id }))] }), _jsxs("div", { className: "mt-6 flex justify-end space-x-3", children: [_jsx(Button, { type: "button", variant: "text", onClick: onClose, disabled: isLoading, children: "Cancel" }), _jsx(Button, { type: "submit", className: "bg-primary text-on-primary", disabled: isLoading, children: isLoading ? 'Saving...' : (initialData === null || initialData === void 0 ? void 0 : initialData.id) ? 'Update Reminder' : 'Save Reminder' })] })] })] }) }));
};
export default AddReminderModal;

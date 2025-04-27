import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Button } from '../../ui/Button/Button';
const ScheduleReminderModal = ({ isOpen, onClose, onSubmit, currentReminderDays, dueDate, isLoading, error, }) => {
    const [formData, setFormData] = useState({
        reminder_days: currentReminderDays,
        reminder_method: 'email',
        reminder_email: '',
        reminder_phone: '',
        additional_notes: '',
    });
    if (!isOpen)
        return null;
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: name === 'reminder_days' ? parseInt(value, 10) : value })));
    };
    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit(formData);
    };
    // Calculate reminder date based on due date and reminder days
    const calculateReminderDate = () => {
        if (!dueDate || formData.reminder_days <= 0)
            return 'Same day as due date';
        const dueDateObj = new Date(dueDate);
        const reminderDateObj = new Date(dueDateObj);
        reminderDateObj.setDate(dueDateObj.getDate() - formData.reminder_days);
        return reminderDateObj.toLocaleDateString();
    };
    return (_jsx("div", { className: "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4", children: _jsxs("div", { className: "bg-white rounded-lg shadow-xl w-full max-w-md", children: [_jsxs("div", { className: "flex justify-between items-center px-6 py-4 border-b border-gray-200", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Schedule Payment Reminder" }), _jsx("button", { onClick: onClose, className: "text-gray-400 hover:text-gray-500 focus:outline-none", children: _jsx("svg", { className: "h-6 w-6", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M6 18L18 6M6 6l12 12" }) }) })] }), _jsxs("form", { onSubmit: handleSubmit, className: "p-6", children: [error && (_jsx("div", { className: "mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded", children: error })), _jsxs("div", { className: "space-y-4", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "reminder_days", className: "block text-sm font-medium text-gray-700 mb-1", children: "Reminder (days before due date)" }), _jsx("input", { type: "number", id: "reminder_days", name: "reminder_days", value: formData.reminder_days, onChange: handleChange, min: "0", max: "30", className: "w-full rounded-md border border-gray-300 shadow-sm p-2", required: true }), _jsxs("p", { className: "mt-1 text-xs text-gray-500", children: ["You'll be reminded on: ", calculateReminderDate()] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "reminder_method", className: "block text-sm font-medium text-gray-700 mb-1", children: "Reminder Method" }), _jsxs("select", { id: "reminder_method", name: "reminder_method", value: formData.reminder_method, onChange: handleChange, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", required: true, children: [_jsx("option", { value: "email", children: "Email" }), _jsx("option", { value: "sms", children: "SMS" }), _jsx("option", { value: "both", children: "Both Email & SMS" })] })] }), (formData.reminder_method === 'email' || formData.reminder_method === 'both') && (_jsxs("div", { children: [_jsx("label", { htmlFor: "reminder_email", className: "block text-sm font-medium text-gray-700 mb-1", children: "Email Address" }), _jsx("input", { type: "email", id: "reminder_email", name: "reminder_email", value: formData.reminder_email, onChange: handleChange, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", placeholder: "your@email.com", required: formData.reminder_method === 'email' || formData.reminder_method === 'both' })] })), (formData.reminder_method === 'sms' || formData.reminder_method === 'both') && (_jsxs("div", { children: [_jsx("label", { htmlFor: "reminder_phone", className: "block text-sm font-medium text-gray-700 mb-1", children: "Phone Number" }), _jsx("input", { type: "tel", id: "reminder_phone", name: "reminder_phone", value: formData.reminder_phone, onChange: handleChange, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", placeholder: "+1 (555) 123-4567", required: formData.reminder_method === 'sms' || formData.reminder_method === 'both' })] })), _jsxs("div", { children: [_jsx("label", { htmlFor: "additional_notes", className: "block text-sm font-medium text-gray-700 mb-1", children: "Additional Notes (Optional)" }), _jsx("textarea", { id: "additional_notes", name: "additional_notes", value: formData.additional_notes, onChange: handleChange, rows: 3, className: "w-full rounded-md border border-gray-300 shadow-sm p-2", placeholder: "Any additional information for the reminder" })] })] }), _jsxs("div", { className: "mt-6 flex justify-end space-x-3", children: [_jsx(Button, { variant: "outlined", onClick: onClose, disabled: isLoading, children: "Cancel" }), _jsx(Button, { type: "submit", disabled: isLoading, children: isLoading ? 'Scheduling...' : 'Schedule Reminder' })] })] })] }) }));
};
export default ScheduleReminderModal;

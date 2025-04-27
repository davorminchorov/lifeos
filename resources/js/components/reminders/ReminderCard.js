import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Bell, CalendarClock, Edit, Trash } from 'lucide-react';
import { Card } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';
const ReminderCard = ({ reminders, title = 'Upcoming Reminders', onAddReminder, onEditReminder, onDeleteReminder, showEmptyState = true, }) => {
    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };
    const getStatusColor = (status) => {
        switch (status) {
            case 'scheduled':
                return 'bg-secondary-container text-on-secondary-container';
            case 'sent':
                return 'bg-tertiary-container text-on-tertiary-container';
            case 'cancelled':
                return 'bg-error-container text-on-error-container';
            default:
                return 'bg-surface-variant text-on-surface-variant';
        }
    };
    const getStatusLabel = (status) => {
        switch (status) {
            case 'scheduled':
                return 'Scheduled';
            case 'sent':
                return 'Sent';
            case 'cancelled':
                return 'Cancelled';
            default:
                return 'Unknown';
        }
    };
    // Sort reminders by date (scheduled reminders first, then by date)
    const sortedReminders = [...reminders].sort((a, b) => {
        // Put scheduled reminders first
        if (a.status === 'scheduled' && b.status !== 'scheduled')
            return -1;
        if (a.status !== 'scheduled' && b.status === 'scheduled')
            return 1;
        // Then sort by date (newest first)
        return new Date(b.reminder_date).getTime() - new Date(a.reminder_date).getTime();
    });
    return (_jsxs(Card, { className: "shadow-elevation-2 border border-outline/40", children: [_jsxs("div", { className: "px-6 py-4 border-b border-outline-variant/60 flex justify-between items-center", children: [_jsx("h3", { className: "text-headline-small font-medium text-on-surface", children: title }), onAddReminder && (_jsxs(Button, { onClick: onAddReminder, size: "sm", className: "bg-secondary text-on-secondary shadow-elevation-1 hover:shadow-elevation-2", children: [_jsx(Bell, { className: "h-4 w-4 mr-2" }), "Add Reminder"] }))] }), _jsx("div", { className: "p-6", children: reminders.length === 0 ? (showEmptyState ? (_jsxs("div", { className: "py-8 flex flex-col items-center justify-center text-center border-2 border-dashed border-outline/40 rounded-lg bg-surface-container", children: [_jsx(Bell, { className: "h-12 w-12 text-on-surface-variant/40 mb-4" }), _jsx("p", { className: "text-body-medium text-on-surface-variant mb-4", children: "No reminders scheduled" }), onAddReminder && (_jsx(Button, { onClick: onAddReminder, size: "sm", className: "bg-secondary text-on-secondary shadow-elevation-1 hover:shadow-elevation-2", children: "Schedule a Reminder" }))] })) : (_jsx("div", { className: "p-6 text-center text-body-medium text-on-surface-variant", children: "No reminders available." }))) : (_jsx("div", { className: "divide-y divide-outline/40", children: sortedReminders.map((reminder) => (_jsx("div", { className: "py-4 first:pt-0 last:pb-0", children: _jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { className: "space-y-1", children: [_jsxs("div", { className: "flex items-center space-x-2", children: [_jsx(CalendarClock, { className: "h-4 w-4 text-on-surface-variant" }), _jsx("p", { className: "text-body-large font-medium text-on-surface", children: formatDate(reminder.reminder_date) })] }), _jsx("p", { className: "text-body-medium text-on-surface-variant", children: reminder.reminder_message }), reminder.sent_at && (_jsxs("p", { className: "text-body-small text-on-surface-variant", children: ["Sent on ", formatDate(reminder.sent_at)] }))] }), _jsxs("div", { className: "flex flex-col items-end space-y-2", children: [_jsx("span", { className: `text-label-small px-3 py-1 rounded-full font-medium inline-block shadow-elevation-1 ${getStatusColor(reminder.status)}`, children: getStatusLabel(reminder.status) }), reminder.status === 'scheduled' && (_jsxs("div", { className: "flex space-x-1", children: [onEditReminder && (_jsx("button", { onClick: () => onEditReminder(reminder), className: "p-1 rounded-full hover:bg-surface-variant/20", "aria-label": "Edit reminder", children: _jsx(Edit, { className: "h-4 w-4 text-on-surface-variant" }) })), onDeleteReminder && (_jsx("button", { onClick: () => onDeleteReminder(reminder.id), className: "p-1 rounded-full hover:bg-surface-variant/20", "aria-label": "Delete reminder", children: _jsx(Trash, { className: "h-4 w-4 text-error" }) }))] }))] })] }) }, reminder.id))) })) })] }));
};
export default ReminderCard;

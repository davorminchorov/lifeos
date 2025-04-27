import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize Laravel Echo
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'your-pusher-key',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true
});

/**
 * Subscribe to expense events
 *
 * @param {Object} callbacks - Object containing callback functions
 * @param {Function} callbacks.onCreated - Called when an expense is created
 * @param {Function} callbacks.onUpdated - Called when an expense is updated
 * @param {Function} callbacks.onDeleted - Called when an expense is deleted
 * @returns {Object} - Subscription object with unsubscribe method
 */
export function subscribeToExpenseEvents({ onCreated, onUpdated, onDeleted }) {
    // Subscribe to the private expenses channel
    const channel = window.Echo.private('expenses');

    // Listen for expense events
    if (onCreated) {
        channel.listen('.expense.created', onCreated);
    }

    if (onUpdated) {
        channel.listen('.expense.updated', onUpdated);
    }

    if (onDeleted) {
        channel.listen('.expense.deleted', onDeleted);
    }

    // Return an object with unsubscribe method
    return {
        unsubscribe: () => {
            channel.stopListening('.expense.created');
            channel.stopListening('.expense.updated');
            channel.stopListening('.expense.deleted');
            window.Echo.leave('expenses');
        }
    };
}

/**
 * Subscribe to events for a specific expense
 *
 * @param {string} expenseId - ID of the expense to subscribe to
 * @param {Object} callbacks - Object containing callback functions
 * @param {Function} callbacks.onUpdated - Called when the expense is updated
 * @returns {Object} - Subscription object with unsubscribe method
 */
export function subscribeToSingleExpense(expenseId, { onUpdated }) {
    // Subscribe to the private expense-specific channel
    const channel = window.Echo.private(`expense.${expenseId}`);

    // Listen for expense update events
    if (onUpdated) {
        channel.listen('.expense.updated', onUpdated);
    }

    // Return an object with unsubscribe method
    return {
        unsubscribe: () => {
            channel.stopListening('.expense.updated');
            window.Echo.leave(`expense.${expenseId}`);
        }
    };
}

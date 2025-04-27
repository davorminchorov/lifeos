/**
 * Format a date string into a readable format
 * @param dateString - ISO date string or any valid date input
 * @returns Formatted date string (e.g., "April 27, 2025")
 */
export function formatDate(dateString) {
    if (!dateString)
        return '';
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        }).format(date);
    }
    catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
}
/**
 * Format a date string with time
 * @param dateString - ISO date string or any valid date input
 * @param timeString - Optional time string (HH:MM)
 * @returns Formatted date and time string (e.g., "April 27, 2025 at 14:30")
 */
export function formatDateTime(dateString, timeString) {
    if (!dateString)
        return '';
    try {
        let date;
        if (timeString) {
            // Combine date and time
            const [year, month, day] = dateString.split('-').map(Number);
            const [hours, minutes] = timeString.split(':').map(Number);
            date = new Date(year, month - 1, day, hours, minutes);
        }
        else {
            date = new Date(dateString);
        }
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true,
        }).format(date);
    }
    catch (error) {
        console.error('Error formatting date and time:', error);
        return timeString ? `${dateString} ${timeString}` : dateString;
    }
}

/**
 * Format a number as currency with the appropriate currency symbol
 * @param amount - The amount to format
 * @param currency - The currency code (e.g., 'USD', 'EUR')
 * @returns Formatted currency string
 */
export const formatCurrency = (amount: number, currency: string): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

/**
 * Format a date string to a more readable format
 * @param dateString - ISO date string
 * @returns Formatted date string (e.g., "Jan 1, 2023")
 */
export const formatDate = (dateString: string): string => {
  if (!dateString) return '';

  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

/**
 * Capitalize the first letter of a string
 * @param str - Input string
 * @returns String with first letter capitalized
 */
export const capitalize = (str: string): string => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
};

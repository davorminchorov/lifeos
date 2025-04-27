/**
 * Utility functions for formatting different types of data
 */

/**
 * Formats a number as currency with the specified currency code
 * @param value The number to format
 * @param currencyCode The ISO currency code (e.g., 'USD', 'EUR')
 * @returns Formatted currency string
 */
export function formatCurrency(value: number, currencyCode: string): string {
  if (value === undefined || value === null) {
    return '$0.00';
  }

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currencyCode,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

/**
 * Formats a date string or Date object into a human-readable string
 * @param dateValue Date string or Date object
 * @param options Optional Intl.DateTimeFormatOptions
 * @returns Formatted date string
 */
export function formatDate(
  dateValue: string | Date,
  options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }
): string {
  if (!dateValue) return '';

  const date = typeof dateValue === 'string' ? new Date(dateValue) : dateValue;

  return new Intl.DateTimeFormat('en-US', options).format(date);
}

/**
 * Formats a percentage value with the specified number of decimal places
 * @param value The number to format as percentage
 * @param decimalPlaces Number of decimal places to show
 * @returns Formatted percentage string
 */
export function formatPercentage(
  value: number,
  decimalPlaces: number = 2
): string {
  if (value === undefined || value === null) {
    return '0%';
  }

  return `${value.toFixed(decimalPlaces)}%`;
}

/**
 * Formats a number with thousands separators
 * @param value The number to format
 * @returns Formatted number string
 */
export function formatNumber(value: number): string {
  if (value === undefined || value === null) {
    return '0';
  }

  return new Intl.NumberFormat('en-US').format(value);
}

/**
 * Truncates a string to the specified length and adds an ellipsis
 * @param str The string to truncate
 * @param maxLength Maximum length before truncation
 * @returns Truncated string
 */
export function truncateString(str: string, maxLength: number = 30): string {
  if (!str) return '';

  if (str.length <= maxLength) return str;

  return `${str.substring(0, maxLength)}...`;
}

/**
 * Format a number as currency in a compact notation (e.g., $1.2K, $1.5M)
 * @param amount - The amount to format
 * @param currency - The currency code (e.g., 'USD', 'EUR')
 * @returns Formatted compact currency string
 */
export const formatCompactCurrency = (amount: number, currency: string): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    notation: 'compact',
    compactDisplay: 'short',
    minimumFractionDigits: 1,
    maximumFractionDigits: 1,
  }).format(amount);
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

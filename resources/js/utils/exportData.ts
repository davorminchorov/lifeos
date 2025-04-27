/**
 * Converts an array of objects to a CSV string
 * @param data Array of objects to convert to CSV
 * @param headers Optional custom headers for the CSV
 * @returns CSV formatted string
 */
export const objectsToCsv = <T extends Record<string, any>>(
  data: T[],
  headers?: Record<keyof T, string>
): string => {
  if (data.length === 0) {
    return '';
  }

  // Get keys from first object to determine CSV columns
  const keys = Object.keys(data[0]) as Array<keyof T>;

  // Build headers row
  let csvContent = '';
  if (headers) {
    csvContent = keys.map(key => `"${headers[key]}"`).join(',') + '\n';
  } else {
    csvContent = keys.map(key => `"${String(key)}"`).join(',') + '\n';
  }

  // Build data rows
  data.forEach(item => {
    const row = keys.map(key => {
      const value = item[key];
      // Handle different types of values
      if (value === null || value === undefined) {
        return '""';
      } else if (typeof value === 'string') {
        // Escape quotes in strings
        return `"${value.replace(/"/g, '""')}"`;
      } else if (typeof value === 'object') {
        // Convert objects to JSON strings
        return `"${JSON.stringify(value).replace(/"/g, '""')}"`;
      } else {
        return `"${value}"`;
      }
    }).join(',');
    csvContent += row + '\n';
  });

  return csvContent;
};

/**
 * Triggers a download of CSV data
 * @param csvContent CSV string content
 * @param filename Name of the file to download
 */
export const downloadCsv = (csvContent: string, filename: string): void => {
  // Create a blob with the CSV content
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

  // Create a download link
  const link = document.createElement('a');

  // Create a URL for the blob
  const url = URL.createObjectURL(blob);

  // Set link properties
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';

  // Add to document, trigger click, and remove
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

/**
 * Exports data to CSV and triggers download
 * @param data Array of objects to export
 * @param filename Name of the file to download
 * @param headers Optional custom headers for the CSV
 */
export const exportToCsv = <T extends Record<string, any>>(
  data: T[],
  filename: string,
  headers?: Record<keyof T, string>
): void => {
  const csvContent = objectsToCsv(data, headers);
  downloadCsv(csvContent, filename);
};

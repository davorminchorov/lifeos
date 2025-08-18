// Dashboard Charts Module
// Reusable chart components for Advanced Analytics Dashboard

// Note: jsPDF and html2canvas are dynamically imported in exportToPDF method

export class DashboardCharts {
    constructor() {
        this.charts = {};
        this.defaultColors = {
            primary: '#3B82F6',
            secondary: '#10B981',
            accent: '#8B5CF6',
            warning: '#F59E0B',
            danger: '#EF4444',
            info: '#06B6D4'
        };
    }

    // Initialize all charts on dashboard
    initializeCharts() {
        this.createSpendingTrendsChart();
        this.createCategoryBreakdownChart();
        this.createPortfolioPerformanceChart();
        this.createMonthlyComparisonChart();
    }

    // Spending Trends Line Chart
    createSpendingTrendsChart() {
        const ctx = document.getElementById('spendingTrendsChart');
        if (!ctx) return;

        // Get data from the page
        const chartData = JSON.parse(ctx.dataset.chartData || '[]');

        this.charts.spendingTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Monthly Spending',
                    data: chartData.spending || [],
                    borderColor: this.defaultColors.primary,
                    backgroundColor: this.defaultColors.primary + '20',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Budget',
                    data: chartData.budget || [],
                    borderColor: this.defaultColors.warning,
                    borderDash: [5, 5],
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Spending Trends (Last 6 Months)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'MKD ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Category Breakdown Doughnut Chart
    createCategoryBreakdownChart() {
        const ctx = document.getElementById('categoryBreakdownChart');
        if (!ctx) return;

        const chartData = JSON.parse(ctx.dataset.chartData || '{}');

        this.charts.categoryBreakdown = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    data: chartData.values || [],
                    backgroundColor: [
                        this.defaultColors.primary,
                        this.defaultColors.secondary,
                        this.defaultColors.accent,
                        this.defaultColors.warning,
                        this.defaultColors.danger,
                        this.defaultColors.info
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Spending by Category'
                    }
                }
            }
        });
    }

    // Portfolio Performance Chart
    createPortfolioPerformanceChart() {
        const ctx = document.getElementById('portfolioPerformanceChart');
        if (!ctx) return;

        const chartData = JSON.parse(ctx.dataset.chartData || '{}');

        this.charts.portfolioPerformance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Portfolio Value',
                    data: chartData.values || [],
                    backgroundColor: this.defaultColors.secondary + '80',
                    borderColor: this.defaultColors.secondary,
                    borderWidth: 1
                }, {
                    label: 'Returns',
                    data: chartData.returns || [],
                    backgroundColor: this.defaultColors.accent + '80',
                    borderColor: this.defaultColors.accent,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Investment Portfolio Performance'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'MKD ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Comparison Chart
    createMonthlyComparisonChart() {
        const ctx = document.getElementById('monthlyComparisonChart');
        if (!ctx) return;

        const chartData = JSON.parse(ctx.dataset.chartData || '{}');

        this.charts.monthlyComparison = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.categories || [],
                datasets: [{
                    label: 'Current Month',
                    data: chartData.current || [],
                    borderColor: this.defaultColors.primary,
                    backgroundColor: this.defaultColors.primary + '20',
                    pointBackgroundColor: this.defaultColors.primary
                }, {
                    label: 'Previous Month',
                    data: chartData.previous || [],
                    borderColor: this.defaultColors.secondary,
                    backgroundColor: this.defaultColors.secondary + '20',
                    pointBackgroundColor: this.defaultColors.secondary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Monthly Category Comparison'
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Update chart data dynamically
    updateChart(chartName, newData) {
        if (this.charts[chartName]) {
            this.charts[chartName].data = newData;
            this.charts[chartName].update();
        }
    }

    // Destroy all charts
    destroyCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts = {};
    }

    // Export dashboard as PDF
    async exportToPDF() {
        try {
            // Dynamic import of PDF libraries to reduce initial bundle size
            const [{ default: jsPDF }, { default: html2canvas }] = await Promise.all([
                import('jspdf'),
                import('html2canvas')
            ]);

            const dashboardElement = document.querySelector('[x-data="chartControls()"]');
            if (!dashboardElement) {
                throw new Error('Dashboard element not found');
            }

            // Create canvas from dashboard
            const canvas = await html2canvas(dashboardElement, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff'
            });

            // Create PDF
            const pdf = new jsPDF('l', 'mm', 'a4'); // Landscape orientation
            const imgWidth = 297; // A4 landscape width in mm
            const pageHeight = 210; // A4 landscape height in mm
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;

            const imgData = canvas.toDataURL('image/png');
            let position = 0;

            // Add title page
            pdf.setFontSize(20);
            pdf.text('LifeOS Analytics Dashboard Report', 20, 30);
            pdf.setFontSize(12);
            pdf.text(`Generated on: ${new Date().toLocaleDateString()}`, 20, 45);

            // Add chart image
            pdf.addImage(imgData, 'PNG', 0, 60, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            // Add new pages if needed
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            // Add summary statistics page
            pdf.addPage();
            pdf.setFontSize(16);
            pdf.text('Financial Summary', 20, 30);

            // Get current stats from the page
            const stats = this.getCurrentStats();
            let yPosition = 50;

            pdf.setFontSize(12);
            Object.entries(stats).forEach(([key, value]) => {
                pdf.text(`${key}: ${value}`, 20, yPosition);
                yPosition += 10;
            });

            // Save the PDF
            pdf.save(`lifeos-analytics-${new Date().toISOString().split('T')[0]}.pdf`);

            return true;
        } catch (error) {
            console.error('Error exporting to PDF:', error);
            alert('Error generating PDF report. Please try again.');
            return false;
        }
    }

    // Export dashboard data as Excel (CSV format)
    exportToExcel() {
        try {
            const data = this.prepareExportData();
            const csvContent = this.convertToCSV(data);

            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', `lifeos-analytics-${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            return true;
        } catch (error) {
            console.error('Error exporting to Excel:', error);
            alert('Error generating Excel report. Please try again.');
            return false;
        }
    }

    // Get current statistics from the dashboard
    getCurrentStats() {
        const stats = {};

        // Extract stats from the page
        const statElements = document.querySelectorAll('[class*="text-lg font-medium"]');
        statElements.forEach(element => {
            const label = element.previousElementSibling?.textContent || 'Unknown';
            const value = element.textContent || '0';
            if (label !== 'Unknown') {
                stats[label] = value;
            }
        });

        return stats;
    }

    // Prepare data for export
    prepareExportData() {
        const data = {
            summary: this.getCurrentStats(),
            chartData: {}
        };

        // Extract chart data
        Object.entries(this.charts).forEach(([chartName, chart]) => {
            if (chart && chart.data) {
                data.chartData[chartName] = {
                    labels: chart.data.labels || [],
                    datasets: chart.data.datasets?.map(dataset => ({
                        label: dataset.label,
                        data: dataset.data || []
                    })) || []
                };
            }
        });

        return data;
    }

    // Convert data to CSV format
    convertToCSV(data) {
        let csv = 'LifeOS Analytics Dashboard Export\n';
        csv += `Generated on: ${new Date().toLocaleString()}\n\n`;

        // Add summary section
        csv += 'FINANCIAL SUMMARY\n';
        csv += 'Metric,Value\n';
        Object.entries(data.summary).forEach(([key, value]) => {
            csv += `"${key}","${value}"\n`;
        });
        csv += '\n';

        // Add chart data sections
        Object.entries(data.chartData).forEach(([chartName, chartData]) => {
            csv += `${chartName.toUpperCase()} DATA\n`;

            if (chartData.labels && chartData.labels.length > 0) {
                // Create header row
                const headers = ['Period/Category', ...chartData.datasets.map(d => d.label)];
                csv += headers.map(h => `"${h}"`).join(',') + '\n';

                // Create data rows
                chartData.labels.forEach((label, index) => {
                    const row = [label];
                    chartData.datasets.forEach(dataset => {
                        row.push(dataset.data[index] || 0);
                    });
                    csv += row.map(cell => `"${cell}"`).join(',') + '\n';
                });
            }
            csv += '\n';
        });

        return csv;
    }
}

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardCharts = new DashboardCharts();
    window.dashboardCharts.initializeCharts();
});

// Alpine.js data for chart interactions
document.addEventListener('alpine:init', () => {
    Alpine.data('chartControls', () => ({
        selectedPeriod: '6months',
        selectedChart: 'spending',
        isExporting: false,

        changePeriod(period) {
            this.selectedPeriod = period;
            this.refreshCharts();
        },

        changeChart(chartType) {
            this.selectedChart = chartType;
            this.showChart(chartType);
        },

        refreshCharts() {
            // Fetch new data and update charts
            fetch(`/dashboard/chart-data?period=${this.selectedPeriod}`)
                .then(response => response.json())
                .then(data => {
                    if (window.dashboardCharts) {
                        window.dashboardCharts.updateChart('spendingTrends', data.spendingTrends);
                        window.dashboardCharts.updateChart('categoryBreakdown', data.categoryBreakdown);
                        window.dashboardCharts.updateChart('portfolioPerformance', data.portfolioPerformance);
                        window.dashboardCharts.updateChart('monthlyComparison', data.monthlyComparison);
                    }
                })
                .catch(error => console.error('Error fetching chart data:', error));
        },

        showChart(chartType) {
            // Show/hide different chart sections
            const charts = document.querySelectorAll('.chart-container');
            charts.forEach(chart => {
                chart.style.display = chart.dataset.chartType === chartType ? 'block' : 'none';
            });
        },

        async exportData(format) {
            if (this.isExporting) return;

            this.isExporting = true;

            try {
                let success = false;

                if (format === 'pdf') {
                    success = await window.dashboardCharts.exportToPDF();
                } else if (format === 'excel') {
                    success = window.dashboardCharts.exportToExcel();
                }

                if (success) {
                    // Show success message
                    this.showNotification('Export completed successfully!', 'success');
                } else {
                    this.showNotification('Export failed. Please try again.', 'error');
                }
            } catch (error) {
                console.error('Export error:', error);
                this.showNotification('Export failed. Please try again.', 'error');
            } finally {
                this.isExporting = false;
            }
        },

        showNotification(message, type) {
            // Create a simple notification
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 transition-opacity duration-300 ${
                type === 'success'
                    ? 'bg-green-100 text-green-800 border border-green-200'
                    : 'bg-red-100 text-red-800 border border-red-200'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    }));
});

/**
 * Dashboard Charts Module
 * Handles all Chart.js initialization and data management
 */

class DashboardCharts {
    constructor() {
        this.charts = {};
        this.chartData = window.chartData || {};
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeCharts();
        });
    }

    initializeCharts() {
        try {
            // Check if Chart.js is available
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js is not loaded. Charts will not be initialized.');
                return;
            }

            this.initSpendingTrendsChart();
            this.initCategoryBreakdownChart();
            this.initPortfolioPerformanceChart();
            this.initMonthlyComparisonChart();
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    }

    initSpendingTrendsChart() {
        const canvas = document.getElementById('spendingTrendsChart');
        if (!canvas || !this.chartData.spendingTrends) return;

        const ctx = canvas.getContext('2d');
        this.charts.spendingTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.chartData.spendingTrends.labels || [],
                datasets: [
                    {
                        label: 'Spending',
                        data: this.chartData.spendingTrends.spending || [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Budget',
                        data: this.chartData.spendingTrends.budget || [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    initCategoryBreakdownChart() {
        const canvas = document.getElementById('categoryBreakdownChart');
        if (!canvas || !this.chartData.categoryBreakdown) return;

        const ctx = canvas.getContext('2d');
        this.charts.categoryBreakdown = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: this.chartData.categoryBreakdown.labels || [],
                datasets: [{
                    data: this.chartData.categoryBreakdown.values || [],
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(107, 114, 128)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    initPortfolioPerformanceChart() {
        const canvas = document.getElementById('portfolioPerformanceChart');
        if (!canvas || !this.chartData.portfolioPerformance) return;

        const ctx = canvas.getContext('2d');
        this.charts.portfolioPerformance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.chartData.portfolioPerformance.labels || [],
                datasets: [
                    {
                        label: 'Portfolio Value',
                        data: this.chartData.portfolioPerformance.values || [],
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    },
                    {
                        label: 'Returns',
                        data: this.chartData.portfolioPerformance.returns || [],
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    initMonthlyComparisonChart() {
        const canvas = document.getElementById('monthlyComparisonChart');
        if (!canvas || !this.chartData.monthlyComparison) return;

        const ctx = canvas.getContext('2d');
        this.charts.monthlyComparison = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: this.chartData.monthlyComparison.categories || [],
                datasets: [
                    {
                        label: 'Current Month',
                        data: this.chartData.monthlyComparison.current || [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(59, 130, 246)'
                    },
                    {
                        label: 'Previous Month',
                        data: this.chartData.monthlyComparison.previous || [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        pointBackgroundColor: 'rgb(239, 68, 68)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(239, 68, 68)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                elements: {
                    line: {
                        borderWidth: 3
                    }
                }
            }
        });
    }

    updateChartData(newData) {
        this.chartData = { ...this.chartData, ...newData };
        this.refreshCharts();
    }

    refreshCharts() {
        Object.keys(this.charts).forEach(chartKey => {
            if (this.charts[chartKey]) {
                this.charts[chartKey].destroy();
            }
        });
        this.charts = {};
        this.initializeCharts();
    }

    exportChart(chartId, format = 'png') {
        const chart = this.charts[chartId];
        if (!chart) return null;

        return chart.toBase64Image();
    }
}

// Alpine.js component for chart controls
function chartControls() {
    return {
        selectedPeriod: '6months',
        isExporting: false,

        changePeriod(period) {
            this.selectedPeriod = period;
            // Here you could make an AJAX request to get new data
            console.log('Period changed to:', period);
        },

        async exportData(format) {
            this.isExporting = true;

            try {
                if (format === 'pdf') {
                    await this.exportToPDF();
                } else if (format === 'excel') {
                    await this.exportToExcel();
                }
            } catch (error) {
                console.error('Export failed:', error);
            } finally {
                this.isExporting = false;
            }
        },

        async exportToPDF() {
            // Implementation for PDF export using jsPDF
            if (typeof jsPDF === 'undefined') {
                console.error('jsPDF library not loaded');
                return;
            }

            console.log('Exporting to PDF...');
            // Add PDF export logic here
        },

        async exportToExcel() {
            // Implementation for Excel export
            console.log('Exporting to Excel...');
            // Add Excel export logic here
        }
    };
}

// Initialize charts when the script loads
const dashboardCharts = new DashboardCharts();

// Make functions globally available
window.chartControls = chartControls;
window.dashboardCharts = dashboardCharts;

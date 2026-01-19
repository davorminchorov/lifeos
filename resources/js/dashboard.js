/**
 * Dashboard JavaScript Module
 * Handles chart initialization and data fetching via API calls
 * No inline PHP variables - pure JavaScript approach
 */

import Alpine from 'alpinejs';

class DashboardManager {
    constructor() {
        this.charts = {};
        this.apiEndpoint = '/dashboard/chart-data';
        this.currentPeriod = '6months';
        this.isLoading = false;
        this.init();
    }

    async init() {
        await this.loadChartData();
        this.initializeCharts();
    }

    async loadChartData(period = null) {
        if (period) {
            this.currentPeriod = period;
        }

        this.isLoading = true;
        this.showLoadingState();

        try {
            const url = new URL(this.apiEndpoint, window.location.origin);
            url.searchParams.append('period', this.currentPeriod);

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            this.chartData = await response.json();
        } catch (error) {
            console.error('Failed to load chart data:', error);
            this.showErrorState(error.message);
            this.chartData = this.getDefaultChartData();
        } finally {
            this.isLoading = false;
            this.hideLoadingState();
        }
    }

    showLoadingState() {
        document.querySelectorAll('.chart-container').forEach(container => {
            container.classList.add('loading');
            const canvas = container.querySelector('canvas');
            if (canvas) {
                canvas.style.opacity = '0.3';
            }
        });
    }

    hideLoadingState() {
        document.querySelectorAll('.chart-container').forEach(container => {
            container.classList.remove('loading');
            const canvas = container.querySelector('canvas');
            if (canvas) {
                canvas.style.opacity = '1';
            }
        });
    }

    showErrorState(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'dashboard-error bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.textContent = `Failed to load dashboard data: ${message}`;

        const dashboard = document.querySelector('[x-data="chartControls()"]');
        if (dashboard) {
            const existing = dashboard.querySelector('.dashboard-error');
            if (existing) {
                existing.remove();
            }
            dashboard.insertBefore(errorDiv, dashboard.firstChild);

            setTimeout(() => errorDiv.remove(), 5000);
        }
    }

    getDefaultChartData() {
        return {
            spendingTrends: {
                labels: [],
                spending: [],
                budget: []
            },
            categoryBreakdown: {
                labels: ["Subscriptions", "Utilities", "Food", "Transport", "Entertainment", "Other"],
                values: [0, 0, 0, 0, 0, 0]
            },
            portfolioPerformance: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
                values: [0, 0, 0, 0, 0, 0],
                returns: [0, 0, 0, 0, 0, 0]
            },
            monthlyComparison: {
                categories: ["Subscriptions", "Utilities", "Food", "Transport", "Entertainment"],
                current: [0, 0, 0, 0, 0],
                previous: [0, 0, 0, 0, 0]
            }
        };
    }

    initializeCharts() {
        // Only initialize if Chart.js is available
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded. Charts will not be initialized.');
            return;
        }

        this.initSpendingTrendsChart();
        this.initCategoryBreakdownChart();
        this.initPortfolioPerformanceChart();
        this.initMonthlyComparisonChart();
    }

    initSpendingTrendsChart() {
        const canvas = document.getElementById('spendingTrendsChart');
        if (!canvas) return;

        // Destroy existing chart if it exists
        if (this.charts.spendingTrends) {
            this.charts.spendingTrends.destroy();
        }

        // Also check for any existing Chart instance on this canvas
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }

        const ctx = canvas.getContext('2d');
        this.charts.spendingTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.chartData.spendingTrends.labels,
                datasets: [
                    {
                        label: 'Spending',
                        data: this.chartData.spendingTrends.spending,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Budget',
                        data: this.chartData.spendingTrends.budget,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    initCategoryBreakdownChart() {
        const canvas = document.getElementById('categoryBreakdownChart');
        if (!canvas) return;

        // Destroy existing chart if it exists
        if (this.charts.categoryBreakdown) {
            this.charts.categoryBreakdown.destroy();
        }

        // Also check for any existing Chart instance on this canvas
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }

        const ctx = canvas.getContext('2d');
        this.charts.categoryBreakdown = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: this.chartData.categoryBreakdown.labels,
                datasets: [{
                    data: this.chartData.categoryBreakdown.values,
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
                maintainAspectRatio: false
            }
        });
    }

    initPortfolioPerformanceChart() {
        const canvas = document.getElementById('portfolioPerformanceChart');
        if (!canvas) return;

        // Destroy existing chart if it exists
        if (this.charts.portfolioPerformance) {
            this.charts.portfolioPerformance.destroy();
        }

        // Also check for any existing Chart instance on this canvas
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }

        const ctx = canvas.getContext('2d');
        this.charts.portfolioPerformance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.chartData.portfolioPerformance.labels,
                datasets: [
                    {
                        label: 'Portfolio Value',
                        data: this.chartData.portfolioPerformance.values,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    },
                    {
                        label: 'Returns',
                        data: this.chartData.portfolioPerformance.returns,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    initMonthlyComparisonChart() {
        const canvas = document.getElementById('monthlyComparisonChart');
        if (!canvas) return;

        // Destroy existing chart if it exists
        if (this.charts.monthlyComparison) {
            this.charts.monthlyComparison.destroy();
        }

        // Also check for any existing Chart instance on this canvas
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }

        const ctx = canvas.getContext('2d');
        this.charts.monthlyComparison = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: this.chartData.monthlyComparison.categories,
                datasets: [
                    {
                        label: 'Current Month',
                        data: this.chartData.monthlyComparison.current,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)'
                    },
                    {
                        label: 'Previous Month',
                        data: this.chartData.monthlyComparison.previous,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    async refreshCharts(period = null) {
        await this.loadChartData(period);
        Object.values(this.charts).forEach(chart => chart.destroy());
        this.charts = {};
        this.initializeCharts();
    }

    async updateChartData() {
        if (this.isLoading) return;

        Object.keys(this.charts).forEach(chartKey => {
            const chart = this.charts[chartKey];
            if (!chart) return;

            switch (chartKey) {
                case 'spendingTrends':
                    chart.data.labels = this.chartData.spendingTrends.labels;
                    chart.data.datasets[0].data = this.chartData.spendingTrends.spending;
                    chart.data.datasets[1].data = this.chartData.spendingTrends.budget;
                    break;
                case 'categoryBreakdown':
                    chart.data.labels = this.chartData.categoryBreakdown.labels;
                    chart.data.datasets[0].data = this.chartData.categoryBreakdown.values;
                    break;
                case 'portfolioPerformance':
                    chart.data.labels = this.chartData.portfolioPerformance.labels;
                    chart.data.datasets[0].data = this.chartData.portfolioPerformance.values;
                    chart.data.datasets[1].data = this.chartData.portfolioPerformance.returns;
                    break;
                case 'monthlyComparison':
                    chart.data.labels = this.chartData.monthlyComparison.categories;
                    chart.data.datasets[0].data = this.chartData.monthlyComparison.current;
                    chart.data.datasets[1].data = this.chartData.monthlyComparison.previous;
                    break;
            }
            chart.update();
        });
    }
}

// Alpine.js components for interactive elements
document.addEventListener('alpine:init', () => {
    Alpine.data('chartControls', () => ({
        selectedPeriod: '6months',
        isExporting: false,
        isRefreshing: false,

        async changePeriod(period) {
            this.selectedPeriod = period;
            if (window.dashboardManager) {
                await window.dashboardManager.refreshCharts(period);
            }
        },

        async refreshData() {
            if (this.isRefreshing) return;

            this.isRefreshing = true;
            try {
                if (window.dashboardManager) {
                    await window.dashboardManager.loadChartData(this.selectedPeriod);
                    await window.dashboardManager.updateChartData();
                }
            } catch (error) {
                console.error('Refresh failed:', error);
            } finally {
                this.isRefreshing = false;
            }
        },

        async exportData(format) {
            this.isExporting = true;
            try {
                if (!window.dashboardManager || !window.dashboardManager.chartData) {
                    throw new Error('No data available to export');
                }

                if (format === 'excel') {
                    this.exportToCSV();
                } else if (format === 'pdf') {
                    alert('PDF export coming soon! For now, please use CSV export.');
                }
            } catch (error) {
                console.error('Export failed:', error);
                alert(`Export failed: ${error.message}`);
            } finally {
                this.isExporting = false;
            }
        },

        exportToCSV() {
            const data = window.dashboardManager.chartData;
            let csv = 'Dashboard Data Export\n\n';

            // Spending Trends
            csv += 'Spending Trends\n';
            csv += 'Month,Spending,Budget\n';
            data.spendingTrends.labels.forEach((label, i) => {
                csv += `${label},${data.spendingTrends.spending[i]},${data.spendingTrends.budget[i]}\n`;
            });
            csv += '\n';

            // Category Breakdown
            csv += 'Category Breakdown\n';
            csv += 'Category,Amount\n';
            data.categoryBreakdown.labels.forEach((label, i) => {
                csv += `${label},${data.categoryBreakdown.values[i]}\n`;
            });
            csv += '\n';

            // Portfolio Performance
            csv += 'Portfolio Performance\n';
            csv += 'Month,Value,Returns\n';
            data.portfolioPerformance.labels.forEach((label, i) => {
                csv += `${label},${data.portfolioPerformance.values[i]},${data.portfolioPerformance.returns[i]}\n`;
            });
            csv += '\n';

            // Monthly Comparison
            csv += 'Monthly Comparison\n';
            csv += 'Category,Current Month,Previous Month\n';
            data.monthlyComparison.categories.forEach((label, i) => {
                csv += `${label},${data.monthlyComparison.current[i]},${data.monthlyComparison.previous[i]}\n`;
            });

            // Download
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `dashboard-export-${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }));
});

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});

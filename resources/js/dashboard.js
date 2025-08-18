/**
 * Dashboard JavaScript Module
 * Handles chart initialization and data fetching via API calls
 * No inline PHP variables - pure JavaScript approach
 */

import Alpine from 'alpinejs';

class DashboardManager {
    constructor() {
        this.charts = {};
        this.apiEndpoint = '/api/dashboard/chart-data';
        this.init();
    }

    async init() {
        await this.loadChartData();
        this.initializeCharts();
    }

    async loadChartData() {
        try {
            const response = await fetch(this.apiEndpoint, {
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
            this.chartData = this.getDefaultChartData();
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

    async refreshCharts() {
        await this.loadChartData();
        Object.values(this.charts).forEach(chart => chart.destroy());
        this.charts = {};
        this.initializeCharts();
    }
}

// Alpine.js components for interactive elements
document.addEventListener('alpine:init', () => {
    Alpine.data('chartControls', () => ({
        selectedPeriod: '6months',
        isExporting: false,

        async changePeriod(period) {
            this.selectedPeriod = period;
            if (window.dashboardManager) {
                await window.dashboardManager.refreshCharts();
            }
        },

        async exportData(format) {
            this.isExporting = true;
            try {
                // Export logic would go here
                console.log(`Exporting dashboard data as ${format}`);
                await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate export
            } catch (error) {
                console.error('Export failed:', error);
            } finally {
                this.isExporting = false;
            }
        }
    }));
});

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});

import React, { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { useToast } from '../../ui/Toast';
import { MonthlySpendingChart } from '../../components/expenses/MonthlySpendingChart';
import { CategoryDistributionChart } from '../../components/expenses/CategoryDistributionChart';
import { BudgetStatusCard } from '../../components/expenses/BudgetStatusCard';
import { useMonthlySummary, useBudgetStatus, useExpenseCategories } from '../../queries/expenseQueries';

interface DateRangeFilter {
  startDate: string;
  endDate: string;
}

const ExpenseAnalytics: React.FC = () => {
  const { toast } = useToast();
  const [dateRange, setDateRange] = useState<DateRangeFilter>({
    startDate: new Date(new Date().getFullYear(), new Date().getMonth() - 5, 1).toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0]
  });

  const [selectedCategories, setSelectedCategories] = useState<string[]>([]);

  // Fetch data using React Query
  const {
    data: monthlySummary,
    isLoading: isLoadingMonthlySummary,
    error: monthlySummaryError
  } = useMonthlySummary();

  const {
    data: budgetStatus,
    isLoading: isLoadingBudgetStatus,
    error: budgetStatusError
  } = useBudgetStatus();

  const {
    data: categories,
    isLoading: isLoadingCategories,
    error: categoriesError
  } = useExpenseCategories();

  const handleDateRangeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setDateRange(prev => ({ ...prev, [name]: value }));
  };

  const handleCategoryToggle = (categoryId: string) => {
    setSelectedCategories(prev =>
      prev.includes(categoryId)
        ? prev.filter(id => id !== categoryId)
        : [...prev, categoryId]
    );
  };

  const exportAnalyticsReport = async () => {
    try {
      const response = await fetch('/api/expenses/export/analytics', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          start_date: dateRange.startDate,
          end_date: dateRange.endDate,
          categories: selectedCategories.length > 0 ? selectedCategories : undefined
        }),
      });

      if (!response.ok) {
        throw new Error('Failed to export report');
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `expense-analytics-${new Date().toISOString().slice(0, 10)}.pdf`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);

      toast({
        title: 'Success',
        description: 'Analytics report exported successfully',
        variant: 'success',
      });
    } catch (error) {
      console.error('Export error:', error);
      toast({
        title: 'Error',
        description: 'Failed to export analytics report',
        variant: 'destructive',
      });
    }
  };

  const isLoading = isLoadingMonthlySummary || isLoadingBudgetStatus || isLoadingCategories;
  const hasError = monthlySummaryError || budgetStatusError || categoriesError;

  if (isLoading) {
    return (
      <div className="container mx-auto p-4">
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>
    );
  }

  if (hasError) {
    return (
      <div className="container mx-auto p-4">
        <div className="bg-error-container border border-error text-on-error-container p-4 rounded-lg">
          <h3 className="text-xl font-semibold mb-2">Error Loading Analytics</h3>
          <p>There was a problem loading the expense analytics data. Please try again later.</p>
          <Button
            variant="filled"
            className="mt-4"
            onClick={() => window.location.reload()}
          >
            Retry
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-4">
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-on-surface mb-2">Expense Analytics</h1>
        <p className="text-on-surface-variant">
          Visualize and analyze your spending patterns to make informed financial decisions.
        </p>
      </div>

      {/* Filters */}
      <Card variant="outlined" className="mb-6">
        <CardContent className="p-4">
          <div className="flex flex-col md:flex-row md:items-center gap-4">
            <div className="flex-1">
              <label htmlFor="startDate" className="block mb-1 text-sm font-medium text-on-surface">
                Start Date
              </label>
              <input
                type="date"
                id="startDate"
                name="startDate"
                value={dateRange.startDate}
                onChange={handleDateRangeChange}
                className="w-full border border-outline rounded-md px-3 py-2 bg-surface"
              />
            </div>
            <div className="flex-1">
              <label htmlFor="endDate" className="block mb-1 text-sm font-medium text-on-surface">
                End Date
              </label>
              <input
                type="date"
                id="endDate"
                name="endDate"
                value={dateRange.endDate}
                onChange={handleDateRangeChange}
                className="w-full border border-outline rounded-md px-3 py-2 bg-surface"
              />
            </div>
            <div className="flex-1 flex items-end">
              <Button
                variant="filled"
                onClick={exportAnalyticsReport}
                className="w-full md:w-auto"
              >
                Export Report
              </Button>
            </div>
          </div>

          {categories && categories.length > 0 && (
            <div className="mt-4">
              <p className="text-sm font-medium text-on-surface mb-2">Filter by Categories</p>
              <div className="flex flex-wrap gap-2">
                {categories.map(category => (
                  <button
                    key={category.id}
                    onClick={() => handleCategoryToggle(category.id)}
                    className={`px-3 py-1 text-sm rounded-full ${
                      selectedCategories.includes(category.id)
                        ? 'bg-primary text-on-primary'
                        : 'bg-surface-variant text-on-surface-variant'
                    }`}
                  >
                    {category.name}
                  </button>
                ))}
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Analytics Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <Card variant="elevated">
          <CardHeader>
            <CardTitle>Monthly Spending Trend</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-80">
              {monthlySummary ? (
                <MonthlySpendingChart data={monthlySummary} />
              ) : (
                <div className="flex justify-center items-center h-full">
                  <p className="text-on-surface-variant">No monthly spending data available</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        <Card variant="elevated">
          <CardHeader>
            <CardTitle>Spending by Category</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-80">
              {categories && monthlySummary ? (
                <CategoryDistributionChart
                  categories={categories}
                  data={monthlySummary}
                  selectedCategories={selectedCategories}
                />
              ) : (
                <div className="flex justify-center items-center h-full">
                  <p className="text-on-surface-variant">No category distribution data available</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>

      <Card variant="elevated" className="mb-6">
        <CardHeader>
          <CardTitle>Budget Status</CardTitle>
        </CardHeader>
        <CardContent>
          {budgetStatus ? (
            <BudgetStatusCard budgets={budgetStatus} />
          ) : (
            <div className="py-8 text-center">
              <p className="text-on-surface-variant">No budget data available</p>
              <Button
                variant="text"
                className="mt-2"
                onClick={() => window.location.href = '/expenses/budgets/create'}
              >
                Create Budget
              </Button>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Insights Card */}
      <Card variant="elevated">
        <CardHeader>
          <CardTitle>Spending Insights</CardTitle>
        </CardHeader>
        <CardContent>
          {monthlySummary && monthlySummary.length > 0 ? (
            <div className="space-y-4">
              {/* Calculate insights from the data */}
              {(() => {
                // Calculate month-over-month growth
                const sortedMonths = [...monthlySummary].sort((a, b) =>
                  new Date(a.year_month).getTime() - new Date(b.year_month).getTime()
                );

                const currentMonth = sortedMonths[sortedMonths.length - 1];
                const previousMonth = sortedMonths[sortedMonths.length - 2];

                const percentChange = previousMonth
                  ? ((currentMonth.total_amount - previousMonth.total_amount) / previousMonth.total_amount) * 100
                  : 0;

                // Find highest spending month
                let highestMonth = sortedMonths[0];
                sortedMonths.forEach(month => {
                  if (month.total_amount > highestMonth.total_amount) {
                    highestMonth = month;
                  }
                });

                // Find lowest spending month
                let lowestMonth = sortedMonths[0];
                sortedMonths.forEach(month => {
                  if (month.total_amount < lowestMonth.total_amount) {
                    lowestMonth = month;
                  }
                });

                return (
                  <>
                    <div>
                      <h3 className="text-lg font-semibold mb-1">Month-over-Month Change</h3>
                      <p className={`${percentChange > 0 ? 'text-error' : 'text-tertiary'}`}>
                        {percentChange > 0 ? '↑' : '↓'} {Math.abs(percentChange).toFixed(1)}% compared to previous month
                      </p>
                    </div>

                    <div>
                      <h3 className="text-lg font-semibold mb-1">Highest Spending Month</h3>
                      <p>
                        {new Date(highestMonth.year_month).toLocaleString('default', { month: 'long', year: 'numeric' })}
                        <span className="ml-2">
                          (${highestMonth.total_amount.toFixed(2)})
                        </span>
                      </p>
                    </div>

                    <div>
                      <h3 className="text-lg font-semibold mb-1">Lowest Spending Month</h3>
                      <p>
                        {new Date(lowestMonth.year_month).toLocaleString('default', { month: 'long', year: 'numeric' })}
                        <span className="ml-2">
                          (${lowestMonth.total_amount.toFixed(2)})
                        </span>
                      </p>
                    </div>
                  </>
                );
              })()}
            </div>
          ) : (
            <div className="py-8 text-center">
              <p className="text-on-surface-variant">Not enough data to generate insights</p>
              <p className="text-sm text-on-surface-variant mt-1">
                Continue tracking your expenses to see spending patterns and insights
              </p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default ExpenseAnalytics;

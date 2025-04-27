import React from 'react';
import { MonthlySpendingChart } from './MonthlySpendingChart';
import { CategoryDistributionChart } from './CategoryDistributionChart';

export const MonthlySummaryCard: React.FC = () => {
  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-title-medium font-medium text-on-surface mb-3">Monthly Spending Trend</h3>
        <MonthlySpendingChart months={6} />
      </div>

      <div>
        <h3 className="text-title-medium font-medium text-on-surface mb-3">Spending by Category</h3>
        <CategoryDistributionChart />
      </div>
    </div>
  );
};

import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { MonthlySpendingChart } from './MonthlySpendingChart';
import { CategoryDistributionChart } from './CategoryDistributionChart';
export const MonthlySummaryCard = () => {
    return (_jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsx("h3", { className: "text-title-medium font-medium text-on-surface mb-3", children: "Monthly Spending Trend" }), _jsx(MonthlySpendingChart, { months: 6 })] }), _jsxs("div", { children: [_jsx("h3", { className: "text-title-medium font-medium text-on-surface mb-3", children: "Spending by Category" }), _jsx(CategoryDistributionChart, {})] })] }));
};

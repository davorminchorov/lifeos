import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Card, CardContent, CardHeader, CardTitle } from '../../ui/Card';
import { PieChart } from 'react-minimal-pie-chart';
import { formatCurrency } from '../../utils/format';
const typeColors = {
    stock: '#4F46E5', // indigo-600
    bond: '#10B981', // emerald-500
    mutual_fund: '#8B5CF6', // purple-500
    etf: '#F59E0B', // amber-500
    real_estate: '#EF4444', // red-500
    retirement: '#3B82F6', // blue-500
    life_insurance: '#14B8A6', // teal-500
    crypto: '#EC4899', // pink-500
    other: '#6B7280', // gray-500
};
const typeLabels = {
    stock: 'Stocks',
    bond: 'Bonds',
    mutual_fund: 'Mutual Funds',
    etf: 'ETFs',
    real_estate: 'Real Estate',
    retirement: 'Retirement Accounts',
    life_insurance: 'Life Insurance',
    crypto: 'Cryptocurrency',
    other: 'Other Investments',
};
const PortfolioAnalytics = ({ data, className = '' }) => {
    if (!data)
        return null;
    // Sort types by value (highest first) for the chart
    const sortedTypes = Object.entries(data.by_type || {}).sort((a, b) => b[1].value - a[1].value);
    // Prepare pie chart data
    const chartData = sortedTypes.map(([type, info]) => ({
        title: typeLabels[type] || type,
        value: info.value,
        color: typeColors[type] || typeColors.other,
        percentage: info.percentage
    }));
    // Calculate value difference and percent change
    const valueDifference = data.total_current_value - data.total_invested;
    const isPositiveReturn = valueDifference >= 0;
    return (_jsx("div", { className: className, children: _jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-3 gap-6", children: [_jsxs(Card, { className: "col-span-1 bg-white shadow-sm rounded-xl overflow-hidden h-full", children: [_jsx(CardHeader, { className: "bg-gray-50 border-b border-gray-100 px-6 py-4", children: _jsx(CardTitle, { className: "text-lg font-semibold text-gray-800", children: "Performance Summary" }) }), _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsxs("div", { className: "flex justify-between items-center mb-2", children: [_jsx("span", { className: "text-sm font-medium text-gray-500", children: "Total Invested" }), _jsx("span", { className: "text-sm font-bold text-gray-900", children: formatCurrency(data.total_invested, 'USD') })] }), _jsxs("div", { className: "flex justify-between items-center mb-2", children: [_jsx("span", { className: "text-sm font-medium text-gray-500", children: "Current Value" }), _jsx("span", { className: "text-sm font-bold text-gray-900", children: formatCurrency(data.total_current_value, 'USD') })] }), _jsxs("div", { className: "flex justify-between items-center mb-2", children: [_jsx("span", { className: "text-sm font-medium text-gray-500", children: "Value Change" }), _jsxs("span", { className: `text-sm font-bold ${isPositiveReturn ? 'text-green-600' : 'text-red-600'}`, children: [isPositiveReturn ? '+' : '', formatCurrency(valueDifference, 'USD')] })] }), _jsxs("div", { className: "flex justify-between items-center", children: [_jsx("span", { className: "text-sm font-medium text-gray-500", children: "Return on Investment" }), _jsxs("span", { className: `text-sm font-bold ${data.overall_roi >= 0 ? 'text-green-600' : 'text-red-600'}`, children: [data.overall_roi >= 0 ? '+' : '', data.overall_roi.toFixed(2), "%"] })] })] }), _jsx("div", { className: "h-1 w-full bg-gray-100 rounded-full overflow-hidden", children: _jsx("div", { className: `h-full ${isPositiveReturn ? 'bg-green-500' : 'bg-red-500'}`, style: { width: `${Math.min(Math.abs(data.overall_roi), 100)}%` } }) }), _jsxs("div", { className: "pt-4 border-t border-gray-100", children: [_jsx("h4", { className: "text-sm font-medium text-gray-700 mb-3", children: "Portfolio Diversity" }), _jsxs("div", { className: "space-y-1.5", children: [sortedTypes.slice(0, 5).map(([type, info]) => (_jsxs("div", { className: "flex justify-between items-center", children: [_jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: "w-3 h-3 rounded-full mr-2", style: { backgroundColor: typeColors[type] || typeColors.other } }), _jsx("span", { className: "text-xs text-gray-600", children: typeLabels[type] || type })] }), _jsxs("span", { className: "text-xs font-medium text-gray-900", children: [info.percentage.toFixed(1), "%"] })] }, type))), sortedTypes.length > 5 && (_jsxs("div", { className: "text-xs text-gray-500 italic mt-1", children: ["And ", sortedTypes.length - 5, " more types..."] }))] })] })] }) })] }), _jsxs(Card, { className: "col-span-1 lg:col-span-2 bg-white shadow-sm rounded-xl overflow-hidden", children: [_jsx(CardHeader, { className: "bg-gray-50 border-b border-gray-100 px-6 py-4", children: _jsx(CardTitle, { className: "text-lg font-semibold text-gray-800", children: "Portfolio Allocation" }) }), _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "flex flex-col lg:flex-row items-center", children: [_jsx("div", { className: "w-48 h-48 mx-auto mb-6 lg:mb-0 lg:mx-0", children: _jsx(PieChart, { data: chartData, lineWidth: 35, paddingAngle: 3, rounded: true, label: ({ dataEntry }) => dataEntry.percentage > 5 ? `${dataEntry.percentage.toFixed(0)}%` : '', labelStyle: {
                                                fontSize: '8px',
                                                fontFamily: 'sans-serif',
                                                fill: '#fff',
                                            }, labelPosition: 70 }) }), _jsx("div", { className: "flex-1 lg:ml-8 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2", children: chartData.map((item, index) => (_jsxs("div", { className: "flex items-center space-x-2", children: [_jsx("div", { className: "w-3 h-3 rounded-full", style: { backgroundColor: item.color } }), _jsxs("div", { className: "text-sm", children: [_jsx("span", { className: "font-medium text-gray-900", children: item.title }), _jsxs("div", { className: "flex space-x-2 text-xs text-gray-500", children: [_jsx("span", { children: formatCurrency(item.value, 'USD') }), _jsx("span", { children: "\u00B7" }), _jsxs("span", { children: [item.percentage.toFixed(1), "%"] })] })] })] }, index))) })] }) })] })] }) }));
};
export default PortfolioAnalytics;

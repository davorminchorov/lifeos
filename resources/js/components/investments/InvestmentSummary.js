import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Card, CardContent } from '../../ui/Card';
import { formatCurrency, formatDate } from '../../utils/format';
import { TrendingUp, TrendingDown, DollarSign, Calendar, Briefcase, Building } from 'lucide-react';
const typeLabels = {
    stock: 'Stocks',
    bond: 'Bonds',
    mutual_fund: 'Mutual Funds',
    etf: 'ETFs',
    real_estate: 'Real Estate',
    retirement: 'Retirement Account',
    life_insurance: 'Life Insurance',
    other: 'Other',
};
const InvestmentSummary = ({ investment }) => {
    const isPositiveROI = investment.roi >= 0;
    return (_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6", children: [_jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-indigo-50 rounded-full", children: _jsx(DollarSign, { className: "h-6 w-6 text-indigo-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Initial Investment" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: formatCurrency(investment.initial_investment, 'USD') }), _jsxs("p", { className: "text-xs text-gray-500 mt-1", children: ["Started ", formatDate(investment.start_date)] })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-green-50 rounded-full", children: _jsx(DollarSign, { className: "h-6 w-6 text-green-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Current Value" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: formatCurrency(investment.current_value, 'USD') }), _jsxs("p", { className: "text-xs text-gray-500 mt-1", children: ["As of ", formatDate(investment.last_valuation_date)] })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: `flex-shrink-0 p-3 ${isPositiveROI ? 'bg-green-50' : 'bg-red-50'} rounded-full`, children: isPositiveROI ? (_jsx(TrendingUp, { className: "h-6 w-6 text-green-600" })) : (_jsx(TrendingDown, { className: "h-6 w-6 text-red-600" })) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Return on Investment" }), _jsxs("h3", { className: `text-xl font-bold mt-1 ${isPositiveROI ? 'text-green-600' : 'text-red-600'}`, children: [isPositiveROI ? '+' : '', investment.roi.toFixed(2), "%"] }), _jsx("p", { className: "text-xs text-gray-500 mt-1", children: formatCurrency(investment.current_value - investment.initial_investment, 'USD') })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-blue-50 rounded-full", children: _jsx(Building, { className: "h-6 w-6 text-blue-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Institution" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: investment.institution })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-purple-50 rounded-full", children: _jsx(Briefcase, { className: "h-6 w-6 text-purple-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Investment Type" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: typeLabels[investment.type] || investment.type })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-amber-50 rounded-full", children: _jsx(Calendar, { className: "h-6 w-6 text-amber-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Holding Period" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: getHoldingPeriod(investment.start_date) }), _jsxs("p", { className: "text-xs text-gray-500 mt-1", children: ["Since ", formatDate(investment.start_date)] })] })] }) }) })] }));
};
// Helper function to calculate holding period
function getHoldingPeriod(startDate) {
    const start = new Date(startDate);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - start.getTime());
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    const years = Math.floor(diffDays / 365);
    const months = Math.floor((diffDays % 365) / 30);
    if (years > 0) {
        return `${years}y ${months}m`;
    }
    else if (months > 0) {
        return `${months} months`;
    }
    else {
        return `${diffDays} days`;
    }
}
export default InvestmentSummary;

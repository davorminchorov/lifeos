import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { Card, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { formatCurrency } from '../../utils/format';
import { PlusCircle, ArrowUpRight, DollarSign, PercentIcon, Briefcase, PieChart, ArrowRight } from 'lucide-react';
const InvestmentsPage = () => {
    var _a;
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    useEffect(() => {
        const fetchSummary = async () => {
            try {
                setLoading(true);
                const response = await axios.get('/api/portfolio/summary');
                setSummary(response.data);
            }
            catch (err) {
                console.error('Failed to fetch portfolio summary', err);
                setError('Failed to load portfolio data');
            }
            finally {
                setLoading(false);
            }
        };
        fetchSummary();
    }, []);
    return (_jsxs(PageContainer, { title: "Investments", subtitle: "Manage and track your investments portfolio", actions: _jsx(Button, { variant: "filled", icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: _jsx(Link, { to: "/investments/new", children: "Add Investment" }) }), children: [error && (_jsx("div", { className: "mb-6", children: _jsx(Card, { variant: "elevated", children: _jsx(CardContent, { children: _jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg", children: error }) }) }) })), loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : (_jsxs(_Fragment, { children: [_jsx(PageSection, { children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6", children: [_jsx(Card, { variant: "elevated", children: _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "flex items-center justify-between", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-primary/10 rounded-full", children: _jsx(DollarSign, { className: "h-6 w-6 text-primary" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Total Invested" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: formatCurrency((summary === null || summary === void 0 ? void 0 : summary.total_invested) || 0, 'USD') })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-primary" })] }) }) }), _jsx(Card, { variant: "elevated", children: _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "flex items-center justify-between", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-success/10 rounded-full", children: _jsx(DollarSign, { className: "h-6 w-6 text-success" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Current Value" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: formatCurrency((summary === null || summary === void 0 ? void 0 : summary.total_current_value) || 0, 'USD') })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-success" })] }) }) }), _jsx(Card, { variant: "elevated", children: _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "flex items-center justify-between", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-warning/10 rounded-full", children: _jsx(PercentIcon, { className: "h-6 w-6 text-warning" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Overall ROI" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: summary && summary.overall_roi !== undefined ? (summary.overall_roi > 0 ? '+' : '') + summary.overall_roi.toFixed(2) + '%' : '0.00%' })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-warning" })] }) }) }), _jsx(Card, { variant: "elevated", children: _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "flex items-center justify-between", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-secondary/10 rounded-full", children: _jsx(Briefcase, { className: "h-6 w-6 text-secondary" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Total Investments" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: (_a = summary === null || summary === void 0 ? void 0 : summary.total_investments) !== null && _a !== void 0 ? _a : 0 })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-secondary" })] }) }) })] }) }), summary && summary.by_type && Object.keys(summary.by_type).length > 0 && (_jsx(PageSection, { title: "Portfolio Allocation", className: "mt-8", children: _jsx(Card, { variant: "outlined", children: _jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6", children: [_jsx("div", { className: "flex items-center justify-center", children: _jsx(PieChart, { className: "h-48 w-48 text-primary opacity-20" }) }), _jsxs("div", { children: [_jsx("h3", { className: "text-lg font-semibold mb-4", children: "Asset Allocation" }), _jsx("div", { className: "space-y-4", children: Object.entries(summary.by_type).map(([type, data]) => (_jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: "w-3 h-3 rounded-full mr-3", style: {
                                                                    backgroundColor: getColorForAssetType(type)
                                                                } }), _jsxs("div", { className: "flex-1", children: [_jsxs("div", { className: "flex justify-between items-center", children: [_jsx("span", { className: "text-sm font-medium", children: formatAssetType(type) }), _jsxs("span", { className: "text-sm text-on-surface-variant", children: [data.percentage.toFixed(1), "%"] })] }), _jsx("div", { className: "w-full bg-surface-variant h-1.5 rounded-full mt-1", children: _jsx("div", { className: "h-full rounded-full", style: {
                                                                                width: `${data.percentage}%`,
                                                                                backgroundColor: getColorForAssetType(type)
                                                                            } }) })] })] }, type))) })] })] }) }) }) })), _jsx("div", { className: "mt-8 text-center", children: _jsx(Button, { variant: "outlined", onClick: () => { }, icon: _jsx(ArrowRight, { className: "h-4 w-4 ml-2" }), className: "ml-auto", children: _jsx(Link, { to: "/investments/list", children: "View All Investments" }) }) })] }))] }));
};
// Helper functions for asset types
function formatAssetType(type) {
    const typeMap = {
        stock: 'Stocks',
        bond: 'Bonds',
        mutual_fund: 'Mutual Funds',
        etf: 'ETFs',
        real_estate: 'Real Estate',
        retirement: 'Retirement',
        crypto: 'Cryptocurrency',
        cash: 'Cash & Savings',
        other: 'Other',
    };
    return typeMap[type] || type.charAt(0).toUpperCase() + type.slice(1);
}
function getColorForAssetType(type) {
    const colorMap = {
        stock: '#4F46E5', // indigo
        bond: '#0891B2', // cyan
        mutual_fund: '#7C3AED', // violet
        etf: '#2563EB', // blue
        real_estate: '#16A34A', // green
        retirement: '#EA580C', // orange
        crypto: '#9333EA', // purple
        cash: '#65A30D', // lime
        other: '#94A3B8', // slate
    };
    return colorMap[type] || '#94A3B8';
}
export default InvestmentsPage;

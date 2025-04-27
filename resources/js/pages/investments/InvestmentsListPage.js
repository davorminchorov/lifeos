import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Card } from '../../ui/Card';
const typeLabels = {
    stock: 'Stocks',
    bond: 'Bonds',
    mutual_fund: 'Mutual Funds',
    etf: 'ETFs',
    real_estate: 'Real Estate',
    retirement: 'Retirement Accounts',
    life_insurance: 'Life Insurance',
    other: 'Other Investments',
};
const typeColors = {
    stock: 'bg-blue-600',
    bond: 'bg-green-600',
    mutual_fund: 'bg-purple-600',
    etf: 'bg-yellow-600',
    real_estate: 'bg-red-600',
    retirement: 'bg-indigo-600',
    life_insurance: 'bg-teal-600',
    other: 'bg-gray-600',
};
const InvestmentsListPage = () => {
    const [investments, setInvestments] = useState([]);
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const fetchData = async () => {
        try {
            setLoading(true);
            setError('');
            // Fetch investments list
            const investmentsResponse = await axios.get('/api/investments');
            setInvestments(Array.isArray(investmentsResponse.data) ? investmentsResponse.data : []);
            // Fetch portfolio summary
            const summaryResponse = await axios.get('/api/portfolio/summary');
            setSummary(summaryResponse.data);
        }
        catch (err) {
            console.error('Failed to load investments data', err);
            setError('Failed to load investments data. Please try again.');
        }
        finally {
            setLoading(false);
        }
    };
    useEffect(() => {
        fetchData();
    }, []);
    if (loading) {
        return (_jsx("div", { className: "p-8 flex justify-center", children: _jsx("div", { className: "w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin" }) }));
    }
    return (_jsxs("div", { children: [error && (_jsx("div", { className: "bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-6", role: "alert", children: _jsx("span", { className: "block sm:inline font-medium", children: error }) })), summary && (_jsx("div", { className: "mb-8", children: _jsxs(Card, { className: "bg-white rounded-xl shadow-sm overflow-hidden", children: [_jsxs("div", { className: "border-b border-gray-100 px-6 py-4", children: [_jsx("h3", { className: "text-lg font-semibold text-gray-800", children: "Portfolio Allocation" }), _jsx("p", { className: "text-sm text-gray-500 mt-1", children: "Distribution of your investments by type" })] }), _jsx("div", { className: "p-6", children: summary && summary.by_type && Object.keys(summary.by_type).length > 0 ? (_jsxs(_Fragment, { children: [_jsx("div", { className: "flex h-6 w-full rounded-md overflow-hidden mb-6", children: Object.entries(summary.by_type).map(([type, data]) => (_jsx("div", { className: `${typeColors[type] || 'bg-gray-600'}`, style: { width: `${data.percentage}%` }, title: `${typeLabels[type] || type}: ${data.percentage.toFixed(1)}%` }, type))) }), _jsx("div", { className: "grid grid-cols-2 md:grid-cols-4 gap-4", children: Object.entries(summary.by_type).map(([type, data]) => (_jsxs("div", { className: "flex items-center p-3 bg-gray-50 rounded-lg", children: [_jsx("div", { className: `w-4 h-4 rounded-full ${typeColors[type] || 'bg-gray-600'} mr-3` }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-800", children: typeLabels[type] || type }), _jsxs("p", { className: "text-xs text-gray-500 mt-1", children: ["$", data.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }), " (", data.percentage.toFixed(1), "%)"] })] })] }, type))) })] })) : (_jsxs("div", { className: "py-12 text-center", children: [_jsxs("svg", { className: "mx-auto h-16 w-16 text-gray-200", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: [_jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1, d: "M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" }), _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1, d: "M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" })] }), _jsx("h3", { className: "mt-4 text-lg font-medium text-gray-900", children: "No allocation data" }), _jsx("p", { className: "mt-1 text-sm text-gray-500", children: "Add your first investment to see your portfolio allocation." })] })) })] }) })), _jsxs(Card, { className: "bg-white rounded-xl shadow-sm overflow-hidden", children: [_jsxs("div", { className: "border-b border-gray-100 px-6 py-4", children: [_jsx("h3", { className: "text-lg font-semibold text-gray-800", children: "Your Investments" }), _jsx("p", { className: "text-sm text-gray-500 mt-1", children: "Manage your investment portfolio" })] }), _jsx("div", { className: "p-0", children: _jsx("div", { className: "overflow-x-auto", children: _jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { className: "bg-gray-50 border-b border-gray-100", children: [_jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Name" }), _jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Type" }), _jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Institution" }), _jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Current Value" }), _jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "ROI" }), _jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Last Valuation" }), _jsx(TableHead, { className: "py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Actions" })] }) }), _jsx(TableBody, { children: investments.length === 0 ? (_jsx(TableRow, { children: _jsx(TableCell, { colSpan: 7, className: "text-center py-16 px-6", children: _jsxs("div", { className: "flex flex-col items-center justify-center", children: [_jsx("svg", { className: "h-16 w-16 text-gray-200 mb-2", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1, d: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" }) }), _jsx("h3", { className: "text-lg font-medium text-gray-900", children: "No investments found" }), _jsx("p", { className: "mt-1 text-sm text-gray-500 max-w-sm text-center", children: "Click \"Add Investment\" to start building your portfolio." })] }) }) })) : (investments.map((investment) => (_jsxs(TableRow, { className: "hover:bg-gray-50 border-b border-gray-100", children: [_jsx(TableCell, { className: "py-4 px-6 font-medium text-gray-900", children: investment.name }), _jsx(TableCell, { className: "py-4 px-6", children: _jsx("span", { className: "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800", children: typeLabels[investment.type] || investment.type }) }), _jsx(TableCell, { className: "py-4 px-6 text-gray-500", children: investment.institution }), _jsxs(TableCell, { className: "py-4 px-6 font-medium text-gray-900", children: ["$", investment.current_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })] }), _jsx(TableCell, { className: "py-4 px-6", children: _jsxs("span", { className: `${investment.roi > 0 ? 'text-green-600' : investment.roi < 0 ? 'text-red-600' : 'text-gray-600'} font-medium`, children: [investment.roi > 0 ? '+' : (investment.roi < 0 ? '' : ''), investment.roi.toFixed(2), "%"] }) }), _jsx(TableCell, { className: "py-4 px-6 text-gray-500", children: new Date(investment.last_valuation_date).toLocaleDateString() }), _jsx(TableCell, { className: "py-4 px-6", children: _jsx(Link, { to: `/investments/${investment.id}`, className: "text-indigo-600 hover:text-indigo-900 font-medium text-sm", children: "View Details" }) })] }, investment.id)))) })] }) }) })] })] }));
};
export default InvestmentsListPage;

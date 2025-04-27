import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { PlusIcon } from '@heroicons/react/24/outline';
const InvestmentsList = () => {
    const [investments, setInvestments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    useEffect(() => {
        fetchInvestments();
    }, []);
    const fetchInvestments = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/investments');
            setInvestments(response.data.data);
            setError('');
        }
        catch (err) {
            console.error('Error fetching investments:', err);
            setError('Failed to load investments. Please try again.');
        }
        finally {
            setLoading(false);
        }
    };
    const getInvestmentTypeLabel = (type) => {
        const types = {
            stock: 'Stock',
            bond: 'Bond',
            etf: 'ETF',
            crypto: 'Cryptocurrency',
            real_estate: 'Real Estate',
            mutual_fund: 'Mutual Fund',
            other: 'Other',
        };
        return types[type] || type;
    };
    const formatCurrency = (value) => {
        if (value === null)
            return 'N/A';
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(value);
    };
    const formatPercentage = (value) => {
        if (value === null)
            return 'N/A';
        return new Intl.NumberFormat('en-US', {
            style: 'percent',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value / 100);
    };
    if (loading) {
        return (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600" }) }));
    }
    if (error) {
        return (_jsx("div", { className: "bg-red-50 border-l-4 border-red-400 p-4 my-4", children: _jsxs("div", { className: "flex", children: [_jsx("div", { className: "flex-shrink-0", children: _jsx("svg", { className: "h-5 w-5 text-red-400", xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", children: _jsx("path", { fillRule: "evenodd", d: "M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z", clipRule: "evenodd" }) }) }), _jsxs("div", { className: "ml-3", children: [_jsx("p", { className: "text-sm text-red-700", children: error }), _jsx("button", { onClick: fetchInvestments, className: "mt-2 text-sm font-medium text-red-700 hover:text-red-600", children: "Try Again" })] })] }) }));
    }
    return (_jsxs("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg", children: [_jsxs("div", { className: "px-4 py-5 sm:px-6 flex justify-between items-center", children: [_jsxs("div", { children: [_jsx("h3", { className: "text-lg leading-6 font-medium text-gray-900", children: "Investments" }), _jsx("p", { className: "mt-1 max-w-2xl text-sm text-gray-500", children: "Track and manage your investment portfolio" })] }), _jsxs(Link, { to: "/investments/create", className: "inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500", children: [_jsx(PlusIcon, { className: "-ml-1 mr-2 h-5 w-5", "aria-hidden": "true" }), "New Investment"] })] }), investments.length === 0 ? (_jsxs("div", { className: "text-center py-12 bg-gray-50", children: [_jsx("svg", { className: "mx-auto h-12 w-12 text-gray-400", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24", xmlns: "http://www.w3.org/2000/svg", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" }) }), _jsx("h3", { className: "mt-2 text-sm font-medium text-gray-900", children: "No investments" }), _jsx("p", { className: "mt-1 text-sm text-gray-500", children: "Get started by creating a new investment." }), _jsx("div", { className: "mt-6", children: _jsxs(Link, { to: "/investments/create", className: "inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500", children: [_jsx(PlusIcon, { className: "-ml-1 mr-2 h-5 w-5", "aria-hidden": "true" }), "New Investment"] }) })] })) : (_jsx("div", { className: "overflow-x-auto", children: _jsxs("table", { className: "min-w-full divide-y divide-gray-200", children: [_jsx("thead", { className: "bg-gray-50", children: _jsxs("tr", { children: [_jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Investment" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Type" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Current Value" }), _jsx("th", { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Gain/Loss" }), _jsx("th", { className: "px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Actions" })] }) }), _jsx("tbody", { className: "bg-white divide-y divide-gray-200", children: investments.map((investment) => (_jsxs("tr", { className: "hover:bg-gray-50", children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: _jsx("div", { className: "text-sm font-medium text-gray-900", children: _jsx(Link, { to: `/investments/${investment.id}`, className: "hover:text-indigo-600", children: investment.name }) }) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: _jsx("span", { className: "px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800", children: getInvestmentTypeLabel(investment.type) }) }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: formatCurrency(investment.current_value) }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap", children: [investment.gain_loss !== null && (_jsxs("div", { className: "flex flex-col", children: [_jsx("span", { className: `text-sm ${investment.gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}`, children: formatCurrency(investment.gain_loss) }), _jsx("span", { className: `text-xs ${investment.gain_loss_percentage >= 0 ? 'text-green-600' : 'text-red-600'}`, children: formatPercentage(investment.gain_loss_percentage) })] })), investment.gain_loss === null && (_jsx("span", { className: "text-sm text-gray-500", children: "N/A" }))] }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap text-right text-sm font-medium", children: [_jsx(Link, { to: `/investments/${investment.id}`, className: "text-indigo-600 hover:text-indigo-900 mr-4", children: "View" }), _jsx(Link, { to: `/investments/${investment.id}/edit`, className: "text-indigo-600 hover:text-indigo-900", children: "Edit" })] })] }, investment.id))) })] }) }))] }));
};
export default InvestmentsList;

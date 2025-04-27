import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import TransactionForm from '../../components/investments/TransactionForm';
import ValuationForm from '../../components/investments/ValuationForm';
const InvestmentDetail = () => {
    const { id } = useParams();
    const [investment, setInvestment] = useState(null);
    const [transactions, setTransactions] = useState([]);
    const [valuations, setValuations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [activeTab, setActiveTab] = useState('details');
    const [showTransactionForm, setShowTransactionForm] = useState(false);
    const [showValuationForm, setShowValuationForm] = useState(false);
    const fetchInvestment = async () => {
        try {
            const response = await axios.get(`/api/investments/${id}`);
            setInvestment(response.data);
        }
        catch (err) {
            console.error('Error fetching investment:', err);
            setError('Failed to load investment details.');
        }
    };
    const fetchTransactions = async () => {
        try {
            const response = await axios.get(`/api/investments/${id}/transactions`);
            setTransactions(response.data);
        }
        catch (err) {
            console.error('Error fetching transactions:', err);
        }
    };
    const fetchValuations = async () => {
        try {
            const response = await axios.get(`/api/investments/${id}/valuations`);
            setValuations(response.data);
        }
        catch (err) {
            console.error('Error fetching valuations:', err);
        }
    };
    const loadData = async () => {
        setLoading(true);
        await Promise.all([
            fetchInvestment(),
            fetchTransactions(),
            fetchValuations(),
        ]);
        setLoading(false);
    };
    useEffect(() => {
        loadData();
    }, [id]);
    const handleTransactionAdded = () => {
        setShowTransactionForm(false);
        fetchTransactions();
        fetchInvestment(); // Refresh investment details as well
    };
    const handleValuationAdded = () => {
        setShowValuationForm(false);
        fetchValuations();
        fetchInvestment(); // Refresh investment details as well
    };
    if (loading) {
        return (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500" }) }));
    }
    if (error || !investment) {
        return (_jsxs("div", { className: "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded", role: "alert", children: [_jsx("p", { children: error || 'Investment not found.' }), _jsx("p", { className: "mt-2", children: _jsx(Link, { to: "/investments", className: "text-indigo-600 hover:text-indigo-900", children: "Return to investments" }) })] }));
    }
    return (_jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6", children: [_jsxs("div", { className: "flex justify-between items-center mb-6", children: [_jsxs("div", { children: [_jsx("h1", { className: "text-2xl font-semibold text-gray-900", children: investment.name }), _jsxs("p", { className: "text-sm text-gray-500", children: [investment.type, " \u2022 ", investment.institution] })] }), _jsxs(Link, { to: "/investments", className: "text-indigo-600 hover:text-indigo-900 flex items-center", children: [_jsx("svg", { className: "h-5 w-5 mr-1", xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor", "aria-hidden": "true", children: _jsx("path", { fillRule: "evenodd", d: "M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z", clipRule: "evenodd" }) }), "Back to Investments"] })] }), _jsx("div", { className: "border-b border-gray-200 mb-6", children: _jsxs("nav", { className: "-mb-px flex space-x-8", children: [_jsx("button", { onClick: () => setActiveTab('details'), className: `${activeTab === 'details'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`, children: "Details" }), _jsx("button", { onClick: () => setActiveTab('transactions'), className: `${activeTab === 'transactions'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`, children: "Transactions" }), _jsx("button", { onClick: () => setActiveTab('valuations'), className: `${activeTab === 'valuations'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`, children: "Valuations" })] }) }), activeTab === 'details' && (_jsxs("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg", children: [_jsxs("div", { className: "px-4 py-5 sm:px-6", children: [_jsx("h3", { className: "text-lg leading-6 font-medium text-gray-900", children: "Investment Details" }), _jsx("p", { className: "mt-1 max-w-2xl text-sm text-gray-500", children: "Details and information about this investment account." })] }), _jsx("div", { className: "border-t border-gray-200", children: _jsxs("dl", { children: [_jsxs("div", { className: "bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6", children: [_jsx("dt", { className: "text-sm font-medium text-gray-500", children: "Name" }), _jsx("dd", { className: "mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2", children: investment.name })] }), _jsxs("div", { className: "bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6", children: [_jsx("dt", { className: "text-sm font-medium text-gray-500", children: "Type" }), _jsx("dd", { className: "mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2", children: investment.type })] }), _jsxs("div", { className: "bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6", children: [_jsx("dt", { className: "text-sm font-medium text-gray-500", children: "Institution" }), _jsx("dd", { className: "mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2", children: investment.institution })] }), _jsxs("div", { className: "bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6", children: [_jsx("dt", { className: "text-sm font-medium text-gray-500", children: "Account Number" }), _jsx("dd", { className: "mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2", children: investment.account_number })] }), _jsxs("div", { className: "bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6", children: [_jsx("dt", { className: "text-sm font-medium text-gray-500", children: "Current Value" }), _jsxs("dd", { className: "mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2", children: ["$", investment.current_value.toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                })] })] }), investment.notes && (_jsxs("div", { className: "bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6", children: [_jsx("dt", { className: "text-sm font-medium text-gray-500", children: "Notes" }), _jsx("dd", { className: "mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2", children: investment.notes })] }))] }) })] })), activeTab === 'transactions' && (_jsxs("div", { children: [_jsxs("div", { className: "flex justify-between items-center mb-4", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Transactions" }), _jsx("button", { onClick: () => setShowTransactionForm(true), className: "inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500", children: "Add Transaction" })] }), showTransactionForm ? (_jsxs("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg p-4 mb-6", children: [_jsx("h4", { className: "text-lg font-medium text-gray-900 mb-4", children: "New Transaction" }), _jsx(TransactionForm, { investmentId: id || '', onTransactionAdded: handleTransactionAdded, onCancel: () => setShowTransactionForm(false) })] })) : transactions.length > 0 ? (_jsx("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg", children: _jsxs("table", { className: "min-w-full divide-y divide-gray-200", children: [_jsx("thead", { className: "bg-gray-50", children: _jsxs("tr", { children: [_jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Date" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Type" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Amount" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Notes" })] }) }), _jsx("tbody", { className: "bg-white divide-y divide-gray-200", children: transactions.map((transaction) => (_jsxs("tr", { children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: new Date(transaction.date).toLocaleDateString() }), _jsx("td", { className: "px-6 py-4 whitespace-nowrap", children: _jsx("span", { className: `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${transaction.type === 'deposit'
                                                        ? 'bg-green-100 text-green-800'
                                                        : transaction.type === 'withdrawal'
                                                            ? 'bg-red-100 text-red-800'
                                                            : 'bg-blue-100 text-blue-800'}`, children: transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1) }) }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: ["$", transaction.amount.toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    })] }), _jsx("td", { className: "px-6 py-4 text-sm text-gray-500 max-w-xs truncate", children: transaction.notes || '-' })] }, transaction.id))) })] }) })) : (_jsx("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center text-gray-500", children: "No transactions recorded for this investment." }))] })), activeTab === 'valuations' && (_jsxs("div", { children: [_jsxs("div", { className: "flex justify-between items-center mb-4", children: [_jsx("h3", { className: "text-lg font-medium text-gray-900", children: "Valuations" }), _jsx("button", { onClick: () => setShowValuationForm(true), className: "inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500", children: "Add Valuation" })] }), showValuationForm ? (_jsxs("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg p-4 mb-6", children: [_jsx("h4", { className: "text-lg font-medium text-gray-900 mb-4", children: "New Valuation" }), _jsx(ValuationForm, { investmentId: id || '', onValuationAdded: handleValuationAdded, onCancel: () => setShowValuationForm(false) })] })) : valuations.length > 0 ? (_jsx("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg", children: _jsxs("table", { className: "min-w-full divide-y divide-gray-200", children: [_jsx("thead", { className: "bg-gray-50", children: _jsxs("tr", { children: [_jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Date" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Value" }), _jsx("th", { scope: "col", className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Notes" })] }) }), _jsx("tbody", { className: "bg-white divide-y divide-gray-200", children: valuations.map((valuation) => (_jsxs("tr", { children: [_jsx("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: new Date(valuation.date).toLocaleDateString() }), _jsxs("td", { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-500", children: ["$", valuation.value.toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2,
                                                    })] }), _jsx("td", { className: "px-6 py-4 text-sm text-gray-500 max-w-xs truncate", children: valuation.notes || '-' })] }, valuation.id))) })] }) })) : (_jsx("div", { className: "bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center text-gray-500", children: "No valuations recorded for this investment." }))] }))] }));
};
export default InvestmentDetail;

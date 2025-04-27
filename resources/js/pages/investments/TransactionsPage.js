import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Button } from '../../ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { ArrowDownCircle, ArrowUpCircle, PiggyBank, Banknote, AlertCircle, Filter, Download } from 'lucide-react';
const transactionTypeConfig = {
    deposit: {
        label: 'Deposit',
        icon: ArrowDownCircle,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
        badgeColor: 'bg-green-100 text-green-800'
    },
    withdrawal: {
        label: 'Withdrawal',
        icon: ArrowUpCircle,
        color: 'text-red-600',
        bgColor: 'bg-red-50',
        badgeColor: 'bg-red-100 text-red-800'
    },
    dividend: {
        label: 'Dividend',
        icon: PiggyBank,
        color: 'text-blue-600',
        bgColor: 'bg-blue-50',
        badgeColor: 'bg-blue-100 text-blue-800'
    },
    interest: {
        label: 'Interest',
        icon: Banknote,
        color: 'text-purple-600',
        bgColor: 'bg-purple-50',
        badgeColor: 'bg-purple-100 text-purple-800'
    },
    fee: {
        label: 'Fee',
        icon: AlertCircle,
        color: 'text-orange-600',
        bgColor: 'bg-orange-50',
        badgeColor: 'bg-orange-100 text-orange-800'
    }
};
const TransactionsPage = () => {
    const [transactions, setTransactions] = useState([]);
    const [investments, setInvestments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [filters, setFilters] = useState({});
    const [showFilters, setShowFilters] = useState(false);
    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                setError('');
                // Fetch investments for filter dropdown
                const investmentsResponse = await axios.get('/api/investments');
                setInvestments(Array.isArray(investmentsResponse.data)
                    ? investmentsResponse.data.map((inv) => ({ id: inv.id, name: inv.name }))
                    : []);
                // Fetch transactions with any applied filters
                const params = new URLSearchParams();
                if (filters.type)
                    params.append('type', filters.type);
                if (filters.investment_id)
                    params.append('investment_id', filters.investment_id);
                if (filters.dateFrom)
                    params.append('date_from', filters.dateFrom);
                if (filters.dateTo)
                    params.append('date_to', filters.dateTo);
                const transactionsResponse = await axios.get(`/api/transactions?${params.toString()}`);
                setTransactions(Array.isArray(transactionsResponse.data) ? transactionsResponse.data : []);
            }
            catch (err) {
                console.error('Failed to load transactions data', err);
                setError('Failed to load transactions data. Please try again.');
            }
            finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [filters]);
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => (Object.assign(Object.assign({}, prev), { [name]: value || undefined // Convert empty strings to undefined
         })));
    };
    const clearFilters = () => {
        setFilters({});
    };
    const exportToCSV = () => {
        // Create CSV content
        const headers = ['Date', 'Investment', 'Type', 'Amount', 'Notes'];
        const csvRows = [
            headers.join(','),
            ...transactions.map(tx => [
                tx.date,
                `"${tx.investment_name}"`,
                transactionTypeConfig[tx.type].label,
                tx.amount.toFixed(2),
                tx.notes ? `"${tx.notes.replace(/"/g, '""')}"` : ''
            ].join(','))
        ];
        const csvContent = csvRows.join('\n');
        // Create and download the file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `investment_transactions_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    // Calculate summary statistics
    const transactionSummary = transactions.reduce((acc, tx) => {
        if (tx.type === 'deposit' || tx.type === 'dividend' || tx.type === 'interest') {
            acc.totalInflow += tx.amount;
        }
        else {
            acc.totalOutflow += tx.amount;
        }
        acc.counts[tx.type] = (acc.counts[tx.type] || 0) + 1;
        return acc;
    }, {
        totalInflow: 0,
        totalOutflow: 0,
        counts: {}
    });
    // Sort transactions by date (most recent first)
    const sortedTransactions = [...transactions].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
    return (_jsxs("div", { className: "max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8", children: [_jsxs("div", { className: "flex justify-between items-center mb-8", children: [_jsxs("div", { children: [_jsx("h1", { className: "text-3xl font-bold tracking-tight text-gray-900", children: "Transactions" }), _jsx("p", { className: "mt-2 text-sm text-gray-500", children: "View and manage all investment transactions" })] }), _jsxs("div", { className: "flex items-center space-x-3", children: [_jsxs(Button, { onClick: () => setShowFilters(!showFilters), variant: "outline", className: "flex items-center gap-2", children: [_jsx(Filter, { className: "h-4 w-4" }), showFilters ? 'Hide Filters' : 'Show Filters'] }), _jsxs(Button, { onClick: exportToCSV, variant: "outline", className: "flex items-center gap-2", children: [_jsx(Download, { className: "h-4 w-4" }), "Export CSV"] })] })] }), showFilters && (_jsx(Card, { className: "mb-8 bg-white shadow-sm rounded-xl overflow-hidden", children: _jsxs(CardContent, { className: "p-6", children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-4 gap-4", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "type", className: "block text-sm font-medium text-gray-700 mb-1", children: "Transaction Type" }), _jsxs("select", { id: "type", name: "type", value: filters.type || '', onChange: handleFilterChange, className: "block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm", children: [_jsx("option", { value: "", children: "All Types" }), _jsx("option", { value: "deposit", children: "Deposits" }), _jsx("option", { value: "withdrawal", children: "Withdrawals" }), _jsx("option", { value: "dividend", children: "Dividends" }), _jsx("option", { value: "interest", children: "Interest" }), _jsx("option", { value: "fee", children: "Fees" })] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "investment_id", className: "block text-sm font-medium text-gray-700 mb-1", children: "Investment" }), _jsxs("select", { id: "investment_id", name: "investment_id", value: filters.investment_id || '', onChange: handleFilterChange, className: "block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm", children: [_jsx("option", { value: "", children: "All Investments" }), investments.map(inv => (_jsx("option", { value: inv.id, children: inv.name }, inv.id)))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "dateFrom", className: "block text-sm font-medium text-gray-700 mb-1", children: "From Date" }), _jsx("input", { type: "date", id: "dateFrom", name: "dateFrom", value: filters.dateFrom || '', onChange: handleFilterChange, className: "block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "dateTo", className: "block text-sm font-medium text-gray-700 mb-1", children: "To Date" }), _jsx("input", { type: "date", id: "dateTo", name: "dateTo", value: filters.dateTo || '', onChange: handleFilterChange, className: "block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" })] })] }), _jsx("div", { className: "mt-4 flex justify-end", children: _jsx(Button, { onClick: clearFilters, variant: "outline", size: "sm", className: "text-sm", children: "Clear Filters" }) })] }) })), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-6 mb-8", children: [_jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-green-50 rounded-full", children: _jsx(ArrowDownCircle, { className: "h-6 w-6 text-green-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Total Inflows" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: formatCurrency(transactionSummary.totalInflow, 'USD') }), _jsx("p", { className: "text-xs text-gray-500 mt-1", children: "Deposits, dividends, and interest" })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-red-50 rounded-full", children: _jsx(ArrowUpCircle, { className: "h-6 w-6 text-red-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Total Outflows" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: formatCurrency(transactionSummary.totalOutflow, 'USD') }), _jsx("p", { className: "text-xs text-gray-500 mt-1", children: "Withdrawals and fees" })] })] }) }) }), _jsx(Card, { className: "bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden", children: _jsx(CardContent, { className: "p-5", children: _jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-indigo-50 rounded-full", children: _jsx(PiggyBank, { className: "h-6 w-6 text-indigo-600" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-sm font-medium text-gray-500", children: "Net Flow" }), _jsx("h3", { className: "text-xl font-bold text-gray-900 mt-1", children: formatCurrency(transactionSummary.totalInflow - transactionSummary.totalOutflow, 'USD') }), _jsxs("p", { className: "text-xs text-gray-500 mt-1", children: [transactions.length, " transactions total"] })] })] }) }) })] }), _jsxs(Card, { className: "bg-white shadow-sm rounded-xl overflow-hidden", children: [_jsxs(CardHeader, { className: "bg-gray-50 border-b border-gray-100 px-6 py-4", children: [_jsx(CardTitle, { className: "text-lg font-semibold text-gray-800", children: "All Transactions" }), _jsx(CardDescription, { className: "text-sm text-gray-500 mt-1", children: "Complete record of investment cash flows" })] }), _jsx(CardContent, { className: "p-0", children: loading ? (_jsx("div", { className: "flex justify-center items-center py-12", children: _jsx("div", { className: "w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin" }) })) : error ? (_jsx("div", { className: "bg-red-50 border-l-4 border-red-400 p-4 m-6", children: _jsx("p", { className: "text-sm text-red-700", children: error }) })) : transactions.length === 0 ? (_jsxs("div", { className: "py-12 px-6 text-center", children: [_jsx("div", { className: "flex justify-center", children: _jsx("div", { className: "bg-gray-50 rounded-full p-3", children: _jsx(Banknote, { className: "h-8 w-8 text-gray-400" }) }) }), _jsx("h3", { className: "mt-4 text-lg font-medium text-gray-900", children: "No transactions found" }), _jsx("p", { className: "mt-1 text-sm text-gray-500 max-w-sm mx-auto", children: Object.keys(filters).length > 0
                                        ? 'Try changing your filters to see more results.'
                                        : 'Start recording transactions for your investments to track performance.' })] })) : (_jsx("div", { className: "overflow-x-auto", children: _jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { className: "bg-gray-50", children: [_jsx(TableHead, { className: "w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Date" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Investment" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Type" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Amount" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Notes" }), _jsx(TableHead, { className: "px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Actions" })] }) }), _jsx(TableBody, { children: sortedTransactions.map(transaction => {
                                            const config = transactionTypeConfig[transaction.type];
                                            const Icon = config.icon;
                                            return (_jsxs(TableRow, { className: "hover:bg-gray-50 border-b border-gray-100 last:border-0", children: [_jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900", children: formatDate(transaction.date) }), _jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap text-sm text-gray-700", children: _jsx(Link, { to: `/investments/${transaction.investment_id}`, className: "text-indigo-600 hover:text-indigo-900", children: transaction.investment_name }) }), _jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap", children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: `flex-shrink-0 p-1.5 ${config.bgColor} rounded-full mr-2`, children: _jsx(Icon, { className: `h-4 w-4 ${config.color}` }) }), _jsx(Badge, { className: config.badgeColor, children: config.label })] }) }), _jsxs(TableCell, { className: `px-6 py-4 whitespace-nowrap text-sm font-medium ${transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                                                            ? 'text-green-700'
                                                            : 'text-red-700'}`, children: [transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                                                                ? '+'
                                                                : '-', formatCurrency(Math.abs(transaction.amount), 'USD')] }), _jsx(TableCell, { className: "px-6 py-4 text-sm text-gray-500 max-w-xs truncate", children: transaction.notes || '-' }), _jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap text-right text-sm font-medium", children: _jsx(Link, { to: `/investments/${transaction.investment_id}/transactions/${transaction.id}`, className: "text-indigo-600 hover:text-indigo-900", children: "View" }) })] }, transaction.id));
                                        }) })] }) })) })] })] }));
};
export default TransactionsPage;

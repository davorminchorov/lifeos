import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Badge } from '../../ui/Badge';
import { formatCurrency, formatDate } from '../../utils/format';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import { ArrowDownCircle, ArrowUpCircle, PiggyBank, Banknote, AlertCircle } from 'lucide-react';
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
const TransactionsList = ({ transactions, onEditTransaction, onDeleteTransaction }) => {
    // Sort transactions by date, most recent first
    const sortedTransactions = [...transactions].sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
    return (_jsxs("div", { className: "overflow-hidden rounded-xl border border-gray-200 bg-white", children: [_jsxs("div", { className: "px-6 py-4 border-b border-gray-100", children: [_jsx("h3", { className: "text-lg font-semibold text-gray-800", children: "Transaction History" }), _jsx("p", { className: "text-sm text-gray-500 mt-1", children: "Record of all transactions for this investment" })] }), transactions.length === 0 ? (_jsxs("div", { className: "py-12 px-6 text-center", children: [_jsx("div", { className: "flex justify-center", children: _jsx("div", { className: "bg-gray-50 rounded-full p-3", children: _jsx(Banknote, { className: "h-8 w-8 text-gray-400" }) }) }), _jsx("h3", { className: "mt-4 text-lg font-medium text-gray-900", children: "No transactions yet" }), _jsx("p", { className: "mt-1 text-sm text-gray-500 max-w-sm mx-auto", children: "Record deposits, withdrawals, dividends, and other transactions to track your investment performance accurately." })] })) : (_jsx("div", { className: "overflow-x-auto", children: _jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { className: "bg-gray-50", children: [_jsx(TableHead, { className: "w-48 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Date" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Type" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Amount" }), _jsx(TableHead, { className: "px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Notes" }), (onEditTransaction || onDeleteTransaction) && (_jsx(TableHead, { className: "px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider", children: "Actions" }))] }) }), _jsx(TableBody, { children: sortedTransactions.map(transaction => {
                                const config = transactionTypeConfig[transaction.type];
                                const Icon = config.icon;
                                return (_jsxs(TableRow, { className: "hover:bg-gray-50 border-b border-gray-100 last:border-0", children: [_jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900", children: formatDate(transaction.date) }), _jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap", children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: `flex-shrink-0 p-1.5 ${config.bgColor} rounded-full mr-2`, children: _jsx(Icon, { className: `h-4 w-4 ${config.color}` }) }), _jsx(Badge, { className: config.badgeColor, children: config.label })] }) }), _jsxs(TableCell, { className: `px-6 py-4 whitespace-nowrap text-sm font-medium ${transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                                                ? 'text-green-700'
                                                : 'text-red-700'}`, children: [transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                                                    ? '+'
                                                    : '-', formatCurrency(Math.abs(transaction.amount), 'USD')] }), _jsx(TableCell, { className: "px-6 py-4 text-sm text-gray-500 max-w-xs truncate", children: transaction.notes || '-' }), (onEditTransaction || onDeleteTransaction) && (_jsx(TableCell, { className: "px-6 py-4 whitespace-nowrap text-right text-sm font-medium", children: _jsxs("div", { className: "flex justify-end space-x-3", children: [onEditTransaction && (_jsx("button", { onClick: () => onEditTransaction(transaction.id), className: "text-indigo-600 hover:text-indigo-900", children: "Edit" })), onDeleteTransaction && (_jsx("button", { onClick: () => onDeleteTransaction(transaction.id), className: "text-red-600 hover:text-red-900", children: "Delete" }))] }) }))] }, transaction.id));
                            }) })] }) }))] }));
};
export default TransactionsList;

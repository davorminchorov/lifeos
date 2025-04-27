import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../ui/Tabs';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { PageContainer } from '../../ui/PageContainer';
import { formatCurrency } from '../../utils/format';
import { ArrowLeft, Edit, PlusCircle, TrendingUp, DollarSign, Calendar, FileText, LineChart, ArrowUp, ArrowDown } from 'lucide-react';
import TransactionForm from '../../components/investments/TransactionForm';
import ValuationForm from '../../components/investments/ValuationForm';
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
const transactionTypeLabels = {
    deposit: 'Deposit',
    withdrawal: 'Withdrawal',
    dividend: 'Dividend',
    fee: 'Fee',
    interest: 'Interest',
};
const getTransactionBadge = (type) => {
    switch (type) {
        case 'deposit':
            return _jsx(Badge, { variant: "success", children: "Deposit" });
        case 'withdrawal':
            return _jsx(Badge, { variant: "danger", children: "Withdrawal" });
        case 'dividend':
            return _jsx(Badge, { variant: "secondary", children: "Dividend" });
        case 'fee':
            return _jsx(Badge, { variant: "warning", children: "Fee" });
        case 'interest':
            return _jsx(Badge, { variant: "outline", children: "Interest" });
        default:
            return _jsx(Badge, { variant: "default", children: type });
    }
};
const InvestmentDetailPage = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [investment, setInvestment] = useState(null);
    const [transactions, setTransactions] = useState([]);
    const [valuations, setValuations] = useState([]);
    const [performance, setPerformance] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [activeTab, setActiveTab] = useState('overview');
    const [showAddTransactionForm, setShowAddTransactionForm] = useState(false);
    const [showAddValuationForm, setShowAddValuationForm] = useState(false);
    useEffect(() => {
        const fetchData = async () => {
            if (!id)
                return;
            try {
                setLoading(true);
                setError('');
                // Fetch investment details
                const detailsResponse = await axios.get(`/api/investments/${id}`);
                setInvestment(detailsResponse.data.investment);
                setTransactions(detailsResponse.data.transactions);
                setValuations(detailsResponse.data.valuations);
                // Fetch performance data
                const performanceResponse = await axios.get(`/api/investments/${id}/performance`);
                setPerformance(performanceResponse.data);
            }
            catch (err) {
                console.error('Failed to load investment data', err);
                setError('Failed to load investment data. Please try again.');
            }
            finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [id]);
    const handleBack = () => {
        navigate('/investments');
    };
    // Define fetchData function outside of useEffect for reuse
    const refreshData = async () => {
        if (!id)
            return;
        try {
            setLoading(true);
            setError('');
            // Fetch investment details
            const detailsResponse = await axios.get(`/api/investments/${id}`);
            setInvestment(detailsResponse.data.investment);
            setTransactions(detailsResponse.data.transactions);
            setValuations(detailsResponse.data.valuations);
            // Fetch performance data
            const performanceResponse = await axios.get(`/api/investments/${id}/performance`);
            setPerformance(performanceResponse.data);
        }
        catch (err) {
            console.error('Failed to load investment data', err);
            setError('Failed to load investment data. Please try again.');
        }
        finally {
            setLoading(false);
        }
    };
    if (loading) {
        return (_jsx(PageContainer, { title: "Investment Details", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) }) }));
    }
    if (error || !investment) {
        return (_jsx(PageContainer, { title: "Error", children: _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { children: [_jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg mb-4", children: error || 'Investment not found' }), _jsx(Button, { variant: "outlined", onClick: handleBack, children: "Back to Investments" })] }) }) }));
    }
    return (_jsxs(PageContainer, { title: investment.name, subtitle: `${typeLabels[investment.type] || investment.type} • ${investment.institution}`, actions: _jsxs("div", { className: "flex space-x-3", children: [_jsx(Button, { variant: "outlined", onClick: () => navigate(`/investments/${id}/edit`), icon: _jsx(Edit, { className: "h-4 w-4 mr-2" }), children: "Edit" }), _jsx(Button, { variant: "filled", onClick: () => setShowAddTransactionForm(true), icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: "Record Transaction" }), _jsx(Button, { variant: "outlined", onClick: () => setShowAddValuationForm(true), icon: _jsx(TrendingUp, { className: "h-4 w-4 mr-2" }), children: "Update Valuation" })] }), children: [_jsxs(Tabs, { value: activeTab, onValueChange: setActiveTab, className: "w-full mb-6", children: [_jsxs(TabsList, { className: "grid grid-cols-3", children: [_jsx(TabsTrigger, { value: "overview", children: "Overview" }), _jsxs(TabsTrigger, { value: "transactions", children: ["Transactions", transactions.length > 0 && (_jsx(Badge, { variant: "secondary", className: "ml-2", children: transactions.length }))] }), _jsxs(TabsTrigger, { value: "valuations", children: ["Valuations", valuations.length > 0 && (_jsx(Badge, { variant: "secondary", className: "ml-2", children: valuations.length }))] })] }), _jsx(TabsContent, { value: "overview", className: "mt-4", children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-12 gap-6", children: [_jsxs("div", { className: "md:col-span-8", children: [_jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Investment Details" }), _jsx(CardDescription, { children: "Summary of your investment information" })] }), _jsxs(CardContent, { children: [_jsxs("dl", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(Calendar, { className: "h-4 w-4 mr-1" }), "Start Date"] }), _jsx("dd", { className: "text-on-surface font-medium", children: new Date(investment.start_date).toLocaleDateString() })] }), investment.end_date && (_jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(Calendar, { className: "h-4 w-4 mr-1" }), "End Date"] }), _jsx("dd", { className: "text-on-surface font-medium", children: new Date(investment.end_date).toLocaleDateString() })] })), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(DollarSign, { className: "h-4 w-4 mr-1" }), "Initial Investment"] }), _jsx("dd", { className: "text-on-surface font-medium", children: formatCurrency(investment.initial_investment, 'USD') })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(DollarSign, { className: "h-4 w-4 mr-1" }), "Current Value"] }), _jsx("dd", { className: "text-on-surface font-medium", children: formatCurrency(investment.current_value, 'USD') })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(ArrowUp, { className: "h-4 w-4 mr-1" }), "Total Deposited"] }), _jsx("dd", { className: "text-on-surface font-medium", children: formatCurrency(investment.total_invested, 'USD') })] }), _jsxs("div", { className: "space-y-1", children: [_jsxs("dt", { className: "text-on-surface-variant text-sm flex items-center", children: [_jsx(ArrowDown, { className: "h-4 w-4 mr-1" }), "Total Withdrawn"] }), _jsx("dd", { className: "text-on-surface font-medium", children: formatCurrency(investment.total_withdrawn, 'USD') })] }), investment.account_number && (_jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Account Number" }), _jsx("dd", { className: "text-on-surface font-medium", children: investment.account_number })] })), _jsxs("div", { className: "space-y-1", children: [_jsx("dt", { className: "text-on-surface-variant text-sm", children: "Last Updated" }), _jsx("dd", { className: "text-on-surface font-medium", children: new Date(investment.last_valuation_date).toLocaleDateString() })] })] }), investment.description && (_jsxs("div", { className: "mt-6", children: [_jsxs("h4", { className: "text-on-surface-variant text-sm flex items-center mb-2", children: [_jsx(FileText, { className: "h-4 w-4 mr-1" }), "Description"] }), _jsx("p", { className: "text-on-surface bg-surface-variant p-3 rounded-md", children: investment.description })] }))] })] }), performance && (_jsxs(Card, { variant: "elevated", className: "mt-6", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Performance" }), _jsx(CardDescription, { children: "Value trends and returns" })] }), _jsxs(CardContent, { children: [_jsx("div", { className: "h-48 flex items-center justify-center mb-6", children: _jsx(LineChart, { className: "h-full w-full text-primary opacity-20" }) }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-6", children: [_jsxs("div", { className: "space-y-1", children: [_jsx("span", { className: "text-on-surface-variant text-sm", children: "Return on Investment" }), _jsxs("p", { className: `text-xl font-semibold ${performance.roi >= 0 ? 'text-success' : 'text-error'}`, children: [performance.roi >= 0 ? '+' : '', performance.roi.toFixed(2), "%"] })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("span", { className: "text-on-surface-variant text-sm", children: "Total Return" }), _jsx("p", { className: "text-xl font-semibold", children: formatCurrency(performance.total_return, 'USD') })] }), _jsxs("div", { className: "space-y-1", children: [_jsx("span", { className: "text-on-surface-variant text-sm", children: "Growth" }), _jsx("p", { className: "text-xl font-semibold", children: formatCurrency(performance.current_value - performance.initial_value, 'USD') })] })] })] })] }))] }), _jsxs("div", { className: "md:col-span-4", children: [_jsxs(Card, { variant: "filled", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Current Value" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "flex flex-col items-center py-4", children: [_jsx("div", { className: "text-4xl font-bold mb-4", children: formatCurrency(investment.current_value, 'USD') }), _jsxs("div", { className: "flex items-center space-x-1", children: [_jsxs("div", { className: `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${investment.roi >= 0 ? 'bg-success/10 text-success' : 'bg-error/10 text-error'}`, children: [investment.roi >= 0 ? (_jsx(ArrowUp, { className: "h-3 w-3 mr-1" })) : (_jsx(ArrowDown, { className: "h-3 w-3 mr-1" })), Math.abs(investment.roi).toFixed(2), "%"] }), _jsx("span", { className: "text-on-surface-variant text-xs", children: "since initial investment" })] })] }) })] }), _jsxs(Card, { variant: "outlined", className: "mt-6", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Quick Actions" }) }), _jsx(CardContent, { children: _jsxs("div", { className: "space-y-3", children: [_jsx(Button, { variant: "filled", className: "w-full", onClick: () => setShowAddTransactionForm(true), icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: "Record Transaction" }), _jsx(Button, { variant: "outlined", className: "w-full", onClick: () => setShowAddValuationForm(true), icon: _jsx(TrendingUp, { className: "h-4 w-4 mr-2" }), children: "Update Valuation" }), _jsx(Button, { variant: "text", className: "w-full", onClick: () => navigate(`/investments/${id}/edit`), children: "Edit Investment" })] }) })] })] })] }) }), _jsx(TabsContent, { value: "transactions", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Transaction History" }), _jsx(CardDescription, { children: "Record of deposits, withdrawals, and other transactions" })] }), _jsx(CardContent, { children: transactions.length === 0 ? (_jsxs("div", { className: "text-center py-8", children: [_jsx(DollarSign, { className: "h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" }), _jsx("p", { className: "text-on-surface-variant mb-4", children: "No transactions recorded yet" }), _jsx(Button, { variant: "filled", onClick: () => setShowAddTransactionForm(true), icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: "Record Transaction" })] })) : (_jsxs(_Fragment, { children: [_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Date" }), _jsx(TableHead, { children: "Type" }), _jsx(TableHead, { children: "Amount" }), _jsx(TableHead, { children: "Notes" })] }) }), _jsx(TableBody, { children: transactions
                                                            .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
                                                            .map((transaction) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: new Date(transaction.date).toLocaleDateString() }), _jsx(TableCell, { children: getTransactionBadge(transaction.type) }), _jsxs(TableCell, { className: `font-medium ${transaction.type === 'withdrawal' || transaction.type === 'fee'
                                                                        ? 'text-error'
                                                                        : 'text-success'}`, children: [transaction.type === 'withdrawal' || transaction.type === 'fee'
                                                                            ? '-'
                                                                            : '+', formatCurrency(transaction.amount, 'USD')] }), _jsx(TableCell, { children: transaction.notes || '-' })] }, transaction.id))) })] }), _jsx("div", { className: "mt-6 flex justify-end", children: _jsx(Button, { variant: "filled", onClick: () => setShowAddTransactionForm(true), icon: _jsx(PlusCircle, { className: "h-4 w-4 mr-2" }), children: "Add New Transaction" }) })] })) })] }) }), _jsx(TabsContent, { value: "valuations", className: "mt-4", children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Valuation History" }), _jsx(CardDescription, { children: "Record of value changes over time" })] }), _jsx(CardContent, { children: valuations.length === 0 ? (_jsxs("div", { className: "text-center py-8", children: [_jsx(TrendingUp, { className: "h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" }), _jsx("p", { className: "text-on-surface-variant mb-4", children: "No valuations recorded yet" }), _jsx(Button, { variant: "filled", onClick: () => setShowAddValuationForm(true), icon: _jsx(TrendingUp, { className: "h-4 w-4 mr-2" }), children: "Record Valuation" })] })) : (_jsxs(_Fragment, { children: [_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Date" }), _jsx(TableHead, { children: "Value" }), _jsx(TableHead, { children: "Change" }), _jsx(TableHead, { children: "Notes" })] }) }), _jsx(TableBody, { children: valuations
                                                            .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
                                                            .map((valuation, index, arr) => {
                                                            const prevValue = index < arr.length - 1 ? arr[index + 1].value : null;
                                                            const change = prevValue !== null ? ((valuation.value - prevValue) / prevValue) * 100 : null;
                                                            return (_jsxs(TableRow, { children: [_jsx(TableCell, { children: new Date(valuation.date).toLocaleDateString() }), _jsx(TableCell, { className: "font-medium", children: formatCurrency(valuation.value, 'USD') }), _jsx(TableCell, { children: change !== null ? (_jsxs("span", { className: `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${change >= 0 ? 'bg-success/10 text-success' : 'bg-error/10 text-error'}`, children: [change >= 0 ? (_jsx(ArrowUp, { className: "h-3 w-3 mr-1" })) : (_jsx(ArrowDown, { className: "h-3 w-3 mr-1" })), Math.abs(change).toFixed(2), "%"] })) : ('Initial') }), _jsx(TableCell, { children: valuation.notes || '-' })] }, valuation.id));
                                                        }) })] }), _jsx("div", { className: "mt-6 flex justify-end", children: _jsx(Button, { variant: "filled", onClick: () => setShowAddValuationForm(true), icon: _jsx(TrendingUp, { className: "h-4 w-4 mr-2" }), children: "Add New Valuation" }) })] })) })] }) })] }), _jsx("div", { className: "flex justify-between mt-8", children: _jsx(Button, { variant: "outlined", onClick: handleBack, icon: _jsx(ArrowLeft, { className: "h-4 w-4 mr-2" }), children: "Back to Investments" }) }), showAddTransactionForm && investment && (_jsx(TransactionForm, { investmentId: investment.id, onSuccess: () => {
                    setShowAddTransactionForm(false);
                    refreshData();
                } })), showAddValuationForm && investment && (_jsx(ValuationForm, { investmentId: investment.id, initialValue: investment.current_value, onSuccess: () => {
                    setShowAddValuationForm(false);
                    refreshData();
                } }))] }));
};
export default InvestmentDetailPage;

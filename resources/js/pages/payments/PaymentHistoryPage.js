import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Download, CreditCard, Calendar, ArrowUpRight, TrendingUp, Filter, ExternalLink, AlertCircle } from 'lucide-react';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../../ui/Card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { Badge } from '../../ui/Badge';
import { formatCurrency } from '../../utils/format';
import { useToast } from '../../ui/Toast';
import { usePaymentStore } from '../../store/paymentStore';
import { usePaymentHistory, useSubscriptionsList, useExportPaymentHistory } from '../../queries/paymentQueries';
export default function PaymentHistoryPage() {
    const { toast } = useToast();
    // Get state and actions from store
    const [state, actions] = usePaymentStore();
    const { filters, exportStatus } = state;
    // Use React Query hooks
    const { data: paymentData, isLoading, error: queryError } = usePaymentHistory(filters);
    const { data: subscriptions = [] } = useSubscriptionsList();
    const exportMutation = useExportPaymentHistory();
    // Update store when data is fetched
    useEffect(() => {
        if (paymentData) {
            actions.setPayments(paymentData.payments || []);
            actions.setSummary(paymentData.summary || {
                total_spent: 0,
                payments_count: 0,
                average_payment: 0,
                this_month: 0,
                previous_month: 0
            });
        }
    }, [paymentData, actions]);
    // Update subscriptions in store
    useEffect(() => {
        if (subscriptions.length > 0) {
            actions.setSubscriptions(subscriptions);
        }
    }, [subscriptions, actions]);
    // Handle query error
    useEffect(() => {
        if (queryError) {
            console.error('Error fetching payment history:', queryError);
            toast({
                title: "Error",
                description: "Failed to load payment history. Please try again.",
                variant: "destructive",
            });
            actions.setError('Failed to load payment history. Please try again.');
        }
    }, [queryError, toast, actions]);
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        actions.updateFilter({ name, value });
    };
    const exportToCSV = async () => {
        actions.setExportStatus('loading');
        exportMutation.mutate(filters, {
            onSuccess: (data) => {
                const url = window.URL.createObjectURL(new Blob([data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'payment_history.csv');
                document.body.appendChild(link);
                link.click();
                link.remove();
                actions.setExportStatus('success');
                // Reset status after 3 seconds
                setTimeout(() => {
                    actions.setExportStatus('idle');
                }, 3000);
            },
            onError: (error) => {
                console.error('Error exporting payment history:', error);
                actions.setExportStatus('error');
                // Reset status after 3 seconds
                setTimeout(() => {
                    actions.setExportStatus('idle');
                }, 3000);
            }
        });
    };
    const calculatePercentageChange = () => {
        const { this_month, previous_month } = state.summary;
        if (!previous_month)
            return 0;
        return ((this_month - previous_month) / previous_month) * 100;
    };
    const percentChange = calculatePercentageChange();
    const percentChangeFormatted = isNaN(percentChange) || !isFinite(percentChange)
        ? '+0.0%'
        : `${percentChange > 0 ? '+' : ''}${percentChange.toFixed(1)}%`;
    if (isLoading) {
        return (_jsx(PageContainer, { title: "Payment History", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) }) }));
    }
    return (_jsxs(PageContainer, { title: "Payment History", subtitle: "View and analyze your payment history across all subscriptions", actions: _jsx(Button, { variant: "outlined", onClick: exportToCSV, disabled: exportStatus === 'loading', icon: _jsx(Download, { className: "h-4 w-4 mr-2" }), children: exportStatus === 'loading' ? 'Exporting...' :
                exportStatus === 'success' ? 'Exported!' :
                    exportStatus === 'error' ? 'Failed' :
                        'Export to CSV' }), children: [state.error && (_jsxs("div", { className: "mb-6 p-4 bg-error/10 text-error rounded-lg flex items-center space-x-2", children: [_jsx(AlertCircle, { className: "h-5 w-5" }), _jsx("span", { children: state.error })] })), _jsx(PageSection, { children: _jsxs("div", { className: "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6", children: [_jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { className: "p-6", children: [_jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-primary/10 rounded-full", children: _jsx(CreditCard, { className: "h-6 w-6 text-primary" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Total Spent" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: formatCurrency(state.summary.total_spent, 'USD') })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-primary" })] }), _jsx("p", { className: "text-body-small text-on-surface-variant mt-2 ml-12", children: "Lifetime payment total" })] }) }), _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { className: "p-6", children: [_jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-secondary/10 rounded-full", children: _jsx(Calendar, { className: "h-6 w-6 text-secondary" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Payments Made" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: state.summary.payments_count })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-secondary" })] }), _jsx("p", { className: "text-body-small text-on-surface-variant mt-2 ml-12", children: "Total number of payments" })] }) }), _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { className: "p-6", children: [_jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-tertiary/10 rounded-full", children: _jsx(ArrowUpRight, { className: "h-6 w-6 text-tertiary" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "Average Payment" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: formatCurrency(state.summary.average_payment, 'USD') })] })] }), _jsx(ArrowUpRight, { className: "h-5 w-5 text-tertiary" })] }), _jsx("p", { className: "text-body-small text-on-surface-variant mt-2 ml-12", children: "Average payment amount" })] }) }), _jsx(Card, { variant: "elevated", children: _jsxs(CardContent, { className: "p-6", children: [_jsxs("div", { className: "flex justify-between items-start", children: [_jsxs("div", { className: "flex items-center space-x-4", children: [_jsx("div", { className: "flex-shrink-0 p-3 bg-error/10 rounded-full", children: _jsx(TrendingUp, { className: "h-6 w-6 text-error" }) }), _jsxs("div", { children: [_jsx("p", { className: "text-on-surface-variant text-sm mb-1", children: "This Month" }), _jsx("p", { className: "text-on-surface text-2xl font-bold", children: formatCurrency(state.summary.this_month, 'USD') })] })] }), _jsx("div", { className: `text-xs font-medium ${percentChange > 0 ? 'text-error' : 'text-tertiary'}`, children: percentChangeFormatted })] }), _jsx("p", { className: `text-body-small mt-2 ml-12 ${percentChange > 0 ? 'text-error' : percentChange < 0 ? 'text-tertiary' : 'text-on-surface-variant'}`, children: "vs last month" })] }) })] }) }), _jsx(PageSection, { children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Filter Payments" }), _jsx(CardDescription, { children: "Filter your payment history by subscription and date range" })] }), _jsx(CardContent, { children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-4 gap-4", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "subscription", className: "block text-sm font-medium text-on-surface-variant mb-2", children: "Subscription" }), _jsxs("select", { id: "subscription", name: "subscription_id", value: filters.subscription_id, onChange: handleFilterChange, className: "block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary", children: [_jsx("option", { value: "all", children: "All Subscriptions" }), state.subscriptions.map(sub => (_jsx("option", { value: sub.id, children: sub.name }, sub.id)))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "from_date", className: "block text-sm font-medium text-on-surface-variant mb-2", children: "From Date" }), _jsx("input", { type: "date", id: "from_date", name: "from_date", value: filters.from_date, onChange: handleFilterChange, className: "block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "to_date", className: "block text-sm font-medium text-on-surface-variant mb-2", children: "To Date" }), _jsx("input", { type: "date", id: "to_date", name: "to_date", value: filters.to_date, onChange: handleFilterChange, className: "block w-full rounded-md border border-outline py-2 px-3 bg-surface text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" })] }), _jsx("div", { className: "flex items-end", children: _jsx(Button, { onClick: () => {
                                                // refetch data with current filters
                                                // the query key will handle the refetch for us
                                            }, variant: "filled", className: "w-full", icon: _jsx(Filter, { className: "h-4 w-4 mr-2" }), children: "Filter" }) })] }) })] }) }), _jsx(PageSection, { children: _jsxs(Card, { variant: "elevated", children: [_jsxs(CardHeader, { children: [_jsx(CardTitle, { children: "Payment History" }), _jsx(CardDescription, { children: "Your payment records across all subscriptions" })] }), _jsx(CardContent, { children: state.payments.length === 0 ? (_jsx("div", { className: "p-8 text-center", children: _jsxs("div", { className: "py-12 flex flex-col items-center justify-center border-2 border-dashed border-outline/40 rounded-lg", children: [_jsx("div", { className: "p-3 rounded-full bg-surface-variant mb-4", children: _jsx(CreditCard, { className: "h-8 w-8 text-on-surface-variant/40" }) }), _jsx("h3", { className: "text-headline-small text-on-surface font-medium mb-2", children: "No payment records found" }), _jsx("p", { className: "text-body-medium text-on-surface-variant max-w-md mb-6", children: "Need to record a payment? Visit your subscriptions to record new payments." }), _jsx(Link, { to: "/subscriptions", children: _jsx(Button, { variant: "filled", icon: _jsx(ExternalLink, { className: "h-4 w-4 mr-2" }), children: "View Subscriptions" }) })] }) })) : (_jsxs(Table, { children: [_jsx(TableHeader, { children: _jsxs(TableRow, { children: [_jsx(TableHead, { children: "Date" }), _jsx(TableHead, { children: "Subscription" }), _jsx(TableHead, { children: "Method" }), _jsx(TableHead, { children: "Category" }), _jsx(TableHead, { className: "text-right", children: "Amount" })] }) }), _jsx(TableBody, { children: state.payments.map((payment) => (_jsxs(TableRow, { children: [_jsx(TableCell, { children: new Date(payment.payment_date).toLocaleDateString('en-US', {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric',
                                                    }) }), _jsx(TableCell, { children: payment.subscription_name }), _jsx(TableCell, { children: _jsx(Badge, { variant: "outline", children: payment.payment_method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') }) }), _jsx(TableCell, { children: _jsx(Badge, { variant: "secondary", children: payment.category }) }), _jsx(TableCell, { className: "text-right font-medium", children: formatCurrency(payment.amount, payment.currency) })] }, payment.id))) })] })) })] }) }), exportStatus === 'success' && (_jsxs("div", { className: "fixed bottom-4 right-4 bg-success text-on-success px-4 py-2 rounded-md shadow-lg flex items-center space-x-2", children: [_jsx(Download, { className: "h-4 w-4" }), _jsx("span", { children: "Export completed successfully!" })] })), exportStatus === 'error' && (_jsxs("div", { className: "fixed bottom-4 right-4 bg-error text-on-error px-4 py-2 rounded-md shadow-lg flex items-center space-x-2", children: [_jsx(AlertCircle, { className: "h-4 w-4" }), _jsx("span", { children: "Export failed. Please try again." })] }))] }));
}

import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';
import SubscriptionManager from '../../components/subscriptions/SubscriptionManager';
import { useSubscriptionStore } from '../../store/subscriptionStore';
import { useSubscriptions, useSubscriptionCategories, useCancelSubscription } from '../../queries/subscriptionQueries';
const SubscriptionsList = () => {
    var _a;
    const navigate = useNavigate();
    const [state, actions] = useSubscriptionStore();
    const { filters } = state;
    // Convert our store filters to query params
    const queryParams = {
        status: filters.status === 'all' ? '' : filters.status,
        category: filters.category === 'all' ? '' : filters.category,
        sort_by: filters.sort_by
    };
    // Use React Query hooks
    const { data: subscriptionsData, isLoading, error: queryError } = useSubscriptions(queryParams);
    const { data: categories = [] } = useSubscriptionCategories();
    const cancelSubscriptionMutation = useCancelSubscription();
    // Update error state from query
    useEffect(() => {
        if (queryError) {
            actions.setError('Failed to load subscriptions');
            console.error(queryError);
        }
        else {
            actions.setError(null);
        }
    }, [queryError, actions]);
    // Extract and transform subscription data for the UI
    const subscriptions = ((_a = subscriptionsData === null || subscriptionsData === void 0 ? void 0 : subscriptionsData.data) === null || _a === void 0 ? void 0 : _a.map(sub => ({
        id: sub.id,
        name: sub.name,
        description: sub.description,
        status: sub.status === 'active' ? 'active' :
            sub.status === 'cancelled' ? 'canceled' :
                sub.status === 'paused' ? 'past_due' : 'inactive',
        currentPeriodEnd: sub.next_payment_date || undefined,
        price: sub.amount,
        interval: sub.billing_cycle === 'monthly' ? 'month' :
            sub.billing_cycle === 'annually' ? 'year' :
                sub.billing_cycle === 'weekly' ? 'week' : 'day',
        currency: sub.currency,
        features: sub.category ? [sub.category] : undefined
    }))) || [];
    // Extract pagination meta
    const meta = (subscriptionsData === null || subscriptionsData === void 0 ? void 0 : subscriptionsData.meta) || {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    };
    const handlePageChange = (page) => {
        // React Query will handle refetching when we change the page param
        // This would ideally be reflected in the URL as well
    };
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        actions.updateFilter({ name, value });
    };
    const handleSearch = (e) => {
        e.preventDefault();
        // The query key change will trigger a refetch
    };
    const handleManageSubscription = (subscription) => {
        navigate(`/subscriptions/${subscription.id}`);
    };
    const handleCancelSubscription = async (subscription) => {
        const endDate = new Date().toISOString().split('T')[0]; // Today's date
        cancelSubscriptionMutation.mutate({ subscriptionId: subscription.id, endDate }, {
            onError: (error) => {
                console.error('Error cancelling subscription:', error);
                actions.setError('Failed to cancel subscription');
            }
        });
    };
    const handleRenewSubscription = (subscription) => {
        // Navigate to edit page where the user can update the subscription
        navigate(`/subscriptions/${subscription.id}/edit`);
    };
    return (_jsxs(PageContainer, { title: "Subscriptions", subtitle: "Manage your recurring subscriptions and track upcoming payments", actions: _jsx(Link, { to: "/subscriptions/create", children: _jsx(Button, { variant: "filled", children: "Add Subscription" }) }), children: [_jsxs(Card, { variant: "elevated", className: "mb-6", children: [_jsx(CardHeader, { children: _jsx(CardTitle, { children: "Filter Subscriptions" }) }), _jsx(CardContent, { children: _jsxs("form", { onSubmit: handleSearch, className: "grid grid-cols-1 md:grid-cols-4 gap-4", children: [_jsxs("div", { children: [_jsx("label", { htmlFor: "status", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Status" }), _jsxs("select", { id: "status", name: "status", value: filters.status, onChange: handleFilterChange, className: "w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface", children: [_jsx("option", { value: "all", children: "All Statuses" }), _jsx("option", { value: "active", children: "Active" }), _jsx("option", { value: "cancelled", children: "Cancelled" })] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "category", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Category" }), _jsxs("select", { id: "category", name: "category", value: filters.category, onChange: handleFilterChange, className: "w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface", children: [_jsx("option", { value: "all", children: "All Categories" }), categories.map(category => (_jsx("option", { value: category, children: category }, category)))] })] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "sort_by", className: "block text-sm font-medium text-on-surface-variant mb-1", children: "Sort By" }), _jsxs("select", { id: "sort_by", name: "sort_by", value: filters.sort_by, onChange: handleFilterChange, className: "w-full border border-outline border-opacity-30 rounded-md shadow-sm p-2 bg-surface text-on-surface", children: [_jsx("option", { value: "name", children: "Name" }), _jsx("option", { value: "amount", children: "Amount" }), _jsx("option", { value: "next_payment_date", children: "Next Payment" })] })] }), _jsx("div", { className: "flex items-end", children: _jsx(Button, { type: "submit", variant: "tonal", className: "w-full", children: "Filter" }) })] }) })] }), isLoading ? (_jsx("div", { className: "flex justify-center py-10", children: _jsxs("div", { className: "animate-pulse text-center", children: [_jsx("div", { className: "h-10 w-40 bg-surface-variant rounded mx-auto mb-4" }), _jsx("div", { className: "h-4 w-60 bg-surface-variant rounded mx-auto" })] }) })) : state.error ? (_jsx("div", { className: "bg-error-container border border-error text-on-error-container p-4 rounded-lg", children: state.error })) : (_jsxs(_Fragment, { children: [_jsx(SubscriptionManager, { subscriptions: subscriptions, onManageSubscription: handleManageSubscription, onCancelSubscription: handleCancelSubscription, onRenewSubscription: handleRenewSubscription }), meta.total > 0 && meta.last_page > 1 && (_jsx("div", { className: "mt-8 flex justify-center", children: _jsx("div", { className: "flex space-x-2", children: Array.from({ length: meta.last_page }, (_, i) => i + 1).map(page => (_jsx("button", { onClick: () => handlePageChange(page), className: `px-3 py-1 rounded-md ${meta.current_page === page
                                    ? 'bg-primary text-on-primary'
                                    : 'bg-surface-variant text-on-surface-variant'}`, children: page }, page))) }) })), subscriptions.length === 0 && (_jsxs("div", { className: "text-center py-8 p-10 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1", children: [_jsx("p", { className: "text-headline-small text-on-surface-variant mb-4", children: "You don't have any subscriptions yet." }), _jsx(Link, { to: "/subscriptions/create", children: _jsx(Button, { variant: "filled", children: "Add Subscription" }) })] }))] }))] }));
};
export default SubscriptionsList;

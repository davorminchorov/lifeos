import { jsx as _jsx } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import SubscriptionManager from '../components/subscriptions/SubscriptionManager';
import { PageContainer } from '../ui/PageContainer';
const SubscriptionsPage = () => {
    const [subscriptions, setSubscriptions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        // In a real app, this would be fetched from your backend
        const fetchSubscriptions = async () => {
            try {
                setLoading(true);
                // Replace with actual API endpoint
                // const response = await axios.get('/api/subscriptions');
                // setSubscriptions(response.data);
                // For demo purposes, we'll use mock data
                setSubscriptions([
                    {
                        id: '1',
                        name: 'Premium Plan',
                        description: 'Access to all premium features',
                        status: 'active',
                        currentPeriodEnd: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
                        price: 29.99,
                        interval: 'month',
                        currency: 'USD',
                        features: [
                            'Unlimited access',
                            'Priority support',
                            'Advanced analytics',
                            'Custom branding'
                        ]
                    },
                    {
                        id: '2',
                        name: 'Basic Plan',
                        description: 'Essential features for individuals',
                        status: 'canceled',
                        currentPeriodEnd: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000).toISOString(),
                        price: 9.99,
                        interval: 'month',
                        currency: 'USD',
                        features: [
                            'Basic access',
                            'Standard support',
                            'Limited analytics'
                        ]
                    }
                ]);
                setError(null);
            }
            catch (err) {
                setError('Failed to load subscriptions. Please try again later.');
                console.error('Error fetching subscriptions:', err);
            }
            finally {
                setLoading(false);
            }
        };
        fetchSubscriptions();
    }, []);
    const handleManageSubscription = (subscription) => {
        console.log('Managing subscription:', subscription);
        // Implement subscription management logic or navigation
    };
    const handleCancelSubscription = (subscription) => {
        console.log('Canceling subscription:', subscription);
        // In a real app, you would call your backend to cancel the subscription
        // For demo, we'll just update the local state
        setSubscriptions(prevSubscriptions => prevSubscriptions.map(sub => sub.id === subscription.id
            ? Object.assign(Object.assign({}, sub), { status: 'canceled' }) : sub));
    };
    const handleRenewSubscription = (subscription) => {
        console.log('Renewing subscription:', subscription);
        // In a real app, you would call your backend to renew the subscription
        // For demo, we'll just update the local state
        setSubscriptions(prevSubscriptions => prevSubscriptions.map(sub => sub.id === subscription.id
            ? Object.assign(Object.assign({}, sub), { status: 'active', currentPeriodEnd: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString() }) : sub));
    };
    return (_jsx(PageContainer, { title: "My Subscriptions", children: loading ? (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) })) : error ? (_jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg", children: error })) : (_jsx(SubscriptionManager, { subscriptions: subscriptions, onManageSubscription: handleManageSubscription, onCancelSubscription: handleCancelSubscription, onRenewSubscription: handleRenewSubscription })) }));
};
export default SubscriptionsPage;

import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import SubscriptionForm from '../../components/subscriptions/SubscriptionForm';
import { Button } from '../../ui';
const EditSubscription = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [subscription, setSubscription] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        const fetchSubscription = async () => {
            setLoading(true);
            try {
                const response = await axios.get(`/api/subscriptions/${id}`);
                setSubscription(response.data);
                setError(null);
            }
            catch (err) {
                setError('Failed to load subscription details');
                console.error(err);
            }
            finally {
                setLoading(false);
            }
        };
        fetchSubscription();
    }, [id]);
    if (loading) {
        return (_jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin" }) }));
    }
    if (error || !subscription) {
        return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: [_jsx("div", { className: "bg-error-container border border-error text-on-error-container px-4 py-3 rounded", children: error || 'Subscription not found' }), _jsx("div", { className: "mt-4", children: _jsx(Button, { onClick: () => navigate('/subscriptions'), variant: "filled", children: "Back to subscriptions" }) })] }));
    }
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-6xl", children: [_jsx("h1", { className: "text-3xl font-bold mb-6 text-on-surface", children: "Edit Subscription" }), _jsx(SubscriptionForm, { initialData: subscription, isEditing: true })] }));
};
export default EditSubscription;

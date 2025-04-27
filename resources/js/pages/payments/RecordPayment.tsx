import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import PaymentForm from '../../components/payments/PaymentForm';
import Button from '../../ui/Button/Button';
import Card from '../../ui/Card/Card';

interface Subscription {
  id: string;
  name: string;
  amount: number;
  currency: string;
}

const RecordPayment: React.FC = () => {
  const { subscriptionId } = useParams<{ subscriptionId: string }>();
  const navigate = useNavigate();
  const [subscription, setSubscription] = useState<Subscription | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchSubscription = async () => {
      if (!subscriptionId) {
        setError('No subscription ID provided');
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        const response = await axios.get(`/api/subscriptions/${subscriptionId}`);
        setSubscription({
          id: response.data.id,
          name: response.data.name,
          amount: response.data.amount,
          currency: response.data.currency
        });
        setError(null);
      } catch (err) {
        setError('Failed to load subscription details');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchSubscription();
  }, [subscriptionId]);

  const handlePaymentSuccess = () => {
    navigate(`/subscriptions/${subscriptionId}`);
  };

  const handleCancel = () => {
    navigate(`/subscriptions/${subscriptionId}`);
  };

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex justify-center items-center h-64">
          <svg className="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    );
  }

  if (error || !subscription) {
    return (
      <div className="container mx-auto px-4 py-8">
        <Card className="p-6">
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {error || 'Subscription not found'}
          </div>
          <div>
            <Button onClick={() => navigate('/subscriptions')}>Back to subscriptions</Button>
          </div>
        </Card>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-2xl">
      <h1 className="text-2xl font-bold mb-6">Record Payment</h1>
      <PaymentForm
        subscriptionId={subscription.id}
        subscriptionName={subscription.name}
        defaultAmount={subscription.amount}
        currency={subscription.currency}
        onSuccess={handlePaymentSuccess}
        onCancel={handleCancel}
      />
    </div>
  );
};

export default RecordPayment;

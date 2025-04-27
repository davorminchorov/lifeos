import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import SubscriptionForm from '../../components/subscriptions/SubscriptionForm';
import Button from '../../ui/Button/Button';

interface Subscription {
  id: string;
  name: string;
  description: string;
  amount: number;
  currency: string;
  billing_cycle: string;
  start_date: string;
  website?: string;
  category?: string;
}

const EditSubscription: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [subscription, setSubscription] = useState<Subscription | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchSubscription = async () => {
      setLoading(true);
      try {
        const response = await axios.get(`/api/subscriptions/${id}`);
        setSubscription(response.data);
        setError(null);
      } catch (err) {
        setError('Failed to load subscription details');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchSubscription();
  }, [id]);

  if (loading) {
    return <div className="flex justify-center items-center h-64">Loading...</div>;
  }

  if (error || !subscription) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error || 'Subscription not found'}
        </div>
        <div className="mt-4">
          <Button onClick={() => navigate('/subscriptions')}>Back to subscriptions</Button>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Edit Subscription</h1>
      <SubscriptionForm
        initialData={subscription}
        isEditing={true}
      />
    </div>
  );
};

export default EditSubscription;

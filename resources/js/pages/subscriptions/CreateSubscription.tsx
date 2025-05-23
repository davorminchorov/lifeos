import React from 'react';
import SubscriptionForm from '../../components/subscriptions/SubscriptionForm';

const CreateSubscription: React.FC = () => {
  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <h1 className="text-3xl font-bold mb-6 text-on-surface">Add New Subscription</h1>
      <SubscriptionForm />
    </div>
  );
};

export default CreateSubscription;

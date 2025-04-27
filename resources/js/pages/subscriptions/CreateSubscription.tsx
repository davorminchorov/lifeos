import React from 'react';
import SubscriptionForm from '../../components/subscriptions/SubscriptionForm';

const CreateSubscription: React.FC = () => {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Add New Subscription</h1>
      <SubscriptionForm />
    </div>
  );
};

export default CreateSubscription;

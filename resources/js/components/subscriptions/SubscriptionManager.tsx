import React, { useState } from 'react';
import SubscriptionCard, { Subscription } from './SubscriptionCard';

interface SubscriptionManagerProps {
  subscriptions: Subscription[];
  onManageSubscription?: (subscription: Subscription) => void;
  onCancelSubscription?: (subscription: Subscription) => void;
  onRenewSubscription?: (subscription: Subscription) => void;
}

const SubscriptionManager: React.FC<SubscriptionManagerProps> = ({
  subscriptions,
  onManageSubscription,
  onCancelSubscription,
  onRenewSubscription,
}) => {
  const [activeSubscriptions, setActiveSubscriptions] = useState<Subscription[]>(
    subscriptions.filter(sub =>
      sub.status === 'active' || sub.status === 'trialing'
    )
  );

  const [inactiveSubscriptions, setInactiveSubscriptions] = useState<Subscription[]>(
    subscriptions.filter(sub =>
      sub.status !== 'active' && sub.status !== 'trialing'
    )
  );

  const handleManageSubscription = (subscription: Subscription) => {
    if (onManageSubscription) {
      onManageSubscription(subscription);
    }
  };

  const handleCancelSubscription = (subscription: Subscription) => {
    if (onCancelSubscription) {
      onCancelSubscription(subscription);
    }
  };

  const handleRenewSubscription = (subscription: Subscription) => {
    if (onRenewSubscription) {
      onRenewSubscription(subscription);
    }
  };

  return (
    <div className="space-y-8">
      {activeSubscriptions.length > 0 && (
        <div>
          <h2 className="text-2xl font-semibold text-on-surface mb-4">Active Subscriptions</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {activeSubscriptions.map(subscription => (
              <SubscriptionCard
                key={subscription.id}
                subscription={subscription}
                onManage={() => handleManageSubscription(subscription)}
                onCancel={() => handleCancelSubscription(subscription)}
              />
            ))}
          </div>
        </div>
      )}

      {inactiveSubscriptions.length > 0 && (
        <div>
          <h2 className="text-2xl font-semibold text-on-surface mb-4">Inactive Subscriptions</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {inactiveSubscriptions.map(subscription => (
              <SubscriptionCard
                key={subscription.id}
                subscription={subscription}
                onManage={() => handleManageSubscription(subscription)}
                onRenew={() => handleRenewSubscription(subscription)}
              />
            ))}
          </div>
        </div>
      )}

      {activeSubscriptions.length === 0 && inactiveSubscriptions.length === 0 && (
        <div className="text-center py-8">
          <p className="text-xl text-on-surface-variant">You don't have any subscriptions yet.</p>
          <button className="mt-4 px-6 py-3 bg-primary text-on-primary rounded-full font-medium">
            Browse Plans
          </button>
        </div>
      )}
    </div>
  );
};

export default SubscriptionManager;

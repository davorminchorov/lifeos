import React from 'react';

export interface Subscription {
  id: string;
  name: string;
  description?: string;
  status: 'active' | 'canceled' | 'past_due' | 'trialing' | 'inactive';
  currentPeriodEnd?: string;
  price: number;
  interval?: 'month' | 'year' | 'week' | 'day';
  currency: string;
  features?: string[];
}

interface SubscriptionCardProps {
  subscription: Subscription;
  onManage?: () => void;
  onCancel?: () => void;
  onRenew?: () => void;
}

const SubscriptionCard: React.FC<SubscriptionCardProps> = ({
  subscription,
  onManage,
  onCancel,
  onRenew,
}) => {
  const {
    name,
    description,
    status,
    currentPeriodEnd,
    price,
    interval,
    currency,
    features,
  } = subscription;

  const formatCurrency = (amount: number, currencyCode: string) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currencyCode,
    }).format(amount);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
      case 'trialing':
        return 'text-success';
      case 'past_due':
        return 'text-warning';
      case 'canceled':
      case 'inactive':
        return 'text-error';
      default:
        return 'text-on-surface-variant';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'active':
        return 'Active';
      case 'trialing':
        return 'Trial';
      case 'past_due':
        return 'Past Due';
      case 'canceled':
        return 'Canceled';
      case 'inactive':
        return 'Inactive';
      default:
        return 'Unknown';
    }
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const formatInterval = (price: number, interval?: string) => {
    if (!interval) return formatCurrency(price, currency);

    switch (interval) {
      case 'month':
        return `${formatCurrency(price, currency)}/month`;
      case 'year':
        return `${formatCurrency(price, currency)}/year`;
      case 'week':
        return `${formatCurrency(price, currency)}/week`;
      case 'day':
        return `${formatCurrency(price, currency)}/day`;
      default:
        return formatCurrency(price, currency);
    }
  };

  const isActive = status === 'active' || status === 'trialing';

  return (
    <div className="bg-surface rounded-lg shadow-elevation-1 overflow-hidden">
      <div className="p-6">
        <div className="flex justify-between items-start">
          <div>
            <h3 className="text-xl font-semibold text-on-surface">{name}</h3>
            {description && <p className="mt-1 text-on-surface-variant">{description}</p>}
          </div>
          <div className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(status)}`}>
            {getStatusLabel(status)}
          </div>
        </div>

        <div className="mt-4 flex flex-wrap gap-5">
          <div>
            <p className="text-sm text-on-surface-variant">Price</p>
            <p className="font-medium text-on-surface">{formatInterval(price, interval)}</p>
          </div>

          {currentPeriodEnd && isActive && (
            <div>
              <p className="text-sm text-on-surface-variant">Renews on</p>
              <p className="font-medium text-on-surface">{formatDate(currentPeriodEnd)}</p>
            </div>
          )}
        </div>

        {features && features.length > 0 && (
          <div className="mt-5">
            <p className="text-sm font-medium text-on-surface-variant mb-2">Included Features</p>
            <ul className="list-disc list-inside text-on-surface space-y-1">
              {features.map((feature, index) => (
                <li key={index} className="text-sm">{feature}</li>
              ))}
            </ul>
          </div>
        )}

        <div className="mt-6 flex flex-wrap gap-3">
          {onManage && (
            <button
              onClick={onManage}
              className="px-4 py-2 bg-primary text-on-primary rounded-full text-sm font-medium"
            >
              Manage Subscription
            </button>
          )}

          {isActive && onCancel && (
            <button
              onClick={onCancel}
              className="px-4 py-2 border border-outline text-on-surface rounded-full text-sm font-medium"
            >
              Cancel Subscription
            </button>
          )}

          {!isActive && onRenew && (
            <button
              onClick={onRenew}
              className="px-4 py-2 bg-primary text-on-primary rounded-full text-sm font-medium"
            >
              Renew Subscription
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default SubscriptionCard;

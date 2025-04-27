import React from 'react';

export interface BillingInfo {
  paymentMethod?: {
    type: string;
    last4?: string;
    expiryDate?: string;
    brand?: string;
  };
  billingAddress?: {
    name: string;
    addressLine1: string;
    addressLine2?: string;
    city: string;
    state?: string;
    postalCode: string;
    country: string;
  };
}

interface BillingInfoCardProps {
  billingInfo: BillingInfo;
  onUpdatePaymentMethod?: () => void;
  onUpdateBillingAddress?: () => void;
}

const BillingInfoCard: React.FC<BillingInfoCardProps> = ({
  billingInfo,
  onUpdatePaymentMethod,
  onUpdateBillingAddress,
}) => {
  const { paymentMethod, billingAddress } = billingInfo;

  const getCardIcon = (brand?: string) => {
    switch (brand?.toLowerCase()) {
      case 'visa':
        return '💳 Visa';
      case 'mastercard':
        return '💳 Mastercard';
      case 'amex':
      case 'american express':
        return '💳 American Express';
      default:
        return '💳 Card';
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h3 className="text-xl font-semibold text-on-surface mb-3">Payment Method</h3>
        {paymentMethod ? (
          <div className="bg-surface-variant/30 p-4 rounded-lg">
            <div className="flex justify-between items-start">
              <div>
                <p className="font-medium text-on-surface">
                  {getCardIcon(paymentMethod.brand)}
                  {paymentMethod.last4 && ` •••• ${paymentMethod.last4}`}
                </p>
                {paymentMethod.expiryDate && (
                  <p className="text-sm text-on-surface-variant mt-1">
                    Expires {paymentMethod.expiryDate}
                  </p>
                )}
              </div>
              {onUpdatePaymentMethod && (
                <button
                  onClick={onUpdatePaymentMethod}
                  className="text-sm text-primary hover:text-primary/80 font-medium"
                >
                  Update
                </button>
              )}
            </div>
          </div>
        ) : (
          <div className="border-2 border-dashed border-outline/30 p-4 rounded-lg text-center">
            <p className="text-on-surface-variant">No payment method on file</p>
            {onUpdatePaymentMethod && (
              <button
                onClick={onUpdatePaymentMethod}
                className="mt-2 text-sm text-primary hover:text-primary/80 font-medium"
              >
                Add Payment Method
              </button>
            )}
          </div>
        )}
      </div>

      <div>
        <h3 className="text-xl font-semibold text-on-surface mb-3">Billing Address</h3>
        {billingAddress ? (
          <div className="bg-surface-variant/30 p-4 rounded-lg">
            <div className="flex justify-between items-start">
              <div>
                <p className="font-medium text-on-surface">{billingAddress.name}</p>
                <div className="text-sm text-on-surface-variant mt-1 space-y-0.5">
                  <p>{billingAddress.addressLine1}</p>
                  {billingAddress.addressLine2 && <p>{billingAddress.addressLine2}</p>}
                  <p>
                    {billingAddress.city}
                    {billingAddress.state && `, ${billingAddress.state}`} {billingAddress.postalCode}
                  </p>
                  <p>{billingAddress.country}</p>
                </div>
              </div>
              {onUpdateBillingAddress && (
                <button
                  onClick={onUpdateBillingAddress}
                  className="text-sm text-primary hover:text-primary/80 font-medium"
                >
                  Update
                </button>
              )}
            </div>
          </div>
        ) : (
          <div className="border-2 border-dashed border-outline/30 p-4 rounded-lg text-center">
            <p className="text-on-surface-variant">No billing address on file</p>
            {onUpdateBillingAddress && (
              <button
                onClick={onUpdateBillingAddress}
                className="mt-2 text-sm text-primary hover:text-primary/80 font-medium"
              >
                Add Billing Address
              </button>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default BillingInfoCard;

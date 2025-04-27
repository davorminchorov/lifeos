import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';

interface PaymentSummaryCardProps {
  amount: number;
  currency: string;
  billingCycle: string;
  nextPaymentDate: string | null;
  totalPaid: number;
  startDate: string;
  paymentCount: number;
}

const PaymentSummaryCard: React.FC<PaymentSummaryCardProps> = ({
  amount,
  currency,
  billingCycle,
  nextPaymentDate,
  totalPaid,
  startDate,
  paymentCount,
}) => {
  const formatBillingCycle = (cycle: string) => {
    if (!cycle) return 'Unknown';
    return cycle.charAt(0).toUpperCase() + cycle.slice(1);
  };

  const getAnnualCost = () => {
    let multiplier = 1;
    switch (billingCycle.toLowerCase()) {
      case 'monthly':
        multiplier = 12;
        break;
      case 'weekly':
        multiplier = 52;
        break;
      case 'quarterly':
        multiplier = 4;
        break;
      case 'biannually':
        multiplier = 2;
        break;
      case 'annually':
      default:
        multiplier = 1;
        break;
    }
    return amount * multiplier;
  };

  const formatStartDate = () => {
    if (!startDate) return 'Not specified';
    return new Date(startDate).toLocaleDateString();
  };

  return (
    <div className="space-y-5">
      <div>
        <h3 className="text-sm font-medium text-on-surface-variant">Per {formatBillingCycle(billingCycle)}</h3>
        <p className="text-3xl font-bold text-on-surface mt-1">
          {formatCurrency(amount, currency)}
        </p>
      </div>

      {billingCycle.toLowerCase() !== 'annually' && (
        <div>
          <h3 className="text-sm font-medium text-on-surface-variant">Annual Cost</h3>
          <p className="text-xl font-semibold text-on-surface mt-1">
            {formatCurrency(getAnnualCost(), currency)}
          </p>
        </div>
      )}

      <div className="pt-2 border-t border-outline/20">
        <h3 className="text-sm font-medium text-on-surface-variant">Next Payment</h3>
        <p className="text-base text-on-surface mt-1">
          {nextPaymentDate
            ? new Date(nextPaymentDate).toLocaleDateString()
            : 'Not scheduled'
          }
        </p>
      </div>

      <div>
        <h3 className="text-sm font-medium text-on-surface-variant">Since</h3>
        <p className="text-base text-on-surface mt-1">
          {formatStartDate()}
        </p>
      </div>

      <div className="pt-2 border-t border-outline/20">
        <h3 className="text-sm font-medium text-on-surface-variant">Total Paid</h3>
        <div className="flex justify-between items-center mt-1">
          <p className="text-xl font-semibold text-on-surface">
            {formatCurrency(totalPaid, currency)}
          </p>
          <span className="text-sm text-on-surface-variant">
            {paymentCount} payment{paymentCount !== 1 ? 's' : ''}
          </span>
        </div>
      </div>
    </div>
  );
};

export default PaymentSummaryCard;

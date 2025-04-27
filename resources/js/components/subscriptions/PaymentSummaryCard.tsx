import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';

interface PaymentSummaryCardProps {
  startDate: string;
  nextPaymentDate: string | null;
  amount: number;
  currency: string;
  billingCycle: string;
  totalPaid: number;
  paymentCount: number;
}

const PaymentSummaryCard: React.FC<PaymentSummaryCardProps> = ({
  startDate,
  nextPaymentDate,
  amount,
  currency,
  billingCycle,
  totalPaid,
  paymentCount,
}) => {
  const formatBillingCycle = (cycle: string): string => {
    return cycle.charAt(0).toUpperCase() + cycle.slice(1);
  };

  return (
    <Card>
      <div className="px-6 py-4 border-b border-gray-200">
        <h3 className="text-lg font-medium text-gray-900">Payment Summary</h3>
      </div>

      <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div className="space-y-4">
            <div>
              <p className="text-sm text-gray-500">Current Amount</p>
              <p className="text-xl font-semibold">{formatCurrency(amount, currency)}</p>
            </div>

            <div>
              <p className="text-sm text-gray-500">Billing Cycle</p>
              <p>{formatBillingCycle(billingCycle)}</p>
            </div>

            <div>
              <p className="text-sm text-gray-500">Start Date</p>
              <p>{formatDate(startDate)}</p>
            </div>
          </div>
        </div>

        <div>
          <div className="space-y-4">
            {nextPaymentDate && (
              <div>
                <p className="text-sm text-gray-500">Next Payment Due</p>
                <p className="font-medium">
                  {formatDate(nextPaymentDate)}
                </p>
              </div>
            )}

            <div>
              <p className="text-sm text-gray-500">Total Paid to Date</p>
              <p className="text-xl font-semibold text-green-600">
                {formatCurrency(totalPaid, currency)}
              </p>
            </div>

            <div>
              <p className="text-sm text-gray-500">Number of Payments</p>
              <p>{paymentCount} {paymentCount === 1 ? 'payment' : 'payments'}</p>
            </div>
          </div>
        </div>
      </div>
    </Card>
  );
};

export default PaymentSummaryCard;

import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';

export interface Payment {
  id: number;
  amount: number;
  date: string;
  status: string;
  reference?: string;
}

interface PaymentHistoryCardProps {
  payments: Payment[];
  currency: string;
}

const PaymentHistoryCard: React.FC<PaymentHistoryCardProps> = ({ payments, currency }) => {
  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'paid':
      case 'completed':
        return 'text-tertiary';
      case 'pending':
        return 'text-secondary';
      case 'failed':
      case 'declined':
        return 'text-error';
      default:
        return 'text-on-surface-variant';
    }
  };

  return (
    <div className="space-y-4">
      <h3 className="text-xl font-semibold text-on-surface">Payment History</h3>

      {payments.length === 0 ? (
        <div className="py-8 flex flex-col items-center justify-center text-center border-2 border-dashed border-outline/30 rounded-lg">
          <p className="text-on-surface-variant">No payment records found</p>
        </div>
      ) : (
        <div className="divide-y divide-outline/20">
          {payments.map((payment) => (
            <div key={payment.id} className="py-3 first:pt-0 last:pb-0">
              <div className="flex justify-between items-center">
                <div className="space-y-1">
                  <p className="font-medium text-on-surface">
                    {formatDate(payment.date)}
                  </p>
                  {payment.reference && (
                    <p className="text-sm text-on-surface-variant">
                      Ref: {payment.reference}
                    </p>
                  )}
                </div>
                <div className="text-right space-y-1">
                  <p className="font-semibold text-on-surface">
                    {formatCurrency(payment.amount, currency)}
                  </p>
                  <p className={`text-sm ${getStatusColor(payment.status)}`}>
                    {payment.status}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default PaymentHistoryCard;

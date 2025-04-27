import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';

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
        return 'bg-tertiary-container text-on-tertiary-container';
      case 'pending':
        return 'bg-secondary-container text-on-secondary-container';
      case 'failed':
      case 'declined':
        return 'bg-error-container text-on-error-container';
      default:
        return 'bg-surface-variant text-on-surface-variant';
    }
  };

  return (
    <Card className="shadow-elevation-2 border border-outline/40">
      <div className="px-6 py-4 border-b border-outline-variant/60">
        <h3 className="text-headline-small font-medium text-on-surface">Payment History</h3>
      </div>

      <div className="p-6">
        {payments.length === 0 ? (
          <div className="py-8 flex flex-col items-center justify-center text-center border-2 border-dashed border-outline/40 rounded-lg bg-surface-container">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-12 w-12 text-on-surface-variant/40 mb-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={1.5}
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"
              />
            </svg>
            <p className="text-body-medium text-on-surface-variant">No payment records found</p>
          </div>
        ) : (
          <div className="divide-y divide-outline/40">
            {payments.map((payment) => (
              <div key={payment.id} className="py-4 first:pt-0 last:pb-0">
                <div className="flex justify-between items-center">
                  <div className="space-y-1">
                    <p className="text-body-large font-medium text-on-surface">
                      {formatDate(payment.date)}
                    </p>
                    {payment.reference && (
                      <p className="text-body-small text-on-surface-variant">
                        Ref: {payment.reference}
                      </p>
                    )}
                  </div>
                  <div className="text-right space-y-2">
                    <p className="text-body-large font-medium text-on-surface">
                      {formatCurrency(payment.amount, currency)}
                    </p>
                    <span className={`text-label-small px-2 py-1 rounded-full font-medium inline-block shadow-elevation-1 ${getStatusColor(payment.status)}`}>
                      {payment.status}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </Card>
  );
};

export default PaymentHistoryCard;

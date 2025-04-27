import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';

interface Payment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  payment_method: string;
  category: string;
  notes?: string;
  created_at: string;
}

interface PaymentHistoryCardProps {
  payments: Payment[];
  currency: string;
  onAddPayment?: () => void;
  showEmptyState?: boolean;
  title?: string;
}

const PaymentHistoryCard: React.FC<PaymentHistoryCardProps> = ({
  payments,
  currency,
  onAddPayment,
  showEmptyState = true,
  title = 'Payment History',
}) => {
  const sortedPayments = [...payments].sort(
    (a, b) => new Date(b.payment_date).getTime() - new Date(a.payment_date).getTime()
  );

  const formatPaymentMethod = (method: string) => {
    return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  };

  return (
    <Card className="shadow-elevation-2 border border-outline/40">
      <div className="px-6 py-4 border-b border-outline-variant/60 flex justify-between items-center">
        <h3 className="text-headline-small font-medium text-on-surface">{title}</h3>
        {onAddPayment && (
          <Button onClick={onAddPayment} size="sm">
            Add Payment
          </Button>
        )}
      </div>

      <div className="p-6">
        {payments.length === 0 ? (
          showEmptyState ? (
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
              <p className="text-body-medium text-on-surface-variant mb-4">No payment records found</p>
              {onAddPayment && (
                <Button onClick={onAddPayment} size="sm">
                  Record Your First Payment
                </Button>
              )}
            </div>
          ) : (
            <div className="p-6 text-center text-body-medium text-on-surface-variant">No payment records available.</div>
          )
        ) : (
          <div className="divide-y divide-outline/40">
            {sortedPayments.map((payment) => (
              <div key={payment.id} className="py-4 first:pt-0 last:pb-0">
                <div className="flex justify-between items-start">
                  <div className="space-y-1">
                    <p className="text-body-large font-medium text-on-surface">{formatDate(payment.payment_date)}</p>
                    <p className="text-body-small text-on-surface-variant">{formatPaymentMethod(payment.payment_method)}</p>
                    <p className="text-body-small text-on-surface-variant">{payment.category}</p>
                  </div>
                  <div className="text-right space-y-1">
                    <p className="text-body-large font-medium text-on-surface">{formatCurrency(payment.amount, currency)}</p>
                    {payment.notes && (
                      <p className="text-body-small text-on-surface-variant line-clamp-2 max-w-xs">{payment.notes}</p>
                    )}
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

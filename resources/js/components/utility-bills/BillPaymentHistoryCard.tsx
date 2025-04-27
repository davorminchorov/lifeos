import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';

interface BillPayment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  payment_method: string;
  reference_number?: string;
  notes?: string;
  created_at: string;
}

interface BillPaymentHistoryCardProps {
  payments: BillPayment[];
  currency: string;
  onRecordPayment: () => void;
  showEmptyState?: boolean;
}

const BillPaymentHistoryCard: React.FC<BillPaymentHistoryCardProps> = ({
  payments,
  currency,
  onRecordPayment,
  showEmptyState = true,
}) => {
  const sortedPayments = [...payments].sort(
    (a, b) => new Date(b.payment_date).getTime() - new Date(a.payment_date).getTime()
  );

  const formatPaymentMethod = (method: string) => {
    return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  };

  return (
    <Card>
      <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 className="text-lg font-medium text-gray-900">Payment History</h3>
        <Button onClick={onRecordPayment} size="sm">
          Record Payment
        </Button>
      </div>

      {payments.length === 0 ? (
        showEmptyState ? (
          <div className="p-6 flex flex-col items-center justify-center text-center">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-12 w-12 text-gray-300 mb-4"
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
            <p className="text-gray-500 mb-4">No payment records found</p>
            <Button onClick={onRecordPayment} size="sm">
              Record Your First Payment
            </Button>
          </div>
        ) : (
          <div className="p-6 text-center text-gray-500">No payment records available.</div>
        )
      ) : (
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Method
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Reference
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Notes
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {sortedPayments.map((payment) => (
                <tr key={payment.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {formatDate(payment.payment_date)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {formatCurrency(payment.amount, currency)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {formatPaymentMethod(payment.payment_method)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {payment.reference_number || '-'}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-500">
                    {payment.notes || '-'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </Card>
  );
};

export default BillPaymentHistoryCard;

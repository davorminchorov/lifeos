import React from 'react';
import { formatCurrency } from '../../utils/format';
import Card from '../../ui/Card/Card';

interface PaymentStatsProps {
  totalSpent: number;
  currency: string;
  paymentCount: number;
  averagePayment: number;
  thisMonth: number;
  lastMonth: number;
}

const PaymentStats: React.FC<PaymentStatsProps> = ({
  totalSpent,
  currency,
  paymentCount,
  averagePayment,
  thisMonth,
  lastMonth
}) => {
  const monthlyChange = thisMonth - lastMonth;
  const percentChange = lastMonth > 0 ? (monthlyChange / lastMonth) * 100 : 0;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {/* Total Spent */}
      <Card>
        <div className="p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">Total Spent</p>
              <p className="text-2xl font-semibold mt-1">{formatCurrency(totalSpent, currency)}</p>
            </div>
            <div className="bg-indigo-100 p-2 rounded-md">
              <svg className="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p className="text-sm text-gray-500 mt-2">Lifetime payment total</p>
        </div>
      </Card>

      {/* Payment Count */}
      <Card>
        <div className="p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">Payments Made</p>
              <p className="text-2xl font-semibold mt-1">{paymentCount}</p>
            </div>
            <div className="bg-green-100 p-2 rounded-md">
              <svg className="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
          <p className="text-sm text-gray-500 mt-2">Total number of payments</p>
        </div>
      </Card>

      {/* Average Payment */}
      <Card>
        <div className="p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">Average Payment</p>
              <p className="text-2xl font-semibold mt-1">{formatCurrency(averagePayment, currency)}</p>
            </div>
            <div className="bg-blue-100 p-2 rounded-md">
              <svg className="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
              </svg>
            </div>
          </div>
          <p className="text-sm text-gray-500 mt-2">Average payment amount</p>
        </div>
      </Card>

      {/* Monthly Comparison */}
      <Card>
        <div className="p-6">
          <div className="flex justify-between items-start">
            <div>
              <p className="text-sm font-medium text-gray-500">This Month</p>
              <p className="text-2xl font-semibold mt-1">{formatCurrency(thisMonth, currency)}</p>
            </div>
            <div className={`${monthlyChange >= 0 ? 'bg-green-100' : 'bg-red-100'} p-2 rounded-md`}>
              <svg
                className={`w-6 h-6 ${monthlyChange >= 0 ? 'text-green-600' : 'text-red-600'}`}
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d={monthlyChange >= 0
                    ? "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                    : "M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"}
                />
              </svg>
            </div>
          </div>
          <div className="flex items-center mt-2">
            <span
              className={`text-sm ${monthlyChange >= 0 ? 'text-green-600' : 'text-red-600'} font-medium`}
            >
              {monthlyChange >= 0 ? '+' : ''}{percentChange.toFixed(1)}%
            </span>
            <span className="text-sm text-gray-500 ml-2">vs last month</span>
          </div>
        </div>
      </Card>
    </div>
  );
};

export default PaymentStats;

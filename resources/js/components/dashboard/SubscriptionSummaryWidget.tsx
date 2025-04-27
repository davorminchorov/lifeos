import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency } from '../../utils/format';
import { Card } from '../../ui/Card';

interface SubscriptionSummary {
  active_count: number;
  monthly_total: number;
  annual_total: number;
  currency: string;
  upcoming_payments: UpcomingPayment[];
}

interface UpcomingPayment {
  id: string;
  name: string;
  amount: number;
  currency: string;
  due_date: string;
}

const SubscriptionSummaryWidget: React.FC = () => {
  const [summary, setSummary] = useState<SubscriptionSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchSummary = async () => {
      try {
        setLoading(true);
        const response = await axios.get('/api/dashboard/subscriptions-summary');
        setSummary(response.data);
      } catch (err) {
        console.error('Failed to fetch subscription summary', err);
        setError('Failed to load subscription data');
      } finally {
        setLoading(false);
      }
    };

    fetchSummary();
  }, []);

  if (loading) {
    return (
      <Card className="h-full">
        <div className="p-6">
          <div className="animate-pulse space-y-4">
            <div className="h-6 bg-gray-200 rounded w-1/3"></div>
            <div className="h-10 bg-gray-200 rounded w-1/2"></div>
            <div className="h-4 bg-gray-200 rounded w-3/4"></div>
            <div className="h-4 bg-gray-200 rounded w-2/3"></div>
            <div className="space-y-2">
              <div className="h-4 bg-gray-200 rounded"></div>
              <div className="h-4 bg-gray-200 rounded"></div>
              <div className="h-4 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>
      </Card>
    );
  }

  if (error || !summary) {
    return (
      <Card className="h-full">
        <div className="p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-2">Subscriptions</h3>
          <p className="text-red-500">{error || 'Failed to load data'}</p>
        </div>
      </Card>
    );
  }

  return (
    <Card className="h-full">
      <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 className="text-lg font-medium text-gray-900">Subscriptions</h3>
        <Link
          to="/subscriptions"
          className="text-sm text-indigo-600 hover:text-indigo-800"
        >
          View all
        </Link>
      </div>

      <div className="p-6">
        <div className="grid grid-cols-2 gap-4 mb-6">
          <div>
            <p className="text-sm text-gray-500 mb-1">Active Subscriptions</p>
            <p className="text-2xl font-bold">{summary.active_count}</p>
          </div>

          <div>
            <p className="text-sm text-gray-500 mb-1">Monthly Cost</p>
            <p className="text-2xl font-bold text-indigo-600">
              {formatCurrency(summary.monthly_total, summary.currency)}
            </p>
          </div>

          <div className="col-span-2">
            <p className="text-sm text-gray-500 mb-1">Annual Cost</p>
            <p className="text-xl font-semibold">
              {formatCurrency(summary.annual_total, summary.currency)}
            </p>
          </div>
        </div>

        {summary.upcoming_payments.length > 0 ? (
          <div>
            <h4 className="text-sm font-medium text-gray-700 mb-2">Upcoming Payments</h4>
            <ul className="divide-y divide-gray-200">
              {summary.upcoming_payments.slice(0, 3).map((payment) => (
                <li key={payment.id} className="py-2">
                  <Link to={`/subscriptions/${payment.id}`} className="block hover:bg-gray-50 -mx-2 px-2 py-1 rounded">
                    <div className="flex justify-between items-center">
                      <p className="text-sm font-medium text-gray-900">{payment.name}</p>
                      <p className="text-sm font-medium text-gray-700">
                        {formatCurrency(payment.amount, payment.currency)}
                      </p>
                    </div>
                    <p className="text-xs text-gray-500">
                      Due on {new Date(payment.due_date).toLocaleDateString()}
                    </p>
                  </Link>
                </li>
              ))}
            </ul>
            {summary.upcoming_payments.length > 3 && (
              <div className="mt-3 text-center">
                <Link
                  to="/subscriptions"
                  className="text-xs text-indigo-600 hover:text-indigo-800"
                >
                  View {summary.upcoming_payments.length - 3} more
                </Link>
              </div>
            )}
          </div>
        ) : (
          <p className="text-sm text-gray-500 text-center py-2">
            No upcoming payments scheduled
          </p>
        )}
      </div>
    </Card>
  );
};

export default SubscriptionSummaryWidget;

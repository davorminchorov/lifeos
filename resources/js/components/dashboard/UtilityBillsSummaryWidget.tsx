import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';

interface UtilityBillsSummary {
  total_count: number;
  monthly_total: number;
  overdue_count: number;
  due_soon_count: number;
  currency: string;
  upcoming_bills: UpcomingBill[];
}

interface UpcomingBill {
  id: string;
  name: string;
  amount: number | null;
  currency: string;
  due_date: string;
  status: string;
  provider: string;
}

const UtilityBillsSummaryWidget: React.FC = () => {
  const [summary, setSummary] = useState<UtilityBillsSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchSummary = async () => {
      try {
        setLoading(true);
        const response = await axios.get('/api/dashboard/utility-bills-summary');
        setSummary(response.data);
      } catch (err) {
        console.error('Failed to fetch utility bills summary', err);
        setError('Failed to load utility bills data');
      } finally {
        setLoading(false);
      }
    };

    fetchSummary();
  }, []);

  const renderStatusBadge = (status: string) => {
    let className = '';

    switch (status) {
      case 'paid':
        className = 'bg-green-100 text-green-800';
        break;
      case 'due':
        className = 'bg-yellow-100 text-yellow-800';
        break;
      case 'overdue':
        className = 'bg-red-100 text-red-800';
        break;
      case 'upcoming':
        className = 'bg-blue-100 text-blue-800';
        break;
      default:
        className = 'bg-gray-100 text-gray-800';
    }

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${className}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

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
          <h3 className="text-lg font-medium text-gray-900 mb-2">Utility Bills</h3>
          <p className="text-red-500">{error || 'Failed to load data'}</p>
        </div>
      </Card>
    );
  }

  return (
    <Card className="h-full">
      <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 className="text-lg font-medium text-gray-900">Utility Bills</h3>
        <Link
          to="/utility-bills"
          className="text-sm text-indigo-600 hover:text-indigo-800"
        >
          View all
        </Link>
      </div>

      <div className="p-6">
        <div className="grid grid-cols-2 gap-4 mb-6">
          <div>
            <p className="text-sm text-gray-500 mb-1">Total Bills</p>
            <p className="text-2xl font-bold">{summary.total_count}</p>
          </div>

          <div>
            <p className="text-sm text-gray-500 mb-1">Monthly Cost</p>
            <p className="text-2xl font-bold text-indigo-600">
              {formatCurrency(summary.monthly_total, summary.currency)}
            </p>
          </div>

          <div>
            <p className="text-sm text-gray-500 mb-1">Overdue</p>
            <p className={`text-lg font-semibold ${summary.overdue_count > 0 ? 'text-red-600' : 'text-gray-700'}`}>
              {summary.overdue_count}
            </p>
          </div>

          <div>
            <p className="text-sm text-gray-500 mb-1">Due Soon</p>
            <p className={`text-lg font-semibold ${summary.due_soon_count > 0 ? 'text-yellow-600' : 'text-gray-700'}`}>
              {summary.due_soon_count}
            </p>
          </div>
        </div>

        {summary.upcoming_bills.length > 0 ? (
          <div>
            <h4 className="text-sm font-medium text-gray-700 mb-2">Upcoming Bills</h4>
            <ul className="divide-y divide-gray-200">
              {summary.upcoming_bills.slice(0, 3).map((bill) => (
                <li key={bill.id} className="py-2">
                  <Link to={`/utility-bills/${bill.id}`} className="block hover:bg-gray-50 -mx-2 px-2 py-1 rounded">
                    <div className="flex justify-between items-center">
                      <div>
                        <p className="text-sm font-medium text-gray-900">{bill.name}</p>
                        <p className="text-xs text-gray-500">{bill.provider}</p>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-medium text-gray-700">
                          {bill.amount !== null ? formatCurrency(bill.amount, bill.currency) : 'Variable'}
                        </p>
                        <div className="mt-1">
                          {renderStatusBadge(bill.status)}
                        </div>
                      </div>
                    </div>
                    <p className="text-xs text-gray-500 mt-1">
                      Due on {formatDate(bill.due_date)}
                    </p>
                  </Link>
                </li>
              ))}
            </ul>
            {summary.upcoming_bills.length > 3 && (
              <div className="mt-3 text-center">
                <Link
                  to="/utility-bills"
                  className="text-xs text-indigo-600 hover:text-indigo-800"
                >
                  View {summary.upcoming_bills.length - 3} more
                </Link>
              </div>
            )}
          </div>
        ) : (
          <p className="text-sm text-gray-500 text-center py-2">
            No upcoming bills due
          </p>
        )}
      </div>
    </Card>
  );
};

export default UtilityBillsSummaryWidget;

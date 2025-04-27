import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { formatCurrency } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { Link } from 'react-router-dom';

// Simple line chart component
const LineChart: React.FC<{
  data: { label: string; value: number }[];
  height?: number;
  width?: number;
  color?: string;
  className?: string;
}> = ({ data, height = 200, width = 600, color = '#4f46e5', className = '' }) => {
  if (data.length < 2) return <div className="p-4 text-center text-gray-500">Not enough data</div>;

  // Find max value for scaling
  const maxValue = Math.max(...data.map(d => d.value));

  // Calculate points
  const points = data.map((d, i) => {
    const x = (i / (data.length - 1)) * width;
    const y = height - (d.value / maxValue) * height;
    return `${x},${y}`;
  });

  return (
    <div className={`relative ${className}`}>
      <svg width={width} height={height}>
        {/* Horizontal grid lines */}
        {[0.25, 0.5, 0.75].map(ratio => (
          <line
            key={ratio}
            x1={0}
            y1={height * (1 - ratio)}
            x2={width}
            y2={height * (1 - ratio)}
            stroke="#e5e7eb"
            strokeWidth="1"
          />
        ))}

        {/* Line */}
        <polyline
          points={points.join(' ')}
          fill="none"
          stroke={color}
          strokeWidth="2"
        />

        {/* Points */}
        {data.map((d, i) => {
          const x = (i / (data.length - 1)) * width;
          const y = height - (d.value / maxValue) * height;
          return (
            <circle
              key={i}
              cx={x}
              cy={y}
              r="4"
              fill="white"
              stroke={color}
              strokeWidth="2"
            />
          );
        })}
      </svg>

      {/* X-axis labels */}
      <div className="flex justify-between mt-2">
        {data.map((d, i) => (
          <div key={i} className="text-xs text-gray-500">
            {d.label}
          </div>
        ))}
      </div>
    </div>
  );
};

// Simple bar chart component
const BarChart: React.FC<{
  data: { label: string; value: number }[];
  height?: number;
  width?: number;
  color?: string;
  className?: string;
}> = ({ data, height = 200, width = 600, color = '#4f46e5', className = '' }) => {
  if (data.length === 0) return <div className="p-4 text-center text-gray-500">No data available</div>;

  // Find max value for scaling
  const maxValue = Math.max(...data.map(d => d.value));
  const barWidth = width / data.length - 10;

  return (
    <div className={`relative ${className}`}>
      <svg width={width} height={height}>
        {/* Horizontal grid lines */}
        {[0.25, 0.5, 0.75].map(ratio => (
          <line
            key={ratio}
            x1={0}
            y1={height * (1 - ratio)}
            x2={width}
            y2={height * (1 - ratio)}
            stroke="#e5e7eb"
            strokeWidth="1"
          />
        ))}

        {/* Bars */}
        {data.map((d, i) => {
          const barHeight = (d.value / maxValue) * height;
          const x = (i * (barWidth + 10)) + 5;
          const y = height - barHeight;

          return (
            <rect
              key={i}
              x={x}
              y={y}
              width={barWidth}
              height={barHeight}
              fill={color}
              rx="2"
              ry="2"
            />
          );
        })}
      </svg>

      {/* X-axis labels */}
      <div className="flex justify-between mt-2">
        {data.map((d, i) => (
          <div key={i} className="text-xs text-gray-500">
            {d.label}
          </div>
        ))}
      </div>
    </div>
  );
};

// Donut chart component for category breakdown
const DonutChart: React.FC<{
  data: { label: string; value: number; color: string }[];
  size?: number;
  className?: string;
}> = ({ data, size = 200, className = '' }) => {
  if (data.length === 0) return <div className="p-4 text-center text-gray-500">No data available</div>;

  const total = data.reduce((sum, d) => sum + d.value, 0);
  const radius = size / 2;
  const center = size / 2;
  const strokeWidth = radius * 0.4;
  const innerRadius = radius - strokeWidth;

  // Calculate stroke-dasharray and stroke-dashoffset for each segment
  let startAngle = 0;
  const segments = data.map(d => {
    const percentage = d.value / total;
    const angle = percentage * 360;
    const circumference = 2 * Math.PI * innerRadius;

    const dashArray = circumference;
    const dashOffset = circumference * (1 - percentage);

    // Rotate to starting position
    const rotation = startAngle;
    startAngle += angle;

    return {
      label: d.label,
      value: d.value,
      color: d.color,
      dashArray,
      dashOffset,
      rotation
    };
  });

  return (
    <div className={`relative ${className}`}>
      <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
        {segments.map((segment, i) => (
          <circle
            key={i}
            cx={center}
            cy={center}
            r={innerRadius}
            fill="none"
            stroke={segment.color}
            strokeWidth={strokeWidth}
            strokeDasharray={segment.dashArray}
            strokeDashoffset={segment.dashOffset}
            transform={`rotate(${segment.rotation} ${center} ${center})`}
          />
        ))}
        {/* Inner white circle */}
        <circle
          cx={center}
          cy={center}
          r={innerRadius - strokeWidth / 2}
          fill="white"
        />
      </svg>

      {/* Legend */}
      <div className="mt-4">
        {segments.map((segment, i) => (
          <div key={i} className="flex items-center mb-2">
            <div
              className="w-3 h-3 rounded-full mr-2"
              style={{ backgroundColor: segment.color }}
            />
            <div className="text-xs text-gray-700">
              {segment.label} ({((segment.value / total) * 100).toFixed(1)}%)
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

interface PaymentData {
  id: string;
  subscription_id: string;
  subscription_name: string;
  amount: number;
  currency: string;
  payment_date: string;
  category?: string;
  notes: string | null;
  created_at: string;
}

const PaymentReports: React.FC = () => {
  const [payments, setPayments] = useState<PaymentData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [timeRange, setTimeRange] = useState<'3m' | '6m' | '1y' | 'all'>('6m');
  const [currency, setCurrency] = useState('USD');

  useEffect(() => {
    const fetchPayments = async () => {
      setLoading(true);
      try {
        // In a real app, we would pass timeRange as a parameter
        const response = await axios.get('/api/payments');
        setPayments(response.data.data || []);
        setError(null);
      } catch (err) {
        setError('Failed to load payment data');
        console.error(err);

        // Mock data for development
        const mockData: PaymentData[] = [
          {
            id: '1',
            subscription_id: '1',
            subscription_name: 'Netflix',
            amount: 15.99,
            currency: 'USD',
            payment_date: '2023-10-15',
            category: 'Entertainment',
            notes: 'Monthly payment',
            created_at: '2023-10-15T10:00:00Z'
          },
          {
            id: '2',
            subscription_id: '2',
            subscription_name: 'Spotify',
            amount: 9.99,
            currency: 'USD',
            payment_date: '2023-10-20',
            category: 'Entertainment',
            notes: null,
            created_at: '2023-10-20T11:30:00Z'
          },
          {
            id: '3',
            subscription_id: '1',
            subscription_name: 'Netflix',
            amount: 15.99,
            currency: 'USD',
            payment_date: '2023-09-15',
            category: 'Entertainment',
            notes: 'Monthly payment',
            created_at: '2023-09-15T14:20:00Z'
          },
          {
            id: '4',
            subscription_id: '3',
            subscription_name: 'Adobe Creative Cloud',
            amount: 52.99,
            currency: 'USD',
            payment_date: '2023-10-05',
            category: 'Software',
            notes: 'Monthly subscription',
            created_at: '2023-10-05T09:15:00Z'
          },
          {
            id: '5',
            subscription_id: '4',
            subscription_name: 'Gym Membership',
            amount: 29.99,
            currency: 'USD',
            payment_date: '2023-09-01',
            category: 'Health',
            notes: 'Monthly gym fee',
            created_at: '2023-09-01T16:45:00Z'
          },
          {
            id: '6',
            subscription_id: '5',
            subscription_name: 'Amazon Prime',
            amount: 14.99,
            currency: 'USD',
            payment_date: '2023-08-20',
            category: 'Shopping',
            notes: 'Annual membership',
            created_at: '2023-08-20T08:30:00Z'
          },
          {
            id: '7',
            subscription_id: '3',
            subscription_name: 'Adobe Creative Cloud',
            amount: 52.99,
            currency: 'USD',
            payment_date: '2023-07-05',
            category: 'Software',
            notes: 'Monthly subscription',
            created_at: '2023-07-05T11:20:00Z'
          }
        ];

        setPayments(mockData);
      } finally {
        setLoading(false);
      }
    };

    fetchPayments();
  }, [timeRange]);

  // Prepare data for monthly spending chart
  const getMonthlySpendingData = () => {
    const months: Record<string, number> = {};

    // Get the date range based on timeRange
    const now = new Date();
    let startDate;

    switch (timeRange) {
      case '3m':
        startDate = new Date(now.getFullYear(), now.getMonth() - 3, 1);
        break;
      case '6m':
        startDate = new Date(now.getFullYear(), now.getMonth() - 6, 1);
        break;
      case '1y':
        startDate = new Date(now.getFullYear() - 1, now.getMonth(), 1);
        break;
      default:
        // Get earliest payment date from data
        const dates = payments.map(p => new Date(p.payment_date).getTime());
        startDate = dates.length ? new Date(Math.min(...dates)) : new Date(now.getFullYear(), now.getMonth() - 6, 1);
    }

    // Initialize all months in the range
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    for (let year = startDate.getFullYear(); year <= currentYear; year++) {
      const startMonth = year === startDate.getFullYear() ? startDate.getMonth() : 0;
      const endMonth = year === currentYear ? currentMonth : 11;

      for (let month = startMonth; month <= endMonth; month++) {
        const monthKey = `${year}-${month + 1}`;
        months[monthKey] = 0;
      }
    }

    // Sum up payments by month
    payments.forEach(payment => {
      const date = new Date(payment.payment_date);
      const monthKey = `${date.getFullYear()}-${date.getMonth() + 1}`;

      if (months[monthKey] !== undefined) {
        months[monthKey] += payment.amount;
      }
    });

    // Convert to chart data format
    const monthLabels = {
      1: 'Jan', 2: 'Feb', 3: 'Mar', 4: 'Apr', 5: 'May', 6: 'Jun',
      7: 'Jul', 8: 'Aug', 9: 'Sep', 10: 'Oct', 11: 'Nov', 12: 'Dec'
    };

    return Object.entries(months).map(([key, value]) => {
      const [year, month] = key.split('-').map(Number);
      return {
        label: `${monthLabels[month]} ${year}`,
        value: value
      };
    });
  };

  // Prepare data for category breakdown chart
  const getCategoryData = () => {
    // Sum payments by category
    const categories: Record<string, number> = {};

    payments.forEach(payment => {
      const category = payment.category || 'Uncategorized';
      categories[category] = (categories[category] || 0) + payment.amount;
    });

    // Define colors for categories
    const categoryColors: Record<string, string> = {
      'Entertainment': '#8b5cf6',
      'Software': '#3b82f6',
      'Health': '#10b981',
      'Shopping': '#f59e0b',
      'Utilities': '#ef4444',
      'Uncategorized': '#9ca3af'
    };

    // Convert to chart data format
    return Object.entries(categories).map(([category, amount]) => ({
      label: category,
      value: amount,
      color: categoryColors[category] || '#9ca3af'
    }));
  };

  // Prepare data for subscription comparison chart
  const getSubscriptionData = () => {
    // Sum payments by subscription
    const subscriptions: Record<string, number> = {};

    payments.forEach(payment => {
      subscriptions[payment.subscription_name] =
        (subscriptions[payment.subscription_name] || 0) + payment.amount;
    });

    // Convert to chart data format and sort by amount (descending)
    return Object.entries(subscriptions)
      .map(([name, amount]) => ({
        label: name,
        value: amount
      }))
      .sort((a, b) => b.value - a.value)
      .slice(0, 5); // Top 5 subscriptions
  };

  // Calculate total spending
  const totalSpending = payments.reduce((sum, payment) => sum + payment.amount, 0);

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex justify-center items-center h-64">
          <svg className="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex flex-col space-y-4 mb-8">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <h1 className="text-3xl font-bold mb-2 sm:mb-0">Payment Reports</h1>

          <div className="inline-flex p-1 bg-gray-100 rounded-lg shadow-sm sm:ml-6">
            <Button
              variant={timeRange === '3m' ? 'contained' : 'outlined'}
              size="sm"
              onClick={() => setTimeRange('3m')}
              className="rounded-md"
            >
              3 Months
            </Button>
            <Button
              variant={timeRange === '6m' ? 'contained' : 'outlined'}
              size="sm"
              onClick={() => setTimeRange('6m')}
              className="rounded-md"
            >
              6 Months
            </Button>
            <Button
              variant={timeRange === '1y' ? 'contained' : 'outlined'}
              size="sm"
              onClick={() => setTimeRange('1y')}
              className="rounded-md"
            >
              1 Year
            </Button>
            <Button
              variant={timeRange === 'all' ? 'contained' : 'outlined'}
              size="sm"
              onClick={() => setTimeRange('all')}
              className="rounded-md"
            >
              All Time
            </Button>
          </div>
        </div>

        <p className="text-gray-600">View your payment history and spending patterns over time.</p>
      </div>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          {error}
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
        {/* Summary Card */}
        <Card className="border border-gray-200 shadow-sm md:col-span-12">
          <div className="p-6">
            <h2 className="text-xl font-semibold mb-4">Summary</h2>
            <div className="flex flex-wrap gap-6">
              <div className="flex-1 min-w-[150px]">
                <p className="text-sm text-gray-500">Total Spending</p>
                <p className="text-2xl font-bold">{formatCurrency(totalSpending, currency)}</p>
              </div>
              <div className="flex-1 min-w-[150px]">
                <p className="text-sm text-gray-500">Number of Payments</p>
                <p className="text-2xl font-bold">{payments.length}</p>
              </div>
              <div className="flex-1 min-w-[150px]">
                <p className="text-sm text-gray-500">Average Payment</p>
                <p className="text-2xl font-bold">
                  {formatCurrency(payments.length ? totalSpending / payments.length : 0, currency)}
                </p>
              </div>
            </div>
          </div>
        </Card>

        {/* Monthly Spending Chart */}
        <Card className="border border-gray-200 shadow-sm md:col-span-12">
          <div className="p-6">
            <h2 className="text-xl font-semibold mb-4">Monthly Spending</h2>
            {getMonthlySpendingData().length === 0 || totalSpending === 0 ? (
              <div className="flex flex-col items-center justify-center p-10 bg-gray-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p className="text-lg font-medium text-gray-600 mb-1">No spending data yet</p>
                <p className="text-gray-500 text-center mb-4">Start recording payments to visualize your spending patterns over time.</p>
                <Link to="/payments/record">
                  <Button size="sm">Record Your First Payment</Button>
                </Link>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <div className="min-w-max">
                  <LineChart
                    data={getMonthlySpendingData()}
                    height={200}
                    width={Math.max(600, getMonthlySpendingData().length * 80)}
                    color="#4f46e5"
                  />
                </div>
              </div>
            )}
          </div>
        </Card>

        {/* Category Breakdown */}
        <Card className="border border-gray-200 shadow-sm md:col-span-6">
          <div className="p-6">
            <h2 className="text-xl font-semibold mb-4">Spending by Category</h2>
            {getCategoryData().length === 0 || totalSpending === 0 ? (
              <div className="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                <p className="text-gray-500 text-center">No category data available</p>
                <p className="text-sm text-gray-400 text-center mt-1">
                  Categories will appear as you record payments
                </p>
              </div>
            ) : (
              <div className="flex justify-center">
                <DonutChart
                  data={getCategoryData()}
                  size={250}
                />
              </div>
            )}
          </div>
        </Card>

        {/* Top Subscriptions */}
        <Card className="border border-gray-200 shadow-sm md:col-span-6">
          <div className="p-6">
            <h2 className="text-xl font-semibold mb-4">Top Subscriptions</h2>
            {getSubscriptionData().length === 0 || totalSpending === 0 ? (
              <div className="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
                <p className="text-gray-500 text-center">No subscription data available</p>
                <p className="text-sm text-gray-400 text-center mt-1">
                  Add subscriptions in the subscription section
                </p>
              </div>
            ) : (
              <BarChart
                data={getSubscriptionData()}
                height={250}
                width={500}
                color="#8b5cf6"
              />
            )}
          </div>
        </Card>

        {/* Subscription Analysis */}
        <Card className="border border-gray-200 shadow-sm md:col-span-12">
          <div className="p-6">
            <h2 className="text-xl font-semibold mb-4">Subscription Analysis</h2>
            {payments.length === 0 ? (
              <div className="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p className="text-lg font-medium text-gray-600 mb-1">Ready for your insights</p>
                <p className="text-gray-500 text-center mb-4">We'll analyze your spending once you start recording payments.</p>
                <div className="flex space-x-3">
                  <Link to="/subscriptions">
                    <Button variant="outlined" size="sm">Manage Subscriptions</Button>
                  </Link>
                  <Link to="/payments/record">
                    <Button size="sm">Record Payment</Button>
                  </Link>
                </div>
              </div>
            ) : (
              <div className="space-y-4">
                <p className="text-gray-700">
                  Based on your spending history, here are some insights and suggestions:
                </p>
                <ul className="list-disc pl-5 space-y-2 text-gray-700">
                  <li>
                    Your highest spending category is{' '}
                    <span className="font-medium">
                      {getCategoryData().sort((a, b) => b.value - a.value)[0]?.label || 'N/A'}
                    </span>
                    {' '}at{' '}
                    {formatCurrency(getCategoryData().sort((a, b) => b.value - a.value)[0]?.value || 0, currency)}
                  </li>
                  <li>
                    Your most expensive subscription is{' '}
                    <span className="font-medium">
                      {getSubscriptionData().sort((a, b) => b.value - a.value)[0]?.label || 'N/A'}
                    </span>
                  </li>
                  <li>
                    Average monthly spending:{' '}
                    {formatCurrency(
                      getMonthlySpendingData().reduce((sum, month) => sum + month.value, 0) /
                      Math.max(getMonthlySpendingData().length, 1),
                      currency
                    )}
                  </li>
                </ul>
              </div>
            )}
          </div>
        </Card>
      </div>

      {payments.length === 0 && (
        <div className="mt-8 text-center">
          <p className="text-gray-500 mb-2">Don't see any data?</p>
          <p className="text-gray-700 mb-4">Start by recording payments for your subscriptions and bills.</p>
          <div className="flex justify-center space-x-4">
            <Link to="/subscriptions">
              <Button variant="outlined">Manage Subscriptions</Button>
            </Link>
            <Link to="/payments/record">
              <Button>Record a Payment</Button>
            </Link>
          </div>
        </div>
      )}
    </div>
  );
};

export default PaymentReports;

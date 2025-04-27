import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../../ui/Card';
import { PieChart, PieChartProps } from 'react-minimal-pie-chart';
import { formatCurrency, formatCompactCurrency } from '../../utils/format';

interface PortfolioData {
  total_invested: number;
  total_current_value: number;
  overall_roi: number;
  by_type: Record<string, { count: number; value: number; percentage: number }>;
}

interface PortfolioAnalyticsProps {
  data: PortfolioData;
  className?: string;
}

const typeColors: Record<string, string> = {
  stock: '#4F46E5', // indigo-600
  bond: '#10B981', // emerald-500
  mutual_fund: '#8B5CF6', // purple-500
  etf: '#F59E0B', // amber-500
  real_estate: '#EF4444', // red-500
  retirement: '#3B82F6', // blue-500
  life_insurance: '#14B8A6', // teal-500
  crypto: '#EC4899', // pink-500
  other: '#6B7280', // gray-500
};

const typeLabels: Record<string, string> = {
  stock: 'Stocks',
  bond: 'Bonds',
  mutual_fund: 'Mutual Funds',
  etf: 'ETFs',
  real_estate: 'Real Estate',
  retirement: 'Retirement Accounts',
  life_insurance: 'Life Insurance',
  crypto: 'Cryptocurrency',
  other: 'Other Investments',
};

const PortfolioAnalytics: React.FC<PortfolioAnalyticsProps> = ({ data, className = '' }) => {
  if (!data) return null;

  // Sort types by value (highest first) for the chart
  const sortedTypes = Object.entries(data.by_type || {}).sort((a, b) => b[1].value - a[1].value);

  // Prepare pie chart data
  const chartData = sortedTypes.map(([type, info]) => ({
    title: typeLabels[type] || type,
    value: info.value,
    color: typeColors[type] || typeColors.other,
    percentage: info.percentage
  }));

  // Calculate value difference and percent change
  const valueDifference = data.total_current_value - data.total_invested;
  const isPositiveReturn = valueDifference >= 0;

  return (
    <div className={className}>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card className="col-span-1 bg-white shadow-sm rounded-xl overflow-hidden h-full">
          <CardHeader className="bg-gray-50 border-b border-gray-100 px-6 py-4">
            <CardTitle className="text-lg font-semibold text-gray-800">Performance Summary</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <div className="space-y-6">
              <div>
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm font-medium text-gray-500">Total Invested</span>
                  <span className="text-sm font-bold text-gray-900">
                    {formatCurrency(data.total_invested, 'USD')}
                  </span>
                </div>
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm font-medium text-gray-500">Current Value</span>
                  <span className="text-sm font-bold text-gray-900">
                    {formatCurrency(data.total_current_value, 'USD')}
                  </span>
                </div>
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm font-medium text-gray-500">Value Change</span>
                  <span className={`text-sm font-bold ${isPositiveReturn ? 'text-green-600' : 'text-red-600'}`}>
                    {isPositiveReturn ? '+' : ''}{formatCurrency(valueDifference, 'USD')}
                  </span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-sm font-medium text-gray-500">Return on Investment</span>
                  <span className={`text-sm font-bold ${data.overall_roi >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                    {data.overall_roi >= 0 ? '+' : ''}{data.overall_roi.toFixed(2)}%
                  </span>
                </div>
              </div>

              <div className="h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                <div
                  className={`h-full ${isPositiveReturn ? 'bg-green-500' : 'bg-red-500'}`}
                  style={{ width: `${Math.min(Math.abs(data.overall_roi), 100)}%` }}
                ></div>
              </div>

              <div className="pt-4 border-t border-gray-100">
                <h4 className="text-sm font-medium text-gray-700 mb-3">Portfolio Diversity</h4>
                <div className="space-y-1.5">
                  {sortedTypes.slice(0, 5).map(([type, info]) => (
                    <div key={type} className="flex justify-between items-center">
                      <div className="flex items-center">
                        <div
                          className="w-3 h-3 rounded-full mr-2"
                          style={{ backgroundColor: typeColors[type] || typeColors.other }}
                        ></div>
                        <span className="text-xs text-gray-600">{typeLabels[type] || type}</span>
                      </div>
                      <span className="text-xs font-medium text-gray-900">
                        {info.percentage.toFixed(1)}%
                      </span>
                    </div>
                  ))}
                  {sortedTypes.length > 5 && (
                    <div className="text-xs text-gray-500 italic mt-1">
                      And {sortedTypes.length - 5} more types...
                    </div>
                  )}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="col-span-1 lg:col-span-2 bg-white shadow-sm rounded-xl overflow-hidden">
          <CardHeader className="bg-gray-50 border-b border-gray-100 px-6 py-4">
            <CardTitle className="text-lg font-semibold text-gray-800">Portfolio Allocation</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <div className="flex flex-col lg:flex-row items-center">
              <div className="w-48 h-48 mx-auto mb-6 lg:mb-0 lg:mx-0">
                <PieChart
                  data={chartData}
                  lineWidth={35}
                  paddingAngle={3}
                  rounded
                  label={({ dataEntry }) => dataEntry.percentage > 5 ? `${dataEntry.percentage.toFixed(0)}%` : ''}
                  labelStyle={{
                    fontSize: '8px',
                    fontFamily: 'sans-serif',
                    fill: '#fff',
                  }}
                  labelPosition={70}
                />
              </div>

              <div className="flex-1 lg:ml-8 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                {chartData.map((item, index) => (
                  <div key={index} className="flex items-center space-x-2">
                    <div
                      className="w-3 h-3 rounded-full"
                      style={{ backgroundColor: item.color }}
                    ></div>
                    <div className="text-sm">
                      <span className="font-medium text-gray-900">{item.title}</span>
                      <div className="flex space-x-2 text-xs text-gray-500">
                        <span>{formatCurrency(item.value, 'USD')}</span>
                        <span>·</span>
                        <span>{item.percentage.toFixed(1)}%</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default PortfolioAnalytics;

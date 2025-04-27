import React from 'react';
import { Card, CardContent } from '../../ui/Card';
import { formatCurrency, formatDate } from '../../utils/format';
import { TrendingUp, TrendingDown, DollarSign, Calendar, Briefcase, Building } from 'lucide-react';

interface InvestmentSummaryProps {
  investment: {
    name: string;
    type: string;
    institution: string;
    initial_investment: number;
    current_value: number;
    roi: number;
    start_date: string;
    last_valuation_date: string;
  };
}

const typeLabels: Record<string, string> = {
  stock: 'Stocks',
  bond: 'Bonds',
  mutual_fund: 'Mutual Funds',
  etf: 'ETFs',
  real_estate: 'Real Estate',
  retirement: 'Retirement Account',
  life_insurance: 'Life Insurance',
  other: 'Other',
};

const InvestmentSummary: React.FC<InvestmentSummaryProps> = ({ investment }) => {
  const isPositiveROI = investment.roi >= 0;

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
        <CardContent className="p-5">
          <div className="flex items-center space-x-4">
            <div className="flex-shrink-0 p-3 bg-indigo-50 rounded-full">
              <DollarSign className="h-6 w-6 text-indigo-600" />
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">Initial Investment</p>
              <h3 className="text-xl font-bold text-gray-900 mt-1">
                {formatCurrency(investment.initial_investment, 'USD')}
              </h3>
              <p className="text-xs text-gray-500 mt-1">Started {formatDate(investment.start_date)}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
        <CardContent className="p-5">
          <div className="flex items-center space-x-4">
            <div className="flex-shrink-0 p-3 bg-green-50 rounded-full">
              <DollarSign className="h-6 w-6 text-green-600" />
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">Current Value</p>
              <h3 className="text-xl font-bold text-gray-900 mt-1">
                {formatCurrency(investment.current_value, 'USD')}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                As of {formatDate(investment.last_valuation_date)}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
        <CardContent className="p-5">
          <div className="flex items-center space-x-4">
            <div className={`flex-shrink-0 p-3 ${isPositiveROI ? 'bg-green-50' : 'bg-red-50'} rounded-full`}>
              {isPositiveROI ? (
                <TrendingUp className="h-6 w-6 text-green-600" />
              ) : (
                <TrendingDown className="h-6 w-6 text-red-600" />
              )}
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">Return on Investment</p>
              <h3 className={`text-xl font-bold mt-1 ${isPositiveROI ? 'text-green-600' : 'text-red-600'}`}>
                {isPositiveROI ? '+' : ''}{investment.roi.toFixed(2)}%
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                {formatCurrency(investment.current_value - investment.initial_investment, 'USD')}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
        <CardContent className="p-5">
          <div className="flex items-center space-x-4">
            <div className="flex-shrink-0 p-3 bg-blue-50 rounded-full">
              <Building className="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">Institution</p>
              <h3 className="text-xl font-bold text-gray-900 mt-1">
                {investment.institution}
              </h3>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
        <CardContent className="p-5">
          <div className="flex items-center space-x-4">
            <div className="flex-shrink-0 p-3 bg-purple-50 rounded-full">
              <Briefcase className="h-6 w-6 text-purple-600" />
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">Investment Type</p>
              <h3 className="text-xl font-bold text-gray-900 mt-1">
                {typeLabels[investment.type] || investment.type}
              </h3>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
        <CardContent className="p-5">
          <div className="flex items-center space-x-4">
            <div className="flex-shrink-0 p-3 bg-amber-50 rounded-full">
              <Calendar className="h-6 w-6 text-amber-600" />
            </div>
            <div>
              <p className="text-sm font-medium text-gray-500">Holding Period</p>
              <h3 className="text-xl font-bold text-gray-900 mt-1">
                {getHoldingPeriod(investment.start_date)}
              </h3>
              <p className="text-xs text-gray-500 mt-1">
                Since {formatDate(investment.start_date)}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

// Helper function to calculate holding period
function getHoldingPeriod(startDate: string): string {
  const start = new Date(startDate);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - start.getTime());
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

  const years = Math.floor(diffDays / 365);
  const months = Math.floor((diffDays % 365) / 30);

  if (years > 0) {
    return `${years}y ${months}m`;
  } else if (months > 0) {
    return `${months} months`;
  } else {
    return `${diffDays} days`;
  }
}

export default InvestmentSummary;

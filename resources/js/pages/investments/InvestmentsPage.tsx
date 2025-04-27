import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { Card, CardContent } from '../../ui/Card';
import { Button } from '../../ui/Button';
import { PlusCircle } from 'lucide-react';
import { formatCurrency } from '../../utils/format';
import { ArrowUpRight, DollarSign, PercentIcon, Briefcase, PieChart } from 'lucide-react';

interface PortfolioSummary {
  total_invested: number;
  total_current_value: number;
  total_withdrawn: number;
  overall_roi: number;
  by_type: Record<string, { count: number; value: number; percentage: number }>;
  total_investments: number;
}

const InvestmentsPage: React.FC = () => {
  const [summary, setSummary] = useState<PortfolioSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchSummary = async () => {
      try {
        setLoading(true);
        const response = await axios.get('/api/portfolio/summary');
        setSummary(response.data);
      } catch (err) {
        console.error('Failed to fetch portfolio summary', err);
        setError('Failed to load portfolio data');
      } finally {
        setLoading(false);
      }
    };

    fetchSummary();
  }, []);

  return (
    <div className="space-y-10 max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Investments</h1>
        <Button asChild>
          <Link to="/investments/new" className="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-5 rounded-lg flex items-center gap-3 shadow-sm transition duration-150 ease-in-out">
            <PlusCircle className="h-5 w-5" />
            Add Investment
          </Link>
        </Button>
      </div>

      {loading ? (
        <div className="flex justify-center items-center py-20">
          <div className="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
      ) : error ? (
        <div className="bg-red-50 border border-red-200 text-red-700 px-8 py-6 rounded-lg my-6" role="alert">
          <span className="font-medium">{error}</span>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <Card className="bg-white shadow-md hover:shadow-lg transition-shadow rounded-xl overflow-hidden">
              <CardContent className="p-7">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-5">
                    <div className="flex-shrink-0 p-3 bg-indigo-50 rounded-full">
                      <DollarSign className="h-7 w-7 text-indigo-600" />
                    </div>
                    <div>
                      <p className="text-sm font-medium text-gray-500 mb-1">Total Invested</p>
                      <h3 className="text-2xl font-bold text-gray-900">{formatCurrency(summary?.total_invested ?? 0, 'USD')}</h3>
                    </div>
                  </div>
                  <ArrowUpRight className="h-5 w-5 text-indigo-600" />
                </div>
              </CardContent>
            </Card>

            <Card className="bg-white shadow-md hover:shadow-lg transition-shadow rounded-xl overflow-hidden">
              <CardContent className="p-7">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-5">
                    <div className="flex-shrink-0 p-3 bg-green-50 rounded-full">
                      <DollarSign className="h-7 w-7 text-green-600" />
                    </div>
                    <div>
                      <p className="text-sm font-medium text-gray-500 mb-1">Current Value</p>
                      <h3 className="text-2xl font-bold text-gray-900">{formatCurrency(summary?.total_current_value ?? 0, 'USD')}</h3>
                    </div>
                  </div>
                  <ArrowUpRight className="h-5 w-5 text-green-600" />
                </div>
              </CardContent>
            </Card>

            <Card className="bg-white shadow-md hover:shadow-lg transition-shadow rounded-xl overflow-hidden">
              <CardContent className="p-7">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-5">
                    <div className="flex-shrink-0 p-3 bg-blue-50 rounded-full">
                      <PercentIcon className="h-7 w-7 text-blue-600" />
                    </div>
                    <div>
                      <p className="text-sm font-medium text-gray-500 mb-1">Overall ROI</p>
                      <h3 className="text-2xl font-bold text-gray-900">{summary && summary.overall_roi !== undefined ? (summary.overall_roi > 0 ? '+' : '') + summary.overall_roi.toFixed(2) + '%' : '0.00%'}</h3>
                    </div>
                  </div>
                  <ArrowUpRight className="h-5 w-5 text-blue-600" />
                </div>
              </CardContent>
            </Card>

            <Card className="bg-white shadow-md hover:shadow-lg transition-shadow rounded-xl overflow-hidden">
              <CardContent className="p-7">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-5">
                    <div className="flex-shrink-0 p-3 bg-purple-50 rounded-full">
                      <Briefcase className="h-7 w-7 text-purple-600" />
                    </div>
                    <div>
                      <p className="text-sm font-medium text-gray-500 mb-1">Total Investments</p>
                      <h3 className="text-2xl font-bold text-gray-900">{summary?.total_investments ?? 0}</h3>
                    </div>
                  </div>
                  <ArrowUpRight className="h-5 w-5 text-purple-600" />
                </div>
              </CardContent>
            </Card>
          </div>

          <div className="mt-10 pb-4">
            <Link to="/investments/list" className="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium text-lg transition duration-150 ease-in-out">
              View All Investments
              <svg className="ml-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </div>
        </>
      )}
    </div>
  );
};

export default InvestmentsPage;

import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import InvestmentForm from '../../components/investments/InvestmentForm';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';

interface Investment {
  id: string;
  name: string;
  type: string;
  institution: string;
  current_value: number;
  roi: number;
  last_valuation_date: string;
}

interface PortfolioSummary {
  total_invested: number;
  total_current_value: number;
  total_withdrawn: number;
  overall_roi: number;
  by_type: Record<string, { count: number; value: number; percentage: number }>;
  total_investments: number;
}

const typeLabels: Record<string, string> = {
  stock: 'Stocks',
  bond: 'Bonds',
  mutual_fund: 'Mutual Funds',
  etf: 'ETFs',
  real_estate: 'Real Estate',
  retirement: 'Retirement Accounts',
  life_insurance: 'Life Insurance',
  other: 'Other Investments',
};

const typeColors: Record<string, string> = {
  stock: 'bg-blue-600',
  bond: 'bg-green-600',
  mutual_fund: 'bg-purple-600',
  etf: 'bg-yellow-600',
  real_estate: 'bg-red-600',
  retirement: 'bg-indigo-600',
  life_insurance: 'bg-teal-600',
  other: 'bg-gray-600',
};

const InvestmentsListPage: React.FC = () => {
  const [investments, setInvestments] = useState<Investment[]>([]);
  const [summary, setSummary] = useState<PortfolioSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const fetchData = async () => {
    try {
      setLoading(true);
      setError('');

      // Fetch investments list
      const investmentsResponse = await axios.get('/api/investments');
      setInvestments(Array.isArray(investmentsResponse.data) ? investmentsResponse.data : []);

      // Fetch portfolio summary
      const summaryResponse = await axios.get('/api/portfolio/summary');
      setSummary(summaryResponse.data);
    } catch (err) {
      console.error('Failed to load investments data', err);
      setError('Failed to load investments data. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  if (loading) {
    return (
      <div className="p-8 flex justify-center">
        <div className="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  return (
    <div>
      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-6" role="alert">
          <span className="block sm:inline font-medium">{error}</span>
        </div>
      )}

      {summary && (
        <div className="mb-8">
          <Card className="bg-white rounded-xl shadow-sm overflow-hidden">
            <div className="border-b border-gray-100 px-6 py-4">
              <h3 className="text-lg font-semibold text-gray-800">Portfolio Allocation</h3>
              <p className="text-sm text-gray-500 mt-1">Distribution of your investments by type</p>
            </div>
            <div className="p-6">
              {summary && summary.by_type && Object.keys(summary.by_type).length > 0 ? (
                <>
                  <div className="flex h-6 w-full rounded-md overflow-hidden mb-6">
                    {Object.entries(summary.by_type).map(([type, data]) => (
                      <div
                        key={type}
                        className={`${typeColors[type] || 'bg-gray-600'}`}
                        style={{ width: `${data.percentage}%` }}
                        title={`${typeLabels[type] || type}: ${data.percentage.toFixed(1)}%`}
                      ></div>
                    ))}
                  </div>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {Object.entries(summary.by_type).map(([type, data]) => (
                      <div key={type} className="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div className={`w-4 h-4 rounded-full ${typeColors[type] || 'bg-gray-600'} mr-3`}></div>
                        <div>
                          <p className="text-sm font-medium text-gray-800">{typeLabels[type] || type}</p>
                          <p className="text-xs text-gray-500 mt-1">${data.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ({data.percentage.toFixed(1)}%)</p>
                        </div>
                      </div>
                    ))}
                  </div>
                </>
              ) : (
                <div className="py-12 text-center">
                  <svg className="mx-auto h-16 w-16 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                  </svg>
                  <h3 className="mt-4 text-lg font-medium text-gray-900">No allocation data</h3>
                  <p className="mt-1 text-sm text-gray-500">Add your first investment to see your portfolio allocation.</p>
                </div>
              )}
            </div>
          </Card>
        </div>
      )}

      <Card className="bg-white rounded-xl shadow-sm overflow-hidden">
        <div className="border-b border-gray-100 px-6 py-4">
          <h3 className="text-lg font-semibold text-gray-800">Your Investments</h3>
          <p className="text-sm text-gray-500 mt-1">Manage your investment portfolio</p>
        </div>
        <div className="p-0">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow className="bg-gray-50 border-b border-gray-100">
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</TableHead>
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</TableHead>
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</TableHead>
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</TableHead>
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROI</TableHead>
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Valuation</TableHead>
                  <TableHead className="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {investments.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7} className="text-center py-16 px-6">
                      <div className="flex flex-col items-center justify-center">
                        <svg className="h-16 w-16 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 className="text-lg font-medium text-gray-900">No investments found</h3>
                        <p className="mt-1 text-sm text-gray-500 max-w-sm text-center">Click "Add Investment" to start building your portfolio.</p>
                      </div>
                    </TableCell>
                  </TableRow>
                ) : (
                  investments.map((investment) => (
                    <TableRow key={investment.id} className="hover:bg-gray-50 border-b border-gray-100">
                      <TableCell className="py-4 px-6 font-medium text-gray-900">{investment.name}</TableCell>
                      <TableCell className="py-4 px-6">
                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                          {typeLabels[investment.type] || investment.type}
                        </span>
                      </TableCell>
                      <TableCell className="py-4 px-6 text-gray-500">{investment.institution}</TableCell>
                      <TableCell className="py-4 px-6 font-medium text-gray-900">${investment.current_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</TableCell>
                      <TableCell className="py-4 px-6">
                        <span className={`${investment.roi > 0 ? 'text-green-600' : investment.roi < 0 ? 'text-red-600' : 'text-gray-600'} font-medium`}>
                          {investment.roi > 0 ? '+' : (investment.roi < 0 ? '' : '')}
                          {investment.roi.toFixed(2)}%
                        </span>
                      </TableCell>
                      <TableCell className="py-4 px-6 text-gray-500">
                        {new Date(investment.last_valuation_date).toLocaleDateString()}
                      </TableCell>
                      <TableCell className="py-4 px-6">
                        <Link to={`/investments/${investment.id}`} className="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                          View Details
                        </Link>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </div>
      </Card>
    </div>
  );
};

export default InvestmentsListPage;

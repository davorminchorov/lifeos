import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import InvestmentForm from '../../components/investments/InvestmentForm';

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
  const [showAddForm, setShowAddForm] = useState(false);

  const fetchData = async () => {
    try {
      setLoading(true);
      setError('');

      // Fetch investments list
      const investmentsResponse = await axios.get('/api/investments');
      setInvestments(investmentsResponse.data);

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

  const handleInvestmentAdded = () => {
    setShowAddForm(false);
    fetchData();
  };

  if (loading) {
    return (
      <div className="p-4">
        <div className="animate-pulse flex space-x-4">
          <div className="flex-1 space-y-6 py-1">
            <div className="h-4 bg-gray-200 rounded w-3/4"></div>
            <div className="space-y-3">
              <div className="grid grid-cols-3 gap-4">
                <div className="h-20 bg-gray-200 rounded col-span-1"></div>
                <div className="h-20 bg-gray-200 rounded col-span-1"></div>
                <div className="h-20 bg-gray-200 rounded col-span-1"></div>
              </div>
              <div className="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-4">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Investment Portfolio</h1>
        <button
          className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded"
          onClick={() => setShowAddForm(!showAddForm)}
        >
          {showAddForm ? 'Cancel' : 'Add Investment'}
        </button>
      </div>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
          <span className="block sm:inline">{error}</span>
        </div>
      )}

      {summary && (
        <div className="mb-8">
          <div className="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 className="text-xl font-semibold mb-4">Portfolio Summary</h2>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div className="bg-gray-50 p-4 rounded-md">
                <p className="text-sm text-gray-500">Total Value</p>
                <p className="text-2xl font-bold">${summary.total_current_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
              </div>
              <div className="bg-gray-50 p-4 rounded-md">
                <p className="text-sm text-gray-500">Total Invested</p>
                <p className="text-2xl font-bold">${summary.total_invested.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
              </div>
              <div className="bg-gray-50 p-4 rounded-md">
                <p className="text-sm text-gray-500">Overall ROI</p>
                <p className={`text-2xl font-bold ${summary.overall_roi >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {summary.overall_roi >= 0 ? '+' : ''}{summary.overall_roi.toFixed(2)}%
                </p>
              </div>
              <div className="bg-gray-50 p-4 rounded-md">
                <p className="text-sm text-gray-500">Total Investments</p>
                <p className="text-2xl font-bold">{summary.total_investments}</p>
              </div>
            </div>
          </div>

          {Object.keys(summary.by_type).length > 0 && (
            <div className="bg-white shadow-md rounded-lg p-6">
              <h2 className="text-xl font-semibold mb-4">Allocation by Type</h2>
              <div className="flex h-4 w-full rounded-full overflow-hidden mb-4">
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
                  <div key={type} className="flex items-center">
                    <div className={`w-3 h-3 rounded-full ${typeColors[type] || 'bg-gray-600'} mr-2`}></div>
                    <div>
                      <p className="text-sm font-medium">{typeLabels[type] || type}</p>
                      <p className="text-xs text-gray-500">${data.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ({data.percentage.toFixed(1)}%)</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}

      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROI</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Valuation</th>
              <th scope="col" className="relative px-6 py-3">
                <span className="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {investments.length === 0 ? (
              <tr>
                <td colSpan={7} className="px-6 py-4 text-center text-sm text-gray-500">
                  No investments found. Add your first investment to get started.
                </td>
              </tr>
            ) : (
              investments.map((investment) => (
                <tr key={investment.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm font-medium text-gray-900">{investment.name}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${typeColors[investment.type] ? `${typeColors[investment.type]} text-white` : 'bg-gray-100 text-gray-800'}`}>
                      {typeLabels[investment.type] || investment.type}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{investment.institution}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${investment.current_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`text-sm ${investment.roi >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                      {investment.roi >= 0 ? '+' : ''}{investment.roi.toFixed(2)}%
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(investment.last_valuation_date).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link to={`/investments/${investment.id}`} className="text-indigo-600 hover:text-indigo-900 mr-4">
                      Details
                    </Link>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {showAddForm && (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div className="p-6">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-medium">Add New Investment</h3>
                <button
                  onClick={() => setShowAddForm(false)}
                  className="text-gray-500 hover:text-gray-700"
                >
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <InvestmentForm
                onSuccess={handleInvestmentAdded}
                onCancel={() => setShowAddForm(false)}
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default InvestmentsListPage;

import React, { useState, useEffect } from 'react';
import { Link, useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';

interface Investment {
  id: string;
  name: string;
  type: string;
  institution: string;
  account_number: string | null;
  initial_investment: number;
  current_value: number;
  roi: number;
  start_date: string;
  end_date: string | null;
  description: string | null;
  total_invested: number;
  total_withdrawn: number;
  last_valuation_date: string;
  created_at: string;
  updated_at: string;
}

interface Transaction {
  id: string;
  investment_id: string;
  type: string;
  amount: number;
  date: string;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

interface Valuation {
  id: string;
  investment_id: string;
  value: number;
  date: string;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

interface PerformanceData {
  roi: number;
  total_return: number;
  initial_value: number;
  current_value: number;
  total_invested: number;
  total_withdrawn: number;
  time_series: Array<{ date: string; value: number }>;
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

const transactionTypeLabels: Record<string, string> = {
  deposit: 'Deposit',
  withdrawal: 'Withdrawal',
  dividend: 'Dividend',
  fee: 'Fee',
  interest: 'Interest',
};

const transactionTypeColors: Record<string, string> = {
  deposit: 'bg-green-100 text-green-800',
  withdrawal: 'bg-red-100 text-red-800',
  dividend: 'bg-blue-100 text-blue-800',
  fee: 'bg-orange-100 text-orange-800',
  interest: 'bg-purple-100 text-purple-800',
};

const InvestmentDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [investment, setInvestment] = useState<Investment | null>(null);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [valuations, setValuations] = useState<Valuation[]>([]);
  const [performance, setPerformance] = useState<PerformanceData | null>(null);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const [activeTab, setActiveTab] = useState('overview');

  const [showAddTransactionForm, setShowAddTransactionForm] = useState(false);
  const [showAddValuationForm, setShowAddValuationForm] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      if (!id) return;

      try {
        setLoading(true);
        setError('');

        // Fetch investment details
        const detailsResponse = await axios.get(`/api/investments/${id}`);
        setInvestment(detailsResponse.data.investment);
        setTransactions(detailsResponse.data.transactions);
        setValuations(detailsResponse.data.valuations);

        // Fetch performance data
        const performanceResponse = await axios.get(`/api/investments/${id}/performance`);
        setPerformance(performanceResponse.data);
      } catch (err) {
        console.error('Failed to load investment data', err);
        setError('Failed to load investment data. Please try again.');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [id]);

  const handleBack = () => {
    navigate('/investments');
  };

  if (loading) {
    return (
      <div className="p-6">
        <div className="animate-pulse space-y-4">
          <div className="h-4 bg-gray-200 rounded w-1/4"></div>
          <div className="h-8 bg-gray-200 rounded w-3/4"></div>
          <div className="h-64 bg-gray-200 rounded"></div>
        </div>
      </div>
    );
  }

  if (error || !investment) {
    return (
      <div className="p-6">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
          <span className="block sm:inline">{error || 'Investment not found'}</span>
        </div>
        <button
          onClick={handleBack}
          className="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded inline-flex items-center"
        >
          <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Investments
        </button>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-4">
      {/* Header */}
      <div className="mb-6">
        <button
          onClick={handleBack}
          className="text-gray-600 hover:text-gray-900 mb-2 inline-flex items-center"
        >
          <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Investments
        </button>

        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-2xl font-bold text-gray-800">{investment.name}</h1>
            <div className="flex items-center mt-1">
              <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800`}>
                {typeLabels[investment.type] || investment.type}
              </span>
              <span className="ml-2 text-sm text-gray-500">{investment.institution}</span>
              {investment.account_number && (
                <span className="ml-2 text-sm text-gray-500">• Account #{investment.account_number}</span>
              )}
            </div>
          </div>

          <div className="flex space-x-3">
            <button
              onClick={() => setShowAddTransactionForm(true)}
              className="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded text-sm"
            >
              Record Transaction
            </button>
            <button
              onClick={() => setShowAddValuationForm(true)}
              className="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded text-sm"
            >
              Update Valuation
            </button>
          </div>
        </div>
      </div>

      {/* Tabs */}
      <div className="mb-6 border-b border-gray-200">
        <nav className="-mb-px flex">
          <button
            onClick={() => setActiveTab('overview')}
            className={`${
              activeTab === 'overview'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm`}
          >
            Overview
          </button>
          <button
            onClick={() => setActiveTab('transactions')}
            className={`${
              activeTab === 'transactions'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm`}
          >
            Transactions
          </button>
          <button
            onClick={() => setActiveTab('valuations')}
            className={`${
              activeTab === 'valuations'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm`}
          >
            Valuations
          </button>
        </nav>
      </div>

      {/* Tab Content */}
      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        {activeTab === 'overview' && (
          <div className="p-6">
            {/* Performance Summary */}
            {performance && (
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div className="bg-gray-50 p-4 rounded-md">
                  <p className="text-sm text-gray-500">Current Value</p>
                  <p className="text-2xl font-bold">${performance.current_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                </div>
                <div className="bg-gray-50 p-4 rounded-md">
                  <p className="text-sm text-gray-500">Initial Investment</p>
                  <p className="text-2xl font-bold">${performance.initial_value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                </div>
                <div className="bg-gray-50 p-4 rounded-md">
                  <p className="text-sm text-gray-500">Total Invested</p>
                  <p className="text-2xl font-bold">${performance.total_invested.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                </div>
                <div className="bg-gray-50 p-4 rounded-md">
                  <p className="text-sm text-gray-500">ROI</p>
                  <p className={`text-2xl font-bold ${performance.roi >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                    {performance.roi >= 0 ? '+' : ''}{performance.roi.toFixed(2)}%
                  </p>
                </div>
              </div>
            )}

            {/* Investment Details */}
            <div className="mb-8">
              <h3 className="text-lg font-medium mb-4">Investment Details</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                  <p className="text-sm text-gray-500">Start Date</p>
                  <p className="font-medium">{new Date(investment.start_date).toLocaleDateString()}</p>
                </div>
                {investment.end_date && (
                  <div>
                    <p className="text-sm text-gray-500">End Date</p>
                    <p className="font-medium">{new Date(investment.end_date).toLocaleDateString()}</p>
                  </div>
                )}
                <div>
                  <p className="text-sm text-gray-500">Last Valuation Date</p>
                  <p className="font-medium">{new Date(investment.last_valuation_date).toLocaleDateString()}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-500">Total Withdrawn</p>
                  <p className="font-medium">${investment.total_withdrawn.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                </div>
              </div>

              {investment.description && (
                <div className="mt-4">
                  <p className="text-sm text-gray-500">Description</p>
                  <p className="mt-1">{investment.description}</p>
                </div>
              )}
            </div>

            {/* Recent Activity */}
            <div>
              <h3 className="text-lg font-medium mb-4">Recent Activity</h3>
              <div className="space-y-4">
                {transactions.length === 0 && valuations.length === 0 ? (
                  <p className="text-gray-500">No recent activity recorded.</p>
                ) : (
                  <>
                    {/* Recent Transactions */}
                    {transactions.slice(0, 3).map(transaction => (
                      <div key={transaction.id} className="flex justify-between items-center border-b border-gray-200 pb-3">
                        <div>
                          <span className={`inline-flex px-2 py-1 text-xs rounded-full ${transactionTypeColors[transaction.type]}`}>
                            {transactionTypeLabels[transaction.type] || transaction.type}
                          </span>
                          <p className="text-sm text-gray-500 mt-1">{new Date(transaction.date).toLocaleDateString()}</p>
                        </div>
                        <div className="text-right">
                          <p className={`font-medium ${transaction.type === 'withdrawal' || transaction.type === 'fee' ? 'text-red-600' : 'text-green-600'}`}>
                            {transaction.type === 'withdrawal' || transaction.type === 'fee' ? '-' : '+'}${transaction.amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                          </p>
                          {transaction.notes && <p className="text-sm text-gray-500">{transaction.notes}</p>}
                        </div>
                      </div>
                    ))}

                    {/* View More Link */}
                    {transactions.length > 3 && (
                      <div className="text-center mt-4">
                        <button
                          onClick={() => setActiveTab('transactions')}
                          className="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                        >
                          View All Transactions
                        </button>
                      </div>
                    )}
                  </>
                )}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'transactions' && (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {transactions.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="px-6 py-4 text-center text-sm text-gray-500">
                      No transactions recorded. Use the "Record Transaction" button to add one.
                    </td>
                  </tr>
                ) : (
                  transactions.map(transaction => (
                    <tr key={transaction.id}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {new Date(transaction.date).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${transactionTypeColors[transaction.type]}`}>
                          {transactionTypeLabels[transaction.type] || transaction.type}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        <span className={`font-medium ${transaction.type === 'withdrawal' || transaction.type === 'fee' ? 'text-red-600' : 'text-green-600'}`}>
                          {transaction.type === 'withdrawal' || transaction.type === 'fee' ? '-' : '+'}${transaction.amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-500">
                        {transaction.notes || '-'}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        {activeTab === 'valuations' && (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {valuations.length === 0 ? (
                  <tr>
                    <td colSpan={3} className="px-6 py-4 text-center text-sm text-gray-500">
                      No valuations recorded. Use the "Update Valuation" button to add one.
                    </td>
                  </tr>
                ) : (
                  valuations.map(valuation => (
                    <tr key={valuation.id}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {new Date(valuation.date).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${valuation.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-500">
                        {valuation.notes || '-'}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Modal placeholders for adding transactions and valuations */}
      {showAddTransactionForm && (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-lg font-medium">Record Transaction</h3>
              <button
                onClick={() => setShowAddTransactionForm(false)}
                className="text-gray-500 hover:text-gray-700"
              >
                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <p className="text-center text-gray-500 mt-4">Transaction form placeholder - will be implemented separately</p>
          </div>
        </div>
      )}

      {showAddValuationForm && (
        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-lg font-medium">Update Valuation</h3>
              <button
                onClick={() => setShowAddValuationForm(false)}
                className="text-gray-500 hover:text-gray-700"
              >
                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <p className="text-center text-gray-500 mt-4">Valuation form placeholder - will be implemented separately</p>
          </div>
        </div>
      )}
    </div>
  );
};

export default InvestmentDetailPage;

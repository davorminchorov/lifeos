import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import TransactionForm from '../../components/investments/TransactionForm';
import ValuationForm from '../../components/investments/ValuationForm';

interface Investment {
  id: string;
  name: string;
  type: string;
  institution: string;
  account_number: string;
  current_value: number;
  notes: string;
  created_at: string;
  updated_at: string;
}

interface Transaction {
  id: string;
  investment_id: string;
  type: string;
  amount: number;
  date: string;
  notes: string;
  created_at: string;
}

interface Valuation {
  id: string;
  investment_id: string;
  value: number;
  date: string;
  notes: string;
  created_at: string;
}

const InvestmentDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const [investment, setInvestment] = useState<Investment | null>(null);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [valuations, setValuations] = useState<Valuation[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState('details');
  const [showTransactionForm, setShowTransactionForm] = useState(false);
  const [showValuationForm, setShowValuationForm] = useState(false);

  const fetchInvestment = async () => {
    try {
      const response = await axios.get(`/api/investments/${id}`);
      setInvestment(response.data);
    } catch (err) {
      console.error('Error fetching investment:', err);
      setError('Failed to load investment details.');
    }
  };

  const fetchTransactions = async () => {
    try {
      const response = await axios.get(`/api/investments/${id}/transactions`);
      setTransactions(response.data);
    } catch (err) {
      console.error('Error fetching transactions:', err);
    }
  };

  const fetchValuations = async () => {
    try {
      const response = await axios.get(`/api/investments/${id}/valuations`);
      setValuations(response.data);
    } catch (err) {
      console.error('Error fetching valuations:', err);
    }
  };

  const loadData = async () => {
    setLoading(true);
    await Promise.all([
      fetchInvestment(),
      fetchTransactions(),
      fetchValuations(),
    ]);
    setLoading(false);
  };

  useEffect(() => {
    loadData();
  }, [id]);

  const handleTransactionAdded = () => {
    setShowTransactionForm(false);
    fetchTransactions();
    fetchInvestment(); // Refresh investment details as well
  };

  const handleValuationAdded = () => {
    setShowValuationForm(false);
    fetchValuations();
    fetchInvestment(); // Refresh investment details as well
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
      </div>
    );
  }

  if (error || !investment) {
    return (
      <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
        <p>{error || 'Investment not found.'}</p>
        <p className="mt-2">
          <Link to="/investments" className="text-indigo-600 hover:text-indigo-900">
            Return to investments
          </Link>
        </p>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-semibold text-gray-900">{investment.name}</h1>
          <p className="text-sm text-gray-500">
            {investment.type} • {investment.institution}
          </p>
        </div>
        <Link
          to="/investments"
          className="text-indigo-600 hover:text-indigo-900 flex items-center"
        >
          <svg
            className="h-5 w-5 mr-1"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
          >
            <path
              fillRule="evenodd"
              d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
              clipRule="evenodd"
            />
          </svg>
          Back to Investments
        </Link>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('details')}
            className={`${
              activeTab === 'details'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`}
          >
            Details
          </button>
          <button
            onClick={() => setActiveTab('transactions')}
            className={`${
              activeTab === 'transactions'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`}
          >
            Transactions
          </button>
          <button
            onClick={() => setActiveTab('valuations')}
            className={`${
              activeTab === 'valuations'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm`}
          >
            Valuations
          </button>
        </nav>
      </div>

      {/* Details Tab */}
      {activeTab === 'details' && (
        <div className="bg-white shadow overflow-hidden sm:rounded-lg">
          <div className="px-4 py-5 sm:px-6">
            <h3 className="text-lg leading-6 font-medium text-gray-900">Investment Details</h3>
            <p className="mt-1 max-w-2xl text-sm text-gray-500">
              Details and information about this investment account.
            </p>
          </div>
          <div className="border-t border-gray-200">
            <dl>
              <div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt className="text-sm font-medium text-gray-500">Name</dt>
                <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{investment.name}</dd>
              </div>
              <div className="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt className="text-sm font-medium text-gray-500">Type</dt>
                <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{investment.type}</dd>
              </div>
              <div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt className="text-sm font-medium text-gray-500">Institution</dt>
                <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{investment.institution}</dd>
              </div>
              <div className="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt className="text-sm font-medium text-gray-500">Account Number</dt>
                <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  {investment.account_number}
                </dd>
              </div>
              <div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt className="text-sm font-medium text-gray-500">Current Value</dt>
                <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  ${investment.current_value.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                  })}
                </dd>
              </div>
              {investment.notes && (
                <div className="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                  <dt className="text-sm font-medium text-gray-500">Notes</dt>
                  <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{investment.notes}</dd>
                </div>
              )}
            </dl>
          </div>
        </div>
      )}

      {/* Transactions Tab */}
      {activeTab === 'transactions' && (
        <div>
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-medium text-gray-900">Transactions</h3>
            <button
              onClick={() => setShowTransactionForm(true)}
              className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Add Transaction
            </button>
          </div>

          {showTransactionForm ? (
            <div className="bg-white shadow overflow-hidden sm:rounded-lg p-4 mb-6">
              <h4 className="text-lg font-medium text-gray-900 mb-4">New Transaction</h4>
              <TransactionForm
                investmentId={id || ''}
                onTransactionAdded={handleTransactionAdded}
                onCancel={() => setShowTransactionForm(false)}
              />
            </div>
          ) : transactions.length > 0 ? (
            <div className="bg-white shadow overflow-hidden sm:rounded-lg">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Date
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Type
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Amount
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Notes
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {transactions.map((transaction) => (
                    <tr key={transaction.id}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {new Date(transaction.date).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span
                          className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                            transaction.type === 'deposit'
                              ? 'bg-green-100 text-green-800'
                              : transaction.type === 'withdrawal'
                              ? 'bg-red-100 text-red-800'
                              : 'bg-blue-100 text-blue-800'
                          }`}
                        >
                          {transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${transaction.amount.toLocaleString(undefined, {
                          minimumFractionDigits: 2,
                          maximumFractionDigits: 2,
                        })}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                        {transaction.notes || '-'}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center text-gray-500">
              No transactions recorded for this investment.
            </div>
          )}
        </div>
      )}

      {/* Valuations Tab */}
      {activeTab === 'valuations' && (
        <div>
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-medium text-gray-900">Valuations</h3>
            <button
              onClick={() => setShowValuationForm(true)}
              className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Add Valuation
            </button>
          </div>

          {showValuationForm ? (
            <div className="bg-white shadow overflow-hidden sm:rounded-lg p-4 mb-6">
              <h4 className="text-lg font-medium text-gray-900 mb-4">New Valuation</h4>
              <ValuationForm
                investmentId={id || ''}
                onValuationAdded={handleValuationAdded}
                onCancel={() => setShowValuationForm(false)}
              />
            </div>
          ) : valuations.length > 0 ? (
            <div className="bg-white shadow overflow-hidden sm:rounded-lg">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Date
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Value
                    </th>
                    <th
                      scope="col"
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Notes
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {valuations.map((valuation) => (
                    <tr key={valuation.id}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {new Date(valuation.date).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${valuation.value.toLocaleString(undefined, {
                          minimumFractionDigits: 2,
                          maximumFractionDigits: 2,
                        })}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                        {valuation.notes || '-'}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="bg-white shadow overflow-hidden sm:rounded-lg p-6 text-center text-gray-500">
              No valuations recorded for this investment.
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default InvestmentDetail;

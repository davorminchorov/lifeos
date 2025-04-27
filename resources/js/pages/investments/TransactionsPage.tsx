import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Button } from '../../ui/Button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { ArrowDownCircle, ArrowUpCircle, PiggyBank, Banknote, AlertCircle, Filter, Download } from 'lucide-react';

interface Transaction {
  id: string;
  investment_id: string;
  investment_name: string;
  type: 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee';
  amount: number;
  date: string;
  notes?: string;
}

interface TransactionFilters {
  type?: string;
  investment_id?: string;
  dateFrom?: string;
  dateTo?: string;
}

const transactionTypeConfig = {
  deposit: {
    label: 'Deposit',
    icon: ArrowDownCircle,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
    badgeColor: 'bg-green-100 text-green-800'
  },
  withdrawal: {
    label: 'Withdrawal',
    icon: ArrowUpCircle,
    color: 'text-red-600',
    bgColor: 'bg-red-50',
    badgeColor: 'bg-red-100 text-red-800'
  },
  dividend: {
    label: 'Dividend',
    icon: PiggyBank,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
    badgeColor: 'bg-blue-100 text-blue-800'
  },
  interest: {
    label: 'Interest',
    icon: Banknote,
    color: 'text-purple-600',
    bgColor: 'bg-purple-50',
    badgeColor: 'bg-purple-100 text-purple-800'
  },
  fee: {
    label: 'Fee',
    icon: AlertCircle,
    color: 'text-orange-600',
    bgColor: 'bg-orange-50',
    badgeColor: 'bg-orange-100 text-orange-800'
  }
};

const TransactionsPage: React.FC = () => {
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [investments, setInvestments] = useState<{id: string, name: string}[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filters, setFilters] = useState<TransactionFilters>({});
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        setError('');

        // Fetch investments for filter dropdown
        const investmentsResponse = await axios.get('/api/investments');
        setInvestments(
          Array.isArray(investmentsResponse.data)
            ? investmentsResponse.data.map((inv: any) => ({ id: inv.id, name: inv.name }))
            : []
        );

        // Fetch transactions with any applied filters
        const params = new URLSearchParams();
        if (filters.type) params.append('type', filters.type);
        if (filters.investment_id) params.append('investment_id', filters.investment_id);
        if (filters.dateFrom) params.append('date_from', filters.dateFrom);
        if (filters.dateTo) params.append('date_to', filters.dateTo);

        const transactionsResponse = await axios.get(`/api/transactions?${params.toString()}`);
        setTransactions(Array.isArray(transactionsResponse.data) ? transactionsResponse.data : []);
      } catch (err) {
        console.error('Failed to load transactions data', err);
        setError('Failed to load transactions data. Please try again.');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [filters]);

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({
      ...prev,
      [name]: value || undefined // Convert empty strings to undefined
    }));
  };

  const clearFilters = () => {
    setFilters({});
  };

  const exportToCSV = () => {
    // Create CSV content
    const headers = ['Date', 'Investment', 'Type', 'Amount', 'Notes'];

    const csvRows = [
      headers.join(','),
      ...transactions.map(tx => [
        tx.date,
        `"${tx.investment_name}"`,
        transactionTypeConfig[tx.type].label,
        tx.amount.toFixed(2),
        tx.notes ? `"${tx.notes.replace(/"/g, '""')}"` : ''
      ].join(','))
    ];

    const csvContent = csvRows.join('\n');

    // Create and download the file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `investment_transactions_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Calculate summary statistics
  const transactionSummary = transactions.reduce((acc, tx) => {
    if (tx.type === 'deposit' || tx.type === 'dividend' || tx.type === 'interest') {
      acc.totalInflow += tx.amount;
    } else {
      acc.totalOutflow += tx.amount;
    }

    acc.counts[tx.type] = (acc.counts[tx.type] || 0) + 1;

    return acc;
  }, {
    totalInflow: 0,
    totalOutflow: 0,
    counts: {} as Record<string, number>
  });

  // Sort transactions by date (most recent first)
  const sortedTransactions = [...transactions].sort(
    (a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()
  );

  return (
    <div className="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="text-3xl font-bold tracking-tight text-gray-900">Transactions</h1>
          <p className="mt-2 text-sm text-gray-500">
            View and manage all investment transactions
          </p>
        </div>
        <div className="flex items-center space-x-3">
          <Button
            onClick={() => setShowFilters(!showFilters)}
            variant="outline"
            className="flex items-center gap-2"
          >
            <Filter className="h-4 w-4" />
            {showFilters ? 'Hide Filters' : 'Show Filters'}
          </Button>
          <Button
            onClick={exportToCSV}
            variant="outline"
            className="flex items-center gap-2"
          >
            <Download className="h-4 w-4" />
            Export CSV
          </Button>
        </div>
      </div>

      {/* Filters */}
      {showFilters && (
        <Card className="mb-8 bg-white shadow-sm rounded-xl overflow-hidden">
          <CardContent className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-1">
                  Transaction Type
                </label>
                <select
                  id="type"
                  name="type"
                  value={filters.type || ''}
                  onChange={handleFilterChange}
                  className="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">All Types</option>
                  <option value="deposit">Deposits</option>
                  <option value="withdrawal">Withdrawals</option>
                  <option value="dividend">Dividends</option>
                  <option value="interest">Interest</option>
                  <option value="fee">Fees</option>
                </select>
              </div>

              <div>
                <label htmlFor="investment_id" className="block text-sm font-medium text-gray-700 mb-1">
                  Investment
                </label>
                <select
                  id="investment_id"
                  name="investment_id"
                  value={filters.investment_id || ''}
                  onChange={handleFilterChange}
                  className="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">All Investments</option>
                  {investments.map(inv => (
                    <option key={inv.id} value={inv.id}>{inv.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label htmlFor="dateFrom" className="block text-sm font-medium text-gray-700 mb-1">
                  From Date
                </label>
                <input
                  type="date"
                  id="dateFrom"
                  name="dateFrom"
                  value={filters.dateFrom || ''}
                  onChange={handleFilterChange}
                  className="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label htmlFor="dateTo" className="block text-sm font-medium text-gray-700 mb-1">
                  To Date
                </label>
                <input
                  type="date"
                  id="dateTo"
                  name="dateTo"
                  value={filters.dateTo || ''}
                  onChange={handleFilterChange}
                  className="block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
            </div>

            <div className="mt-4 flex justify-end">
              <Button
                onClick={clearFilters}
                variant="outline"
                size="sm"
                className="text-sm"
              >
                Clear Filters
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
          <CardContent className="p-5">
            <div className="flex items-center space-x-4">
              <div className="flex-shrink-0 p-3 bg-green-50 rounded-full">
                <ArrowDownCircle className="h-6 w-6 text-green-600" />
              </div>
              <div>
                <p className="text-sm font-medium text-gray-500">Total Inflows</p>
                <h3 className="text-xl font-bold text-gray-900 mt-1">
                  {formatCurrency(transactionSummary.totalInflow, 'USD')}
                </h3>
                <p className="text-xs text-gray-500 mt-1">
                  Deposits, dividends, and interest
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
          <CardContent className="p-5">
            <div className="flex items-center space-x-4">
              <div className="flex-shrink-0 p-3 bg-red-50 rounded-full">
                <ArrowUpCircle className="h-6 w-6 text-red-600" />
              </div>
              <div>
                <p className="text-sm font-medium text-gray-500">Total Outflows</p>
                <h3 className="text-xl font-bold text-gray-900 mt-1">
                  {formatCurrency(transactionSummary.totalOutflow, 'USD')}
                </h3>
                <p className="text-xs text-gray-500 mt-1">
                  Withdrawals and fees
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="bg-white shadow-sm hover:shadow transition-shadow rounded-xl overflow-hidden">
          <CardContent className="p-5">
            <div className="flex items-center space-x-4">
              <div className="flex-shrink-0 p-3 bg-indigo-50 rounded-full">
                <PiggyBank className="h-6 w-6 text-indigo-600" />
              </div>
              <div>
                <p className="text-sm font-medium text-gray-500">Net Flow</p>
                <h3 className="text-xl font-bold text-gray-900 mt-1">
                  {formatCurrency(transactionSummary.totalInflow - transactionSummary.totalOutflow, 'USD')}
                </h3>
                <p className="text-xs text-gray-500 mt-1">
                  {transactions.length} transactions total
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Transactions Table */}
      <Card className="bg-white shadow-sm rounded-xl overflow-hidden">
        <CardHeader className="bg-gray-50 border-b border-gray-100 px-6 py-4">
          <CardTitle className="text-lg font-semibold text-gray-800">All Transactions</CardTitle>
          <CardDescription className="text-sm text-gray-500 mt-1">
            Complete record of investment cash flows
          </CardDescription>
        </CardHeader>
        <CardContent className="p-0">
          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
          ) : error ? (
            <div className="bg-red-50 border-l-4 border-red-400 p-4 m-6">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          ) : transactions.length === 0 ? (
            <div className="py-12 px-6 text-center">
              <div className="flex justify-center">
                <div className="bg-gray-50 rounded-full p-3">
                  <Banknote className="h-8 w-8 text-gray-400" />
                </div>
              </div>
              <h3 className="mt-4 text-lg font-medium text-gray-900">No transactions found</h3>
              <p className="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
                {Object.keys(filters).length > 0
                  ? 'Try changing your filters to see more results.'
                  : 'Start recording transactions for your investments to track performance.'}
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow className="bg-gray-50">
                    <TableHead className="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</TableHead>
                    <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investment</TableHead>
                    <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</TableHead>
                    <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</TableHead>
                    <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</TableHead>
                    <TableHead className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {sortedTransactions.map(transaction => {
                    const config = transactionTypeConfig[transaction.type];
                    const Icon = config.icon;

                    return (
                      <TableRow
                        key={transaction.id}
                        className="hover:bg-gray-50 border-b border-gray-100 last:border-0"
                      >
                        <TableCell className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {formatDate(transaction.date)}
                        </TableCell>
                        <TableCell className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                          <Link to={`/investments/${transaction.investment_id}`} className="text-indigo-600 hover:text-indigo-900">
                            {transaction.investment_name}
                          </Link>
                        </TableCell>
                        <TableCell className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <div className={`flex-shrink-0 p-1.5 ${config.bgColor} rounded-full mr-2`}>
                              <Icon className={`h-4 w-4 ${config.color}`} />
                            </div>
                            <Badge className={config.badgeColor}>
                              {config.label}
                            </Badge>
                          </div>
                        </TableCell>
                        <TableCell className={`px-6 py-4 whitespace-nowrap text-sm font-medium ${
                          transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                            ? 'text-green-700'
                            : 'text-red-700'
                        }`}>
                          {transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                            ? '+'
                            : '-'
                          }
                          {formatCurrency(Math.abs(transaction.amount), 'USD')}
                        </TableCell>
                        <TableCell className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                          {transaction.notes || '-'}
                        </TableCell>
                        <TableCell className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                          <Link
                            to={`/investments/${transaction.investment_id}/transactions/${transaction.id}`}
                            className="text-indigo-600 hover:text-indigo-900"
                          >
                            View
                          </Link>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default TransactionsPage;

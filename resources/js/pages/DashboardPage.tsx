import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../ui/Card';
import { Button } from '../ui/Button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/Tabs';
import { CircleDollarSign, TrendingUp, PiggyBank, LineChart, Plus, ChevronRight } from 'lucide-react';
import { formatCurrency, formatDate } from '../utils/format';
import InvestmentService, { PortfolioSummary, Transaction } from '../services/InvestmentService';

const DashboardPage: React.FC = () => {
  const [portfolioSummary, setPortfolioSummary] = useState<PortfolioSummary | null>(null);
  const [recentTransactions, setRecentTransactions] = useState<Transaction[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const loadDashboardData = async () => {
      try {
        setLoading(true);
        setError('');

        // Fetch portfolio summary
        const summary = await InvestmentService.getPortfolioSummary();
        setPortfolioSummary(summary);

        // Fetch recent transactions
        const transactions = await InvestmentService.getTransactions({ limit: 5 });
        setRecentTransactions(transactions);

      } catch (err) {
        console.error('Failed to load dashboard data', err);
        setError('Failed to load dashboard data. Please try again.');
      } finally {
        setLoading(false);
      }
    };

    loadDashboardData();
  }, []);

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-6 bg-error-container text-on-error-container border border-error rounded-lg">
        <p>{error}</p>
        <Button
          onClick={() => window.location.reload()}
          variant="outlined"
          className="mt-4"
        >
          Retry
        </Button>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-bold text-on-surface">Financial Dashboard</h1>
        <div className="space-x-3">
          <Button asChild variant="outlined" className="flex items-center gap-2">
            <Link to="/investments">
              <PiggyBank className="h-4 w-4" />
              Investments
            </Link>
          </Button>
          <Button asChild variant="filled" className="flex items-center gap-2">
            <Link to="/investments/new">
              <Plus className="h-4 w-4" />
              Add Investment
            </Link>
          </Button>
        </div>
      </div>

      {/* Overview Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Card className="overflow-hidden">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium text-on-surface">Total Portfolio</h3>
              <div className="p-2 bg-primary-container rounded-full">
                <CircleDollarSign className="h-6 w-6 text-on-primary-container" />
              </div>
            </div>
            <div className="space-y-1">
              <p className="text-2xl font-bold text-on-surface">
                {portfolioSummary ? formatCurrency(portfolioSummary.total_current_value || 0, 'USD') : '$0.00'}
              </p>
              <p className="text-sm text-on-surface-variant">
                {portfolioSummary && portfolioSummary.total_current_value > portfolioSummary.total_invested ? (
                  <span className="text-tertiary font-medium">
                    +{formatCurrency((portfolioSummary.total_current_value || 0) - (portfolioSummary.total_invested || 0), 'USD')}
                  </span>
                ) : portfolioSummary && portfolioSummary.total_current_value < portfolioSummary.total_invested ? (
                  <span className="text-error font-medium">
                    -{formatCurrency((portfolioSummary.total_invested || 0) - (portfolioSummary.total_current_value || 0), 'USD')}
                  </span>
                ) : (
                  <span className="text-on-surface-variant">$0.00</span>
                )}
                {' '}since inception
              </p>
            </div>
          </CardContent>
        </Card>

        <Card className="overflow-hidden">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium text-on-surface">Overall Return</h3>
              <div className="p-2 bg-tertiary-container rounded-full">
                <TrendingUp className="h-6 w-6 text-on-tertiary-container" />
              </div>
            </div>
            <div className="space-y-1">
              <p className="text-2xl font-bold text-on-surface">
                {portfolioSummary ?
                  (portfolioSummary.overall_roi > 0 ? '+' : '') + portfolioSummary.overall_roi.toFixed(2) + '%'
                  : '0.00%'}
              </p>
              <p className="text-sm text-on-surface-variant">
                Based on {portfolioSummary?.total_investments || 0} investments
              </p>
            </div>
          </CardContent>
        </Card>

        <Card className="overflow-hidden">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium text-on-surface">Total Invested</h3>
              <div className="p-2 bg-secondary-container rounded-full">
                <PiggyBank className="h-6 w-6 text-on-secondary-container" />
              </div>
            </div>
            <div className="space-y-1">
              <p className="text-2xl font-bold text-on-surface">
                {portfolioSummary ? formatCurrency(portfolioSummary.total_invested || 0, 'USD') : '$0.00'}
              </p>
              <p className="text-sm text-on-surface-variant">
                Across {portfolioSummary?.total_investments || 0} investments
              </p>
            </div>
          </CardContent>
        </Card>

        <Card className="overflow-hidden">
          <CardContent className="p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium text-on-surface">Withdrawals</h3>
              <div className="p-2 bg-secondary-container rounded-full">
                <LineChart className="h-6 w-6 text-on-secondary-container" />
              </div>
            </div>
            <div className="space-y-1">
              <p className="text-2xl font-bold text-on-surface">
                {portfolioSummary ? formatCurrency(portfolioSummary.total_withdrawn || 0, 'USD') : '$0.00'}
              </p>
              <p className="text-sm text-on-surface-variant">
                Total withdrawn amount
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Tabs for Recent Activity and Portfolio Details */}
      <Tabs defaultValue="activity" className="mb-8">
        <TabsList className="mb-6 bg-surface-variant p-1 rounded-lg">
          <TabsTrigger value="activity" className="rounded-md px-4 py-2">Recent Activity</TabsTrigger>
          <TabsTrigger value="allocation" className="rounded-md px-4 py-2">Portfolio Allocation</TabsTrigger>
        </TabsList>

        <TabsContent value="activity" className="mt-0">
          <Card variant="elevated" className="overflow-hidden">
            <CardHeader className="bg-surface-variant px-6 py-4 border-b border-outline border-opacity-20">
              <CardTitle>Recent Transactions</CardTitle>
              <CardDescription>Latest updates to your investments</CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              {recentTransactions.length === 0 ? (
                <div className="py-12 text-center">
                  <p className="text-on-surface-variant">No recent transactions found.</p>
                  <Button asChild variant="tonal" className="mt-4">
                    <Link to="/investments/transactions">Add Transaction</Link>
                  </Button>
                </div>
              ) : (
                <div className="divide-y divide-outline divide-opacity-20">
                  {recentTransactions.map(transaction => (
                    <div key={transaction.id} className="px-6 py-4 flex items-center justify-between">
                      <div className="flex items-center space-x-4">
                        <div className={`p-2 rounded-full ${
                          transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                            ? 'bg-green-50'
                            : 'bg-red-50'
                        }`}>
                          <CircleDollarSign className={`h-5 w-5 ${
                            transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                              ? 'text-green-600'
                              : 'text-red-600'
                          }`} />
                        </div>
                        <div>
                          <p className="font-medium text-gray-900">
                            {transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                          </p>
                          <p className="text-sm text-gray-500">
                            {transaction.investment_name} • {formatDate(transaction.date)}
                          </p>
                        </div>
                      </div>
                      <div className="text-right">
                        <p className={`font-semibold ${
                          transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                            ? 'text-green-600'
                            : 'text-red-600'
                        }`}>
                          {transaction.type === 'deposit' || transaction.type === 'dividend' || transaction.type === 'interest'
                            ? '+'
                            : '-'
                          }
                          {formatCurrency(transaction.amount || 0, 'USD')}
                        </p>
                      </div>
                    </div>
                  ))}
                  <div className="px-6 py-4 bg-gray-50">
                    <Link to="/investments/transactions" className="text-indigo-600 hover:text-indigo-800 flex items-center justify-center font-medium">
                      View All Transactions
                      <ChevronRight className="h-4 w-4 ml-1" />
                    </Link>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="allocation" className="mt-0">
          <Card className="bg-white shadow-md rounded-xl overflow-hidden">
            <CardHeader className="bg-gray-50 px-6 py-4 border-b border-gray-100">
              <CardTitle>Portfolio Allocation</CardTitle>
              <CardDescription>Breakdown by investment type</CardDescription>
            </CardHeader>
            <CardContent className="p-6">
              {portfolioSummary && Object.keys(portfolioSummary.by_type).length > 0 ? (
                <div>
                  {/* Allocation bar visualization */}
                  <div className="h-8 w-full rounded-md overflow-hidden flex mb-6">
                    {Object.entries(portfolioSummary.by_type)
                      .sort((a, b) => b[1].percentage - a[1].percentage)
                      .map(([type, data]) => (
                        <div
                          key={type}
                          className="h-full"
                          style={{
                            width: `${data.percentage}%`,
                            backgroundColor: getTypeColor(type),
                            minWidth: data.percentage < 3 ? '3%' : undefined
                          }}
                          title={`${type}: ${data.percentage.toFixed(1)}%`}
                        ></div>
                      ))}
                  </div>

                  {/* Allocation details */}
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {Object.entries(portfolioSummary.by_type)
                      .sort((a, b) => b[1].value - a[1].value)
                      .map(([type, data]) => (
                        <div key={type} className="flex items-center space-x-3">
                          <div
                            className="w-4 h-4 rounded-full flex-shrink-0"
                            style={{ backgroundColor: getTypeColor(type) }}
                          ></div>
                          <div className="flex-1">
                            <p className="text-sm font-medium text-gray-900">
                              {type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ')}
                            </p>
                            <div className="flex justify-between">
                              <span className="text-xs text-gray-500">
                                {data.count} {data.count === 1 ? 'investment' : 'investments'}
                              </span>
                              <span className="text-xs font-medium">
                                {data.percentage.toFixed(1)}%
                              </span>
                            </div>
                          </div>
                          <div className="text-right">
                            <p className="text-sm font-semibold text-gray-900">
                              {formatCurrency(data.value || 0, 'USD')}
                            </p>
                          </div>
                        </div>
                      ))}
                  </div>
                </div>
              ) : (
                <div className="py-12 text-center">
                  <p className="text-gray-500">No portfolio allocation data available.</p>
                  <Button asChild variant="outline" className="mt-4">
                    <Link to="/investments/new">Add Investment</Link>
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      {/* Quick Actions */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card className="bg-white shadow-md rounded-xl overflow-hidden">
          <CardContent className="p-6">
            <div className="flex flex-col items-center text-center">
              <div className="p-3 bg-green-50 rounded-full mb-4">
                <Plus className="h-6 w-6 text-green-600" />
              </div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">Add Investment</h3>
              <p className="text-sm text-gray-500 mb-4">
                Record a new investment in your portfolio
              </p>
              <Button asChild className="w-full bg-green-600 hover:bg-green-700 text-white">
                <Link to="/investments/new">Add Investment</Link>
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card className="bg-white shadow-md rounded-xl overflow-hidden">
          <CardContent className="p-6">
            <div className="flex flex-col items-center text-center">
              <div className="p-3 bg-blue-50 rounded-full mb-4">
                <CircleDollarSign className="h-6 w-6 text-blue-600" />
              </div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">Record Transaction</h3>
              <p className="text-sm text-gray-500 mb-4">
                Add deposits, withdrawals or dividends
              </p>
              <Button asChild className="w-full bg-blue-600 hover:bg-blue-700 text-white">
                <Link to="/investments/transactions">Manage Transactions</Link>
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card className="bg-white shadow-md rounded-xl overflow-hidden">
          <CardContent className="p-6">
            <div className="flex flex-col items-center text-center">
              <div className="p-3 bg-purple-50 rounded-full mb-4">
                <LineChart className="h-6 w-6 text-purple-600" />
              </div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">View Performance</h3>
              <p className="text-sm text-gray-500 mb-4">
                Analyze your investment performance
              </p>
              <Button asChild className="w-full bg-purple-600 hover:bg-purple-700 text-white">
                <Link to="/investments/list">View All Investments</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

// Helper function to get colors for investment types
function getTypeColor(type: string): string {
  const colorMap: Record<string, string> = {
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

  return colorMap[type] || colorMap.other;
}

export default DashboardPage;

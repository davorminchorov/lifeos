import React, { useState, useEffect } from 'react';
import { Link, useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../ui/Tabs';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { formatCurrency } from '../../utils/format';
import { ArrowLeft, Edit, PlusCircle, TrendingUp, DollarSign, Calendar, FileText, BarChart, LineChart, ArrowUp, ArrowDown } from 'lucide-react';
import TransactionForm from '../../components/investments/TransactionForm';
import ValuationForm from '../../components/investments/ValuationForm';

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

const getTransactionBadge = (type: string) => {
  switch (type) {
    case 'deposit':
      return <Badge variant="success">Deposit</Badge>;
    case 'withdrawal':
      return <Badge variant="danger">Withdrawal</Badge>;
    case 'dividend':
      return <Badge variant="secondary">Dividend</Badge>;
    case 'fee':
      return <Badge variant="warning">Fee</Badge>;
    case 'interest':
      return <Badge variant="outline">Interest</Badge>;
    default:
      return <Badge variant="default">{type}</Badge>;
  }
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

  // Define fetchData function outside of useEffect for reuse
  const refreshData = async () => {
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

  if (loading) {
    return (
      <PageContainer title="Investment Details">
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      </PageContainer>
    );
  }

  if (error || !investment) {
    return (
      <PageContainer title="Error">
        <Card variant="elevated">
          <CardContent>
            <div className="bg-error/10 text-error p-4 rounded-lg mb-4">
              {error || 'Investment not found'}
            </div>
            <Button variant="outlined" onClick={handleBack}>Back to Investments</Button>
          </CardContent>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer
      title={investment.name}
      subtitle={`${typeLabels[investment.type] || investment.type} • ${investment.institution}`}
      actions={
        <div className="flex space-x-3">
          <Button
            variant="outlined"
            onClick={() => navigate(`/investments/${id}/edit`)}
            icon={<Edit className="h-4 w-4 mr-2" />}
          >
            Edit
          </Button>
          <Button
            variant="filled"
            onClick={() => setShowAddTransactionForm(true)}
            icon={<PlusCircle className="h-4 w-4 mr-2" />}
          >
            Record Transaction
          </Button>
          <Button
            variant="outlined"
            onClick={() => setShowAddValuationForm(true)}
            icon={<TrendingUp className="h-4 w-4 mr-2" />}
          >
            Update Valuation
          </Button>
        </div>
      }
    >
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full mb-6">
        <TabsList className="grid grid-cols-3">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="transactions">
            Transactions
            {transactions.length > 0 && (
              <Badge variant="secondary" className="ml-2">
                {transactions.length}
              </Badge>
            )}
          </TabsTrigger>
          <TabsTrigger value="valuations">
            Valuations
            {valuations.length > 0 && (
              <Badge variant="secondary" className="ml-2">
                {valuations.length}
              </Badge>
            )}
          </TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="mt-4">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div className="md:col-span-8">
              <Card variant="elevated">
                <CardHeader>
                  <CardTitle>Investment Details</CardTitle>
                  <CardDescription>Summary of your investment information</CardDescription>
                </CardHeader>
                <CardContent>
                  <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <Calendar className="h-4 w-4 mr-1" />
                        Start Date
                      </dt>
                      <dd className="text-on-surface font-medium">
                        {new Date(investment.start_date).toLocaleDateString()}
                      </dd>
                    </div>

                    {investment.end_date && (
                      <div className="space-y-1">
                        <dt className="text-on-surface-variant text-sm flex items-center">
                          <Calendar className="h-4 w-4 mr-1" />
                          End Date
                        </dt>
                        <dd className="text-on-surface font-medium">
                          {new Date(investment.end_date).toLocaleDateString()}
                        </dd>
                      </div>
                    )}

                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <DollarSign className="h-4 w-4 mr-1" />
                        Initial Investment
                      </dt>
                      <dd className="text-on-surface font-medium">
                        {formatCurrency(investment.initial_investment, 'USD')}
                      </dd>
                    </div>

                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <DollarSign className="h-4 w-4 mr-1" />
                        Current Value
                      </dt>
                      <dd className="text-on-surface font-medium">
                        {formatCurrency(investment.current_value, 'USD')}
                      </dd>
                    </div>

                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <ArrowUp className="h-4 w-4 mr-1" />
                        Total Deposited
                      </dt>
                      <dd className="text-on-surface font-medium">
                        {formatCurrency(investment.total_invested, 'USD')}
                      </dd>
                    </div>

                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <ArrowDown className="h-4 w-4 mr-1" />
                        Total Withdrawn
                      </dt>
                      <dd className="text-on-surface font-medium">
                        {formatCurrency(investment.total_withdrawn, 'USD')}
                      </dd>
                    </div>

                    {investment.account_number && (
                      <div className="space-y-1">
                        <dt className="text-on-surface-variant text-sm">Account Number</dt>
                        <dd className="text-on-surface font-medium">{investment.account_number}</dd>
                      </div>
                    )}

                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm">Last Updated</dt>
                      <dd className="text-on-surface font-medium">
                        {new Date(investment.last_valuation_date).toLocaleDateString()}
                      </dd>
                    </div>
                  </dl>

                  {investment.description && (
                    <div className="mt-6">
                      <h4 className="text-on-surface-variant text-sm flex items-center mb-2">
                        <FileText className="h-4 w-4 mr-1" />
                        Description
                      </h4>
                      <p className="text-on-surface bg-surface-variant p-3 rounded-md">
                        {investment.description}
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>

              {performance && (
                <Card variant="elevated" className="mt-6">
                  <CardHeader>
                    <CardTitle>Performance</CardTitle>
                    <CardDescription>Value trends and returns</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="h-48 flex items-center justify-center mb-6">
                      <LineChart className="h-full w-full text-primary opacity-20" />
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                      <div className="space-y-1">
                        <span className="text-on-surface-variant text-sm">Return on Investment</span>
                        <p className={`text-xl font-semibold ${performance.roi >= 0 ? 'text-success' : 'text-error'}`}>
                          {performance.roi >= 0 ? '+' : ''}{performance.roi.toFixed(2)}%
                        </p>
                      </div>
                      <div className="space-y-1">
                        <span className="text-on-surface-variant text-sm">Total Return</span>
                        <p className="text-xl font-semibold">
                          {formatCurrency(performance.total_return, 'USD')}
                        </p>
                      </div>
                      <div className="space-y-1">
                        <span className="text-on-surface-variant text-sm">Growth</span>
                        <p className="text-xl font-semibold">
                          {formatCurrency(performance.current_value - performance.initial_value, 'USD')}
                        </p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              )}
            </div>

            <div className="md:col-span-4">
              <Card variant="filled">
                <CardHeader>
                  <CardTitle>Current Value</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="flex flex-col items-center py-4">
                    <div className="text-4xl font-bold mb-4">
                      {formatCurrency(investment.current_value, 'USD')}
                    </div>
                    <div className="flex items-center space-x-1">
                      <div className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${investment.roi >= 0 ? 'bg-success/10 text-success' : 'bg-error/10 text-error'}`}>
                        {investment.roi >= 0 ? (
                          <ArrowUp className="h-3 w-3 mr-1" />
                        ) : (
                          <ArrowDown className="h-3 w-3 mr-1" />
                        )}
                        {Math.abs(investment.roi).toFixed(2)}%
                      </div>
                      <span className="text-on-surface-variant text-xs">since initial investment</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card variant="outlined" className="mt-6">
                <CardHeader>
                  <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <Button
                      variant="filled"
                      className="w-full"
                      onClick={() => setShowAddTransactionForm(true)}
                      icon={<PlusCircle className="h-4 w-4 mr-2" />}
                    >
                      Record Transaction
                    </Button>
                    <Button
                      variant="outlined"
                      className="w-full"
                      onClick={() => setShowAddValuationForm(true)}
                      icon={<TrendingUp className="h-4 w-4 mr-2" />}
                    >
                      Update Valuation
                    </Button>
                    <Button
                      variant="text"
                      className="w-full"
                      onClick={() => navigate(`/investments/${id}/edit`)}
                    >
                      Edit Investment
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="transactions" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Transaction History</CardTitle>
              <CardDescription>Record of deposits, withdrawals, and other transactions</CardDescription>
            </CardHeader>
            <CardContent>
              {transactions.length === 0 ? (
                <div className="text-center py-8">
                  <DollarSign className="h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" />
                  <p className="text-on-surface-variant mb-4">No transactions recorded yet</p>
                  <Button
                    variant="filled"
                    onClick={() => setShowAddTransactionForm(true)}
                    icon={<PlusCircle className="h-4 w-4 mr-2" />}
                  >
                    Record Transaction
                  </Button>
                </div>
              ) : (
                <>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Date</TableHead>
                        <TableHead>Type</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Notes</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {transactions
                        .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
                        .map((transaction) => (
                          <TableRow key={transaction.id}>
                            <TableCell>
                              {new Date(transaction.date).toLocaleDateString()}
                            </TableCell>
                            <TableCell>
                              {getTransactionBadge(transaction.type)}
                            </TableCell>
                            <TableCell className={`font-medium ${
                              transaction.type === 'withdrawal' || transaction.type === 'fee'
                                ? 'text-error'
                                : 'text-success'
                            }`}>
                              {transaction.type === 'withdrawal' || transaction.type === 'fee'
                                ? '-'
                                : '+'}{formatCurrency(transaction.amount, 'USD')}
                            </TableCell>
                            <TableCell>{transaction.notes || '-'}</TableCell>
                          </TableRow>
                        ))}
                    </TableBody>
                  </Table>
                  <div className="mt-6 flex justify-end">
                    <Button
                      variant="filled"
                      onClick={() => setShowAddTransactionForm(true)}
                      icon={<PlusCircle className="h-4 w-4 mr-2" />}
                    >
                      Add New Transaction
                    </Button>
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="valuations" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Valuation History</CardTitle>
              <CardDescription>Record of value changes over time</CardDescription>
            </CardHeader>
            <CardContent>
              {valuations.length === 0 ? (
                <div className="text-center py-8">
                  <TrendingUp className="h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" />
                  <p className="text-on-surface-variant mb-4">No valuations recorded yet</p>
                  <Button
                    variant="filled"
                    onClick={() => setShowAddValuationForm(true)}
                    icon={<TrendingUp className="h-4 w-4 mr-2" />}
                  >
                    Record Valuation
                  </Button>
                </div>
              ) : (
                <>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Date</TableHead>
                        <TableHead>Value</TableHead>
                        <TableHead>Change</TableHead>
                        <TableHead>Notes</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {valuations
                        .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
                        .map((valuation, index, arr) => {
                          const prevValue = index < arr.length - 1 ? arr[index + 1].value : null;
                          const change = prevValue !== null ? ((valuation.value - prevValue) / prevValue) * 100 : null;

                          return (
                            <TableRow key={valuation.id}>
                              <TableCell>
                                {new Date(valuation.date).toLocaleDateString()}
                              </TableCell>
                              <TableCell className="font-medium">
                                {formatCurrency(valuation.value, 'USD')}
                              </TableCell>
                              <TableCell>
                                {change !== null ? (
                                  <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${change >= 0 ? 'bg-success/10 text-success' : 'bg-error/10 text-error'}`}>
                                    {change >= 0 ? (
                                      <ArrowUp className="h-3 w-3 mr-1" />
                                    ) : (
                                      <ArrowDown className="h-3 w-3 mr-1" />
                                    )}
                                    {Math.abs(change).toFixed(2)}%
                                  </span>
                                ) : (
                                  'Initial'
                                )}
                              </TableCell>
                              <TableCell>{valuation.notes || '-'}</TableCell>
                            </TableRow>
                          );
                        })}
                    </TableBody>
                  </Table>
                  <div className="mt-6 flex justify-end">
                    <Button
                      variant="filled"
                      onClick={() => setShowAddValuationForm(true)}
                      icon={<TrendingUp className="h-4 w-4 mr-2" />}
                    >
                      Add New Valuation
                    </Button>
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      <div className="flex justify-between mt-8">
        <Button variant="outlined" onClick={handleBack} icon={<ArrowLeft className="h-4 w-4 mr-2" />}>
          Back to Investments
        </Button>
      </div>

      {showAddTransactionForm && investment && (
        <TransactionForm
          investmentId={investment.id}
          onSuccess={() => {
            setShowAddTransactionForm(false);
            refreshData();
          }}
        />
      )}

      {showAddValuationForm && investment && (
        <ValuationForm
          investmentId={investment.id}
          initialValue={investment.current_value}
          onSuccess={() => {
            setShowAddValuationForm(false);
            refreshData();
          }}
        />
      )}
    </PageContainer>
  );
};

export default InvestmentDetailPage;

import React from 'react';
import { Badge } from '../../ui/Badge';
import { formatCurrency, formatDate } from '../../utils/format';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../ui/Table';
import { ArrowDownCircle, ArrowUpCircle, PiggyBank, Banknote, AlertCircle } from 'lucide-react';

interface Transaction {
  id: string;
  type: 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee';
  amount: number;
  date: string;
  notes?: string;
}

interface TransactionsListProps {
  transactions: Transaction[];
  onEditTransaction?: (id: string) => void;
  onDeleteTransaction?: (id: string) => void;
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

const TransactionsList: React.FC<TransactionsListProps> = ({
  transactions,
  onEditTransaction,
  onDeleteTransaction
}) => {
  // Sort transactions by date, most recent first
  const sortedTransactions = [...transactions].sort(
    (a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()
  );

  return (
    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">
      <div className="px-6 py-4 border-b border-gray-100">
        <h3 className="text-lg font-semibold text-gray-800">Transaction History</h3>
        <p className="text-sm text-gray-500 mt-1">Record of all transactions for this investment</p>
      </div>

      {transactions.length === 0 ? (
        <div className="py-12 px-6 text-center">
          <div className="flex justify-center">
            <div className="bg-gray-50 rounded-full p-3">
              <Banknote className="h-8 w-8 text-gray-400" />
            </div>
          </div>
          <h3 className="mt-4 text-lg font-medium text-gray-900">No transactions yet</h3>
          <p className="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
            Record deposits, withdrawals, dividends, and other transactions to track your investment performance accurately.
          </p>
        </div>
      ) : (
        <div className="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow className="bg-gray-50">
                <TableHead className="w-48 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</TableHead>
                <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</TableHead>
                <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</TableHead>
                <TableHead className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</TableHead>
                {(onEditTransaction || onDeleteTransaction) && (
                  <TableHead className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</TableHead>
                )}
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
                    {(onEditTransaction || onDeleteTransaction) && (
                      <TableCell className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div className="flex justify-end space-x-3">
                          {onEditTransaction && (
                            <button
                              onClick={() => onEditTransaction(transaction.id)}
                              className="text-indigo-600 hover:text-indigo-900"
                            >
                              Edit
                            </button>
                          )}
                          {onDeleteTransaction && (
                            <button
                              onClick={() => onDeleteTransaction(transaction.id)}
                              className="text-red-600 hover:text-red-900"
                            >
                              Delete
                            </button>
                          )}
                        </div>
                      </TableCell>
                    )}
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </div>
      )}
    </div>
  );
};

export default TransactionsList;

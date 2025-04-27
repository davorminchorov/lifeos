import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { format } from 'date-fns';

interface Expense {
  expense_id: string;
  description: string;
  amount: number;
  category_id: string | null;
  category_name?: string;
  date: string;
  notes: string | null;
}

interface Category {
  category_id: string;
  name: string;
}

interface ExpensesListProps {
  refreshTrigger?: number;
}

export const ExpensesList: React.FC<ExpensesListProps> = ({ refreshTrigger = 0 }) => {
  const [expenses, setExpenses] = useState<Expense[]>([]);
  const [categories, setCategories] = useState<Record<string, Category>>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filterCategory, setFilterCategory] = useState('');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');

  useEffect(() => {
    // Fetch categories first to display names
    const fetchCategories = async () => {
      try {
        const response = await axios.get('/api/categories');
        const categoriesMap: Record<string, Category> = {};

        if (response.data && Array.isArray(response.data.data)) {
          response.data.data.forEach((category: Category) => {
            categoriesMap[category.category_id] = category;
          });
        }

        setCategories(categoriesMap);
      } catch (err) {
        console.error('Failed to fetch categories', err);
      }
    };

    fetchCategories();
  }, []);

  useEffect(() => {
    fetchExpenses();
  }, [refreshTrigger, filterCategory, startDate, endDate]);

  const fetchExpenses = async () => {
    setLoading(true);
    setError('');

    try {
      const params: Record<string, string> = {};
      if (filterCategory) {
        params.category_id = filterCategory;
      }
      if (startDate) {
        params.start_date = startDate;
      }
      if (endDate) {
        params.end_date = endDate;
      }

      const response = await axios.get('/api/expenses', { params });

      if (response.data && Array.isArray(response.data.data)) {
        // Add category names to expenses
        const expensesWithCategories = response.data.data.map((expense: Expense) => ({
          ...expense,
          category_name: expense.category_id ? categories[expense.category_id]?.name : 'Uncategorized'
        }));

        setExpenses(expensesWithCategories);
      } else {
        setExpenses([]);
      }
    } catch (err) {
      setError('Failed to load expenses');
      console.error('Failed to fetch expenses', err);
      setExpenses([]);
    } finally {
      setLoading(false);
    }
  };

  const handleCategorize = async (expenseId: string, categoryId: string) => {
    try {
      await axios.post(`/api/expenses/${expenseId}/categorize`, {
        category_id: categoryId,
      });

      // Update the local state
      setExpenses(expenses.map(expense =>
        expense.expense_id === expenseId
          ? {
              ...expense,
              category_id: categoryId,
              category_name: categories[categoryId].name
            }
          : expense
      ));
    } catch (err) {
      console.error('Failed to categorize expense', err);
    }
  };

  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    return format(new Date(dateString), 'MMM d, yyyy');
  };

  // Add export function
  const handleExport = () => {
    let url = '/api/expense-exports/csv';

    // Add any filters that are currently applied
    const params: string[] = [];
    if (filterCategory) {
      params.push(`category_id=${filterCategory}`);
    }
    if (startDate) {
      params.push(`start_date=${startDate}`);
    }
    if (endDate) {
      params.push(`end_date=${endDate}`);
    }

    if (params.length > 0) {
      url += '?' + params.join('&');
    }

    // Open in a new tab
    window.open(url, '_blank');
  };

  if (loading && expenses.length === 0) {
    return <div className="flex justify-center py-8">Loading expenses...</div>;
  }

  if (error) {
    return <div className="bg-red-50 text-red-600 p-4 rounded">{error}</div>;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-xl font-semibold">Your Expenses</h2>

        <div className="flex items-center">
          <button
            onClick={fetchExpenses}
            className="ml-2 text-blue-600 hover:text-blue-800 text-sm flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <a
            href="/reports"
            className="ml-4 text-blue-600 hover:text-blue-800 text-sm"
          >
            Export CSV
          </a>
        </div>
      </div>

      <div className="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Category
            </label>
            <select
              value={filterCategory}
              onChange={(e) => setFilterCategory(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2 bg-white"
            >
              <option value="">All Categories</option>
              {Object.values(categories).map((category) => (
                <option key={category.category_id} value={category.category_id}>
                  {category.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Start Date
            </label>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2 bg-white"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              End Date
            </label>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2 bg-white"
            />
          </div>
        </div>
      </div>

      {loading ? (
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-full mb-4"></div>
          <div className="h-8 bg-gray-200 rounded w-full mb-4"></div>
          <div className="h-8 bg-gray-200 rounded w-full mb-4"></div>
        </div>
      ) : error ? (
        <div className="p-8 text-center text-red-600 bg-red-50 rounded-lg border border-red-200">
          {error}
          <button
            onClick={fetchExpenses}
            className="block mx-auto mt-2 text-sm text-blue-600 hover:text-blue-800"
          >
            Try Again
          </button>
        </div>
      ) : expenses.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-10 bg-gray-50 rounded-lg border border-gray-200">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <p className="text-lg font-medium text-gray-600 mb-1">No expenses found</p>
          <p className="text-gray-500 text-center mb-4">Start by adding a new expense to track your spending.</p>
        </div>
      ) : (
        <div className="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Description
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Category
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {expenses.map((expense) => (
                <tr key={expense.expense_id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {formatDate(expense.date)}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-900">
                    {expense.description}
                    {expense.notes && (
                      <p className="text-xs text-gray-500 mt-1">{expense.notes}</p>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {expense.category_id ? (
                      <span className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {expense.category_name}
                      </span>
                    ) : (
                      <select
                        className="text-xs rounded border border-gray-300 px-2 py-1 bg-white"
                        onChange={(e) => handleCategorize(expense.expense_id, e.target.value)}
                        value=""
                      >
                        <option value="" disabled>
                          Categorize
                        </option>
                        {Object.values(categories).map((category) => (
                          <option key={category.category_id} value={category.category_id}>
                            {category.name}
                          </option>
                        ))}
                      </select>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                    {formatAmount(expense.amount)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-right">
                    <button
                      className="text-blue-600 hover:text-blue-900"
                      onClick={() => {/* Edit function would go here */}}
                    >
                      Edit
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

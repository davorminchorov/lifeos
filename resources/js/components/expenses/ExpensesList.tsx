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
        response.data.data.forEach((category: Category) => {
          categoriesMap[category.category_id] = category;
        });
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

      // Add category names to expenses
      const expensesWithCategories = response.data.data.map((expense: Expense) => ({
        ...expense,
        category_name: expense.category_id ? categories[expense.category_id]?.name : 'Uncategorized'
      }));

      setExpenses(expensesWithCategories);
    } catch (err) {
      setError('Failed to load expenses');
      console.error('Failed to fetch expenses', err);
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
    <div className="bg-white rounded-lg shadow">
      <div className="p-4 border-b">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-semibold">Your Expenses</h2>
          <button
            onClick={handleExport}
            className="text-blue-600 hover:text-blue-800 flex items-center"
          >
            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
          </button>
        </div>

        <div className="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Category
            </label>
            <select
              value={filterCategory}
              onChange={(e) => setFilterCategory(e.target.value)}
              className="w-full rounded-md border border-gray-300 px-3 py-2"
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
              className="w-full rounded-md border border-gray-300 px-3 py-2"
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
              className="w-full rounded-md border border-gray-300 px-3 py-2"
            />
          </div>
        </div>
      </div>

      {expenses.length === 0 ? (
        <div className="p-8 text-center text-gray-500">
          No expenses found. Start by adding a new expense.
        </div>
      ) : (
        <div className="overflow-x-auto">
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
                <tr key={expense.expense_id}>
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
                      expense.category_name
                    ) : (
                      <select
                        className="text-xs rounded border border-gray-300 px-2 py-1"
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
                  <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    {expense.category_id && (
                      <button
                        className="text-blue-600 hover:text-blue-900"
                        onClick={() => {
                          const newCategoryId = prompt(
                            `Change category for ${expense.description}`,
                            expense.category_id || ''
                          );
                          if (newCategoryId && newCategoryId !== expense.category_id) {
                            handleCategorize(expense.expense_id, newCategoryId);
                          }
                        }}
                      >
                        Change
                      </button>
                    )}
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

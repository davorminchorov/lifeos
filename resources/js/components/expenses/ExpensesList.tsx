import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { format } from 'date-fns';
import { Button } from '../../ui';

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

  return (
    <div>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-title-medium font-medium text-on-surface">Your Expenses</h2>

        <div className="flex items-center space-x-2">
          <Button
            onClick={fetchExpenses}
            variant="text"
            icon={
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clipRule="evenodd" />
              </svg>
            }
            size="sm"
          >
            Refresh
          </Button>

          <Button
            onClick={handleExport}
            variant="text"
            size="sm"
            icon={
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clipRule="evenodd" />
              </svg>
            }
          >
            Export CSV
          </Button>
        </div>
      </div>

      <div className="mb-6 bg-surface-container rounded-lg p-4 border border-outline/40 shadow-elevation-1">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-body-small font-medium text-on-surface-variant mb-1">
              Category
            </label>
            <select
              value={filterCategory}
              onChange={(e) => setFilterCategory(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
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
            <label className="block text-body-small font-medium text-on-surface-variant mb-1">
              Start Date
            </label>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
            />
          </div>

          <div>
            <label className="block text-body-small font-medium text-on-surface-variant mb-1">
              End Date
            </label>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
            />
          </div>
        </div>
      </div>

      {loading ? (
        <div className="animate-pulse space-y-4">
          <div className="h-8 bg-surface-variant/40 rounded w-full"></div>
          <div className="h-8 bg-surface-variant/40 rounded w-full"></div>
          <div className="h-8 bg-surface-variant/40 rounded w-full"></div>
        </div>
      ) : error ? (
        <div className="p-8 text-center text-error bg-error-container/50 rounded-lg border border-error/50 shadow-elevation-1">
          {error}
          <button
            onClick={fetchExpenses}
            className="block mx-auto mt-2 text-body-small text-primary hover:text-primary/80"
          >
            Try Again
          </button>
        </div>
      ) : expenses.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-10 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-on-surface-variant/40 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <p className="text-title-medium font-medium text-on-surface mb-1">No expenses found</p>
          <p className="text-body-medium text-on-surface-variant text-center mb-4 max-w-md">
            Create your first expense record to start tracking your spending or adjust your filters to see more results.
          </p>
        </div>
      ) : (
        <div className="overflow-x-auto border border-outline/40 rounded-lg shadow-elevation-1">
          <table className="min-w-full divide-y divide-outline/40">
            <thead className="bg-surface-container">
              <tr>
                <th scope="col" className="px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" className="px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                  Description
                </th>
                <th scope="col" className="px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                  Category
                </th>
                <th scope="col" className="px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                  Amount
                </th>
              </tr>
            </thead>
            <tbody className="bg-surface divide-y divide-outline/40">
              {expenses.map((expense) => (
                <tr key={expense.expense_id} className="hover:bg-surface-variant/20">
                  <td className="px-6 py-4 whitespace-nowrap text-body-medium text-on-surface">
                    {formatDate(expense.date)}
                  </td>
                  <td className="px-6 py-4 text-body-medium text-on-surface">
                    {expense.description}
                    {expense.notes && (
                      <p className="text-body-small text-on-surface-variant mt-1">{expense.notes}</p>
                    )}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {expense.category_id ? (
                      <span className="px-2 py-1 text-label-small font-medium bg-tertiary-container text-on-tertiary-container rounded-full shadow-elevation-1">
                        {expense.category_name}
                      </span>
                    ) : (
                      <select
                        className="text-label-small rounded border border-outline/50 px-2 py-1 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
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
                  <td className="px-6 py-4 whitespace-nowrap text-body-medium text-right font-medium text-on-surface">
                    {formatAmount(expense.amount)}
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

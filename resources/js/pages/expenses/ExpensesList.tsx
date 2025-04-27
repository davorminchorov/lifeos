import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';

interface Expense {
  id: string;
  title: string;
  amount: number;
  currency: string;
  date: string;
  category: {
    id: string;
    name: string;
    color: string;
  };
  payment_method: string;
}

interface Meta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

const ExpensesList: React.FC = () => {
  const [expenses, setExpenses] = useState<Expense[]>([]);
  const [meta, setMeta] = useState<Meta>({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState({
    category_id: '',
    date_from: '',
    date_to: '',
    search: '',
    sort_by: 'date',
    sort_order: 'desc',
  });

  const fetchExpenses = async (page = 1) => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        ...filters,
      });

      const response = await axios.get(`/api/expenses?${params}`);
      setExpenses(response.data.data || []);
      setMeta(response.data.meta || {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
      });
      setError(null);
    } catch (err) {
      setError('Failed to load expenses');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchExpenses();
  }, [filters]);

  const handlePageChange = (page: number) => {
    fetchExpenses(page);
  };

  const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement | HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const handleSortChange = (sortField: string) => {
    setFilters(prev => {
      if (prev.sort_by === sortField) {
        // Toggle sort order if clicking the same field
        return {
          ...prev,
          sort_order: prev.sort_order === 'asc' ? 'desc' : 'asc',
        };
      } else {
        // Default to descending for a new sort field
        return {
          ...prev,
          sort_by: sortField,
          sort_order: 'desc',
        };
      }
    });
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    fetchExpenses();
  };

  const formatPaymentMethod = (method: string) => {
    if (!method) return '-';
    return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  };

  const getSortIndicator = (field: string) => {
    if (filters.sort_by !== field) return null;

    return filters.sort_order === 'asc'
      ? '↑'
      : '↓';
  };

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex flex-col space-y-4 mb-8">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 sm:mb-0">Expenses</h1>
          </div>

          <Link to="/expenses/create">
            <Button>Add Expense</Button>
          </Link>
        </div>

        <p className="text-gray-600">Track and manage your expenses.</p>
      </div>

      <Card className="mb-6 border border-gray-200 shadow-sm">
        <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-t-lg border-b border-gray-200">
          <div>
            <label htmlFor="category_id" className="block text-sm font-medium text-gray-700 mb-1">
              Category
            </label>
            <select
              id="category_id"
              name="category_id"
              value={filters.category_id}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
            >
              <option value="">All Categories</option>
              {/* We'll populate categories dynamically */}
            </select>
          </div>

          <div>
            <label htmlFor="date_from" className="block text-sm font-medium text-gray-700 mb-1">
              From Date
            </label>
            <input
              type="date"
              id="date_from"
              name="date_from"
              value={filters.date_from}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
            />
          </div>

          <div>
            <label htmlFor="date_to" className="block text-sm font-medium text-gray-700 mb-1">
              To Date
            </label>
            <input
              type="date"
              id="date_to"
              name="date_to"
              value={filters.date_to}
              onChange={handleFilterChange}
              className="w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white"
            />
          </div>

          <div className="flex flex-col">
            <label htmlFor="search" className="block text-sm font-medium text-gray-700 mb-1">
              Search
            </label>
            <div className="flex">
              <input
                type="text"
                id="search"
                name="search"
                value={filters.search}
                onChange={handleFilterChange}
                placeholder="Search expenses..."
                className="w-full border border-gray-300 rounded-l-md shadow-sm p-2 bg-white"
              />
              <Button type="submit" className="rounded-l-none">
                Filter
              </Button>
            </div>
          </div>
        </form>

        {error && (
          <div className="bg-red-100 border-y border-red-400 text-red-700 px-4 py-3">
            {error}
          </div>
        )}

        {loading && expenses.length === 0 ? (
          <div className="p-8 flex flex-col items-center justify-center">
            <div className="animate-pulse space-y-4 w-full max-w-3xl">
              <div className="h-8 bg-gray-200 rounded w-1/3"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
              <div className="h-8 bg-gray-200 rounded w-full"></div>
            </div>
            <p className="text-gray-500 mt-4">Loading expenses...</p>
          </div>
        ) : expenses.length === 0 ? (
          <div className="flex flex-col items-center justify-center p-10">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p className="text-lg font-medium text-gray-600 mb-1">No expenses found</p>
            <p className="text-gray-500 text-center mb-4">Start tracking your expenses by adding your first record.</p>
            <Link to="/expenses/create">
              <Button size="sm">Add Your First Expense</Button>
            </Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSortChange('date')}
                  >
                    Date {getSortIndicator('date')}
                  </th>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSortChange('title')}
                  >
                    Title {getSortIndicator('title')}
                  </th>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSortChange('category')}
                  >
                    Category {getSortIndicator('category')}
                  </th>
                  <th
                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    onClick={() => handleSortChange('amount')}
                  >
                    Amount {getSortIndicator('amount')}
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Payment Method
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {expenses.map((expense) => (
                  <tr key={expense.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatDate(expense.date)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="font-medium text-gray-900">{expense.title}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span
                        className="px-2 py-1 text-xs font-medium rounded-full"
                        style={{
                          backgroundColor: `${expense.category.color}20`,
                          color: expense.category.color,
                        }}
                      >
                        {expense.category.name}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        {formatCurrency(expense.amount, expense.currency)}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatPaymentMethod(expense.payment_method)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex justify-end space-x-2">
                        <Link to={`/expenses/${expense.id}`} className="text-indigo-600 hover:text-indigo-900">
                          View
                        </Link>
                        <Link to={`/expenses/${expense.id}/edit`} className="text-indigo-600 hover:text-indigo-900">
                          Edit
                        </Link>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {meta.last_page > 1 && (
          <div className="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div className="flex-1 flex justify-between sm:hidden">
              <button
                onClick={() => handlePageChange(meta.current_page - 1)}
                disabled={meta.current_page === 1}
                className={`relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md ${
                  meta.current_page === 1
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    : 'bg-white text-gray-700 hover:bg-gray-50'
                }`}
              >
                Previous
              </button>
              <button
                onClick={() => handlePageChange(meta.current_page + 1)}
                disabled={meta.current_page === meta.last_page}
                className={`relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md ${
                  meta.current_page === meta.last_page
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    : 'bg-white text-gray-700 hover:bg-gray-50'
                }`}
              >
                Next
              </button>
            </div>
            <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p className="text-sm text-gray-700">
                  Showing <span className="font-medium">{(meta.current_page - 1) * meta.per_page + 1}</span> to{' '}
                  <span className="font-medium">
                    {Math.min(meta.current_page * meta.per_page, meta.total)}
                  </span>{' '}
                  of <span className="font-medium">{meta.total}</span> results
                </p>
              </div>
              <div>
                <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    onClick={() => handlePageChange(meta.current_page - 1)}
                    disabled={meta.current_page === 1}
                    className={`relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium ${
                      meta.current_page === 1
                        ? 'text-gray-300 cursor-not-allowed'
                        : 'text-gray-500 hover:bg-gray-50'
                    }`}
                  >
                    <span className="sr-only">Previous</span>
                    <svg className="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  </button>

                  {/* Page numbers would go here - simplified for brevity */}

                  <button
                    onClick={() => handlePageChange(meta.current_page + 1)}
                    disabled={meta.current_page === meta.last_page}
                    className={`relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium ${
                      meta.current_page === meta.last_page
                        ? 'text-gray-300 cursor-not-allowed'
                        : 'text-gray-500 hover:bg-gray-50'
                    }`}
                  >
                    <span className="sr-only">Next</span>
                    <svg className="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
};

export default ExpensesList;

import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { BudgetForm } from './BudgetForm';

interface Budget {
  budget_id: string;
  category_id: string | null;
  category_name?: string;
  budget_amount: number;
  current_spending: number;
  remaining: number;
  percentage_used: number;
  status: string;
  start_date: string;
  end_date: string;
  notes?: string | null;
}

interface Category {
  category_id: string;
  name: string;
}

interface BudgetsListProps {
  refreshTrigger?: number;
}

export const BudgetsList: React.FC<BudgetsListProps> = ({ refreshTrigger = 0 }) => {
  const [budgets, setBudgets] = useState<Budget[]>([]);
  const [categories, setCategories] = useState<Record<string, Category>>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [editingBudget, setEditingBudget] = useState<Budget | null>(null);
  const [showAddForm, setShowAddForm] = useState(false);

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
    fetchBudgets();
  }, [refreshTrigger, categories]);

  const fetchBudgets = async () => {
    setLoading(true);
    setError('');

    try {
      const response = await axios.get('/api/budgets');

      if (response.data && Array.isArray(response.data.data)) {
        // Add category names to budgets
        const budgetsWithCategories = response.data.data.map((budget: Budget) => ({
          ...budget,
          category_name: budget.category_id ? categories[budget.category_id]?.name : 'Overall Budget'
        }));

        setBudgets(budgetsWithCategories);
      } else {
        setBudgets([]);
      }
    } catch (err) {
      setError('Failed to load budgets');
      console.error('Failed to fetch budgets', err);
    } finally {
      setLoading(false);
    }
  };

  const handleFormSuccess = () => {
    setEditingBudget(null);
    setShowAddForm(false);
    fetchBudgets();
  };

  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  if (editingBudget) {
    return (
      <BudgetForm
        initialData={{
          budget_id: editingBudget.budget_id,
          category_id: editingBudget.category_id,
          amount: editingBudget.budget_amount,
          start_date: editingBudget.start_date,
          end_date: editingBudget.end_date,
          notes: editingBudget.notes,
        }}
        onSuccess={handleFormSuccess}
      />
    );
  }

  if (showAddForm) {
    return <BudgetForm onSuccess={handleFormSuccess} />;
  }

  if (loading && budgets.length === 0) {
    return <div className="flex justify-center py-8">Loading budgets...</div>;
  }

  if (error) {
    return <div className="bg-red-50 text-red-600 p-4 rounded">{error}</div>;
  }

  return (
    <div className="bg-white rounded-lg shadow">
      <div className="p-4 border-b flex justify-between items-center">
        <h2 className="text-xl font-semibold">Your Budgets</h2>
        <button
          onClick={() => setShowAddForm(true)}
          className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-300"
        >
          Add New Budget
        </button>
      </div>

      {budgets.length === 0 ? (
        <div className="p-8 text-center text-gray-500">
          No budgets found. Start by adding a new budget.
        </div>
      ) : (
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Category
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Period
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Budget
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Spent
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Remaining
                </th>
                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {budgets.map((budget) => (
                <tr key={budget.budget_id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {budget.category_name}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {formatDate(budget.start_date)} - {formatDate(budget.end_date)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    {formatAmount(budget.budget_amount)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    {formatAmount(budget.current_spending)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                    {formatAmount(budget.remaining)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-center">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      budget.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}>
                      {budget.status === 'active' ? 'Active' : 'Exceeded'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      onClick={() => setEditingBudget(budget)}
                      className="text-blue-600 hover:text-blue-900"
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

import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { BudgetForm } from './BudgetForm';
import { Button } from '../../ui';

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

  return (
    <div>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-title-medium font-medium text-on-surface">Your Budgets</h2>
        <Button
          onClick={() => setShowAddForm(true)}
          variant="filled"
          icon={
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
            </svg>
          }
        >
          Add New Budget
        </Button>
      </div>

      {loading ? (
        <div className="animate-pulse space-y-4">
          <div className="h-8 bg-surface-variant/40 rounded w-full"></div>
          <div className="h-8 bg-surface-variant/40 rounded w-full"></div>
          <div className="h-8 bg-surface-variant/40 rounded w-full"></div>
        </div>
      ) : error ? (
        <div className="p-8 text-center text-error bg-error-container/50 rounded-lg border border-error/30">
          {error}
          <button
            onClick={fetchBudgets}
            className="block mx-auto mt-2 text-body-small text-primary hover:text-primary/80"
          >
            Try Again
          </button>
        </div>
      ) : budgets.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-10 bg-surface-variant/20 rounded-lg border border-outline/20">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-on-surface-variant/30 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p className="text-title-medium font-medium text-on-surface mb-1">No budgets found</p>
          <p className="text-body-medium text-on-surface-variant text-center mb-4 max-w-md">
            Start by adding a new budget to track your spending limits.
          </p>
          <Button
            onClick={() => setShowAddForm(true)}
            variant="filled"
            icon={
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
              </svg>
            }
          >
            Add Your First Budget
          </Button>
        </div>
      ) : (
        <div className="bg-surface rounded-lg shadow-elevation-1 border border-outline/10 overflow-hidden">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-outline/20">
              <thead className="bg-surface-variant/20">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Category
                  </th>
                  <th scope="col" className="px-6 py-3 text-left text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Period
                  </th>
                  <th scope="col" className="px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Budget
                  </th>
                  <th scope="col" className="px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Spent
                  </th>
                  <th scope="col" className="px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Remaining
                  </th>
                  <th scope="col" className="px-6 py-3 text-center text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" className="px-6 py-3 text-right text-label-small font-medium text-on-surface-variant uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-surface divide-y divide-outline/20">
                {budgets.map((budget) => (
                  <tr key={budget.budget_id} className="hover:bg-surface-variant/10">
                    <td className="px-6 py-4 whitespace-nowrap text-body-medium font-medium text-on-surface">
                      {budget.category_name}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-body-medium text-on-surface-variant">
                      {formatDate(budget.start_date)} - {formatDate(budget.end_date)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-body-medium text-right text-on-surface">
                      {formatAmount(budget.budget_amount)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-body-medium text-right text-on-surface">
                      {formatAmount(budget.current_spending)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-body-medium text-right text-on-surface">
                      {formatAmount(budget.remaining)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-center">
                      <span className={`px-2 py-1 text-label-small leading-5 font-medium rounded-full ${
                        budget.status === 'active'
                          ? 'bg-tertiary-container text-on-tertiary-container'
                          : 'bg-error-container text-on-error-container'
                      }`}>
                        {budget.status === 'active' ? 'Active' : 'Exceeded'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-body-medium font-medium">
                      <Button
                        onClick={() => setEditingBudget(budget)}
                        variant="text"
                        size="sm"
                        icon={
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                          </svg>
                        }
                      >
                        Edit
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
};

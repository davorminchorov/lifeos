import React, { useState, useEffect } from 'react';
import axios from 'axios';

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
}

export const BudgetStatusCard: React.FC = () => {
  const [budgets, setBudgets] = useState<Budget[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchBudgets = async () => {
      setLoading(true);
      setError('');

      try {
        const response = await axios.get('/api/budgets');
        // Sort by budget with lowest percentage remaining first
        const sortedBudgets = [...response.data.data].sort((a, b) => {
          const aRemaining = (a.budget_amount - a.current_spending) / a.budget_amount;
          const bRemaining = (b.budget_amount - b.current_spending) / b.budget_amount;
          return aRemaining - bRemaining;
        }).slice(0, 3); // Only show top 3

        setBudgets(sortedBudgets);
      } catch (err) {
        setError('Failed to load budgets');
        console.error('Failed to fetch budgets', err);
      } finally {
        setLoading(false);
      }
    };

    fetchBudgets();
  }, []);

  const formatAmount = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  };

  if (loading) {
    return <div className="animate-pulse p-4">
      <div className="h-4 bg-gray-300 rounded w-3/4 mb-3"></div>
      <div className="h-4 bg-gray-300 rounded w-1/2 mb-3"></div>
      <div className="h-4 bg-gray-300 rounded w-2/3"></div>
    </div>;
  }

  if (error) {
    return <div className="text-red-600 p-4">{error}</div>;
  }

  if (budgets.length === 0) {
    return (
      <div className="text-gray-500 p-4">
        No budgets found. <a href="/budgets" className="text-blue-600 hover:underline">Create a budget</a>.
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {budgets.map((budget) => (
        <div key={budget.budget_id} className="border rounded-lg p-4">
          <div className="flex justify-between items-center mb-2">
            <h3 className="font-medium">{budget.category_name || 'Overall Budget'}</h3>
            <span
              className={`px-2 py-1 rounded-full text-xs ${
                budget.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              }`}
            >
              {budget.status === 'active' ? 'Active' : 'Exceeded'}
            </span>
          </div>

          <div className="flex justify-between text-sm text-gray-600 mb-2">
            <span>Ends {formatDate(budget.end_date)}</span>
            <span>{formatAmount(budget.current_spending)} / {formatAmount(budget.budget_amount)}</span>
          </div>

          <div className="w-full bg-gray-200 rounded-full h-2.5">
            <div
              className={`h-2.5 rounded-full ${
                budget.percentage_used > 90 ? 'bg-red-600' :
                budget.percentage_used > 70 ? 'bg-yellow-500' :
                'bg-green-600'
              }`}
              style={{width: `${Math.min(100, budget.percentage_used)}%`}}
            ></div>
          </div>

          <div className="mt-2 text-sm">
            <span className={budget.remaining < 0 ? 'text-red-600' : 'text-gray-700'}>
              {budget.remaining < 0 ? 'Over by ' : 'Remaining: '}
              {formatAmount(Math.abs(budget.remaining))}
            </span>
          </div>
        </div>
      ))}

      <div className="text-center mt-4">
        <a href="/budgets" className="text-blue-600 hover:underline text-sm">
          View all budgets
        </a>
      </div>
    </div>
  );
};

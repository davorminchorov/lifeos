import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { Button } from '../../ui';

interface Budget {
  budget_id: string;
  category_id: string | null;
  category_name: string;
  budget_amount: number;
  current_spending: number;
  remaining: number;
  percentage_used: number;
  status: string;
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

        // Add null check and ensure we have an array
        if (response.data && Array.isArray(response.data.data)) {
          // Sort by budget with lowest percentage remaining first
          const sortedBudgets = [...response.data.data].sort((a, b) => {
            const aRemaining = (a.budget_amount - a.current_spending) / a.budget_amount;
            const bRemaining = (b.budget_amount - b.current_spending) / b.budget_amount;
            return aRemaining - bRemaining;
          }).slice(0, 3); // Only show top 3

          setBudgets(sortedBudgets);
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
    return (
      <div className="animate-pulse space-y-4">
        <div className="h-20 bg-surface-variant/40 rounded w-full"></div>
        <div className="h-20 bg-surface-variant/40 rounded w-full"></div>
        <div className="h-20 bg-surface-variant/40 rounded w-full"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-error bg-error-container/60 p-4 rounded-lg border border-error/50 shadow-elevation-1">
        {error}
      </div>
    );
  }

  if (budgets.length === 0) {
    return (
      <div className="text-on-surface-variant p-4 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1">
        No budgets found. <Link to="/budgets" className="text-primary hover:text-primary/80">Create a budget</Link>.
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {budgets.map((budget) => (
        <div key={budget.budget_id} className="bg-surface-container rounded-lg p-4 border border-outline/40 shadow-elevation-1">
          <div className="flex justify-between items-center mb-2">
            <h3 className="font-medium text-title-medium text-on-surface">{budget.category_name || 'Overall Budget'}</h3>
            <span
              className={`px-2 py-1 rounded-full text-label-small font-medium shadow-elevation-1 ${
                budget.status === 'active'
                  ? 'bg-tertiary-container text-on-tertiary-container'
                  : 'bg-error-container text-on-error-container'
              }`}
            >
              {budget.status === 'active' ? 'Active' : 'Exceeded'}
            </span>
          </div>

          <div className="flex justify-between text-body-small text-on-surface-variant mb-2">
            <span>Ends {formatDate(budget.end_date)}</span>
            <span>{formatAmount(budget.current_spending)} / {formatAmount(budget.budget_amount)}</span>
          </div>

          <div className="w-full bg-surface-variant rounded-full h-2.5 shadow-elevation-1">
            <div
              className={`h-2.5 rounded-full ${
                budget.percentage_used > 90 ? 'bg-error' :
                budget.percentage_used > 70 ? 'bg-tertiary' :
                'bg-primary'
              }`}
              style={{width: `${Math.min(100, budget.percentage_used)}%`}}
            ></div>
          </div>

          <div className="mt-2 text-body-small">
            <span className={budget.remaining < 0 ? 'text-error' : 'text-on-surface'}>
              {budget.remaining < 0 ? 'Over by ' : 'Remaining: '}
              {formatAmount(Math.abs(budget.remaining))}
            </span>
          </div>
        </div>
      ))}

      <div className="text-center mt-6">
        <Link to="/budgets">
          <Button variant="text" size="sm">View all budgets</Button>
        </Link>
      </div>
    </div>
  );
};

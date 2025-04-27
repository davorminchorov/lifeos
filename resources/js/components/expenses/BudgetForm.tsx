import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';

interface Category {
  category_id: string;
  name: string;
}

interface BudgetFormProps {
  onSuccess: () => void;
  initialData?: {
    budget_id: string;
    category_id?: string | null;
    amount: number;
    start_date: string;
    end_date: string;
    notes?: string | null;
  };
}

export const BudgetForm: React.FC<BudgetFormProps> = ({ onSuccess, initialData }) => {
  const [categoryId, setCategoryId] = useState<string | null>(initialData?.category_id || null);
  const [amount, setAmount] = useState(initialData?.amount?.toString() || '');
  const [startDate, setStartDate] = useState(initialData?.start_date || '');
  const [endDate, setEndDate] = useState(initialData?.end_date || '');
  const [notes, setNotes] = useState(initialData?.notes || '');
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    // Set default dates if not editing
    if (!initialData) {
      const today = new Date();
      const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      const lastOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

      setStartDate(firstOfMonth.toISOString().split('T')[0]);
      setEndDate(lastOfMonth.toISOString().split('T')[0]);
    }

    // Fetch categories
    const fetchCategories = async () => {
      try {
        const response = await axios.get('/api/categories');
        if (response.data && Array.isArray(response.data.data)) {
          setCategories(response.data.data);
        } else {
          setCategories([]);
        }
      } catch (err) {
        console.error('Failed to fetch categories', err);
        setCategories([]);
      }
    };

    fetchCategories();
  }, [initialData]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const payload = {
        category_id: categoryId,
        amount: parseFloat(amount),
        start_date: startDate,
        end_date: endDate,
        notes: notes || null,
      };

      if (initialData?.budget_id) {
        // Update existing budget (this would require a backend endpoint)
        await axios.put(`/api/budgets/${initialData.budget_id}`, payload);
      } else {
        // Create new budget
        await axios.post('/api/budgets', payload);
      }

      onSuccess();
    } catch (err: any) {
      setError('Failed to save budget. ' + (err.response?.data?.message || ''));
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card className="p-6 shadow-elevation-2 border border-outline/40">
      <h2 className="text-headline-small font-medium text-on-surface mb-4">
        {initialData ? 'Edit Budget' : 'Create New Budget'}
      </h2>

      {error && (
        <div className="bg-error-container text-on-error-container p-3 rounded mb-4 border border-error/50 shadow-elevation-1">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit}>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Category
            </label>
            <select
              value={categoryId || ''}
              onChange={(e) => setCategoryId(e.target.value || null)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
            >
              <option value="">All Categories (Overall Budget)</option>
              {Array.isArray(categories) && categories.map((category) => (
                <option key={category.category_id} value={category.category_id}>
                  {category.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Budget Amount
            </label>
            <input
              type="number"
              min="0"
              step="0.01"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
              required
            />
          </div>

          <div>
            <label className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Start Date
            </label>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
              required
            />
          </div>

          <div>
            <label className="block text-body-medium font-medium text-on-surface-variant mb-1">
              End Date
            </label>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
              required
            />
          </div>

          <div className="col-span-2">
            <label className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Notes
            </label>
            <textarea
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              className="w-full rounded-md border border-outline/50 px-3 py-2 bg-surface text-on-surface shadow-elevation-1 focus:border-primary focus:ring-1 focus:ring-primary"
              rows={3}
            />
          </div>
        </div>

        <div className="mt-6 flex justify-end space-x-3">
          <Button
            type="button"
            onClick={onSuccess}
            variant="outlined"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            disabled={loading}
          >
            {loading ? 'Saving...' : 'Save Budget'}
          </Button>
        </div>
      </form>
    </Card>
  );
};

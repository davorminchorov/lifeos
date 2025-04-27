import React, { useState } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';

interface CategoryFormProps {
  onSuccess: () => void;
  initialData?: {
    category_id: string;
    name: string;
    description?: string | null;
    color?: string | null;
  };
}

export const CategoryForm: React.FC<CategoryFormProps> = ({ onSuccess, initialData }) => {
  const [name, setName] = useState(initialData?.name || '');
  const [description, setDescription] = useState(initialData?.description || '');
  const [color, setColor] = useState(initialData?.color || '#3b82f6');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const payload = {
        name,
        description: description || null,
        color,
      };

      if (initialData?.category_id) {
        // Update existing category
        await axios.put(`/api/categories/${initialData.category_id}`, payload);
      } else {
        // Create new category
        await axios.post('/api/categories', payload);
      }

      onSuccess();
    } catch (err: any) {
      setError('Failed to save category. ' + (err.response?.data?.message || ''));
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card className="shadow-elevation-2 border border-outline/40">
      <div className="px-6 py-4 border-b border-outline-variant/60">
        <h2 className="text-headline-small font-medium text-on-surface">
          {initialData ? 'Edit Category' : 'Create New Category'}
        </h2>
      </div>

      <form onSubmit={handleSubmit} className="p-6">
        {error && (
          <div className="mb-4 p-3 bg-error-container border border-error/50 text-on-error-container rounded shadow-elevation-1">
            {error}
          </div>
        )}

        <div className="space-y-6">
          <div>
            <label htmlFor="name" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Name <span className="text-error">*</span>
            </label>
            <input
              type="text"
              id="name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full rounded-md border border-outline/50 shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary"
              required
            />
          </div>

          <div>
            <label htmlFor="description" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Description
            </label>
            <textarea
              id="description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="w-full rounded-md border border-outline/50 shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary"
              rows={3}
              placeholder="What is this category used for?"
            />
          </div>

          <div>
            <label htmlFor="color" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Color
            </label>
            <div className="flex items-center space-x-4">
              <input
                type="color"
                id="colorPicker"
                value={color}
                onChange={(e) => setColor(e.target.value)}
                className="h-10 w-20 rounded border-0"
              />
              <input
                type="text"
                id="color"
                value={color}
                onChange={(e) => setColor(e.target.value)}
                className="w-32 rounded-md border border-outline/50 shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary"
                pattern="^#[0-9A-Fa-f]{6}$"
                title="Hex color code (e.g. #3b82f6)"
              />
              <div className="ml-4">
                <div
                  className="w-8 h-8 rounded-full border border-outline/50 shadow-elevation-1"
                  style={{ backgroundColor: color }}
                ></div>
              </div>
            </div>
            <p className="mt-1 text-body-small text-on-surface-variant">Choose a color to visually identify this category</p>
          </div>

          <div className="flex justify-end space-x-3 pt-4 border-t border-outline-variant/60">
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
              {loading && (
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              )}
              {loading ? 'Saving...' : 'Save Category'}
            </Button>
          </div>
        </div>
      </form>
    </Card>
  );
};

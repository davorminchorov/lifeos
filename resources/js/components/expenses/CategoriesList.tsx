import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { CategoryForm } from './CategoryForm';
import { Button } from '../../ui';

interface Category {
  category_id: string;
  name: string;
  description: string | null;
  color: string | null;
}

interface CategoriesListProps {
  refreshTrigger?: number;
}

export const CategoriesList: React.FC<CategoriesListProps> = ({ refreshTrigger = 0 }) => {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [editingCategory, setEditingCategory] = useState<Category | null>(null);
  const [showAddForm, setShowAddForm] = useState(false);

  useEffect(() => {
    fetchCategories();
  }, [refreshTrigger]);

  const fetchCategories = async () => {
    setLoading(true);
    setError('');

    try {
      const response = await axios.get('/api/categories');
      if (response.data && Array.isArray(response.data.data)) {
        setCategories(response.data.data);
      } else {
        setCategories([]);
      }
    } catch (err) {
      setError('Failed to load categories');
      console.error('Failed to fetch categories', err);
      setCategories([]);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (categoryId: string) => {
    if (!confirm('Are you sure you want to delete this category?')) {
      return;
    }

    try {
      await axios.delete(`/api/categories/${categoryId}`);
      setCategories(categories.filter(cat => cat.category_id !== categoryId));
    } catch (err: any) {
      const errorMessage = err.response?.data?.error || 'Failed to delete category';
      alert(errorMessage);
    }
  };

  const handleFormSuccess = () => {
    setEditingCategory(null);
    setShowAddForm(false);
    fetchCategories();
  };

  if (editingCategory) {
    return <CategoryForm initialData={editingCategory} onSuccess={handleFormSuccess} />;
  }

  if (showAddForm) {
    return <CategoryForm onSuccess={handleFormSuccess} />;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-title-medium font-medium text-on-surface">Expense Categories</h2>
        <Button
          onClick={() => setShowAddForm(true)}
          variant="filled"
          icon={
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
            </svg>
          }
        >
          Add New Category
        </Button>
      </div>

      {loading ? (
        <div className="animate-pulse space-y-4">
          <div className="h-20 bg-surface-variant/40 rounded w-full"></div>
          <div className="h-20 bg-surface-variant/40 rounded w-full"></div>
          <div className="h-20 bg-surface-variant/40 rounded w-full"></div>
        </div>
      ) : error ? (
        <div className="p-8 text-center text-error bg-error-container/60 rounded-lg border border-error/50 shadow-elevation-1">
          {error}
          <button
            onClick={fetchCategories}
            className="block mx-auto mt-2 text-body-small text-primary hover:text-primary/80"
          >
            Try Again
          </button>
        </div>
      ) : categories.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-10 bg-surface-container rounded-lg border border-outline/40 shadow-elevation-1">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-on-surface-variant/40 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          <p className="text-title-medium font-medium text-on-surface mb-1">No categories found</p>
          <p className="text-body-medium text-on-surface-variant text-center mb-4 max-w-md">
            Create your first category to get started with organizing your expenses.
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
            Add Your First Category
          </Button>
        </div>
      ) : (
        <div className="bg-surface rounded-lg shadow-elevation-1 border border-outline/40 overflow-hidden">
          <ul className="divide-y divide-outline/40">
            {categories.map((category) => (
              <li key={category.category_id} className="p-4 hover:bg-surface-variant/20">
                <div className="flex items-center justify-between">
                  <div className="flex items-center">
                    <div
                      className="w-10 h-10 rounded-full mr-3 flex-shrink-0 shadow-elevation-2 border border-outline/30"
                      style={{ backgroundColor: category.color || '#CCCCCC' }}
                    />
                    <div>
                      <h3 className="font-medium text-title-small text-on-surface">{category.name}</h3>
                      {category.description && (
                        <p className="text-body-small text-on-surface-variant mt-1">{category.description}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex space-x-3">
                    <Button
                      onClick={() => setEditingCategory(category)}
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
                    <Button
                      onClick={() => handleDelete(category.category_id)}
                      variant="text"
                      size="sm"
                      className="text-error"
                      icon={
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fillRule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                      }
                    >
                      Delete
                    </Button>
                  </div>
                </div>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
};

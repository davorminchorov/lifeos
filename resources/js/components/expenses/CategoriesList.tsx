import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { CategoryForm } from './CategoryForm';

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

  if (loading && categories.length === 0) {
    return <div className="flex justify-center py-8">Loading categories...</div>;
  }

  if (error) {
    return <div className="bg-red-50 text-red-600 p-4 rounded">{error}</div>;
  }

  if (editingCategory) {
    return <CategoryForm initialData={editingCategory} onSuccess={handleFormSuccess} />;
  }

  if (showAddForm) {
    return <CategoryForm onSuccess={handleFormSuccess} />;
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="p-4 border-b flex justify-between items-center">
        <h2 className="text-xl font-semibold">Expense Categories</h2>
        <button
          onClick={() => setShowAddForm(true)}
          className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-300 flex items-center"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          Add New Category
        </button>
      </div>

      {categories.length === 0 ? (
        <div className="flex flex-col items-center justify-center p-10 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          <p className="text-lg font-medium text-gray-600 mb-1">No categories found</p>
          <p className="text-gray-500 text-center mb-4">Create your first category to get started with organizing your expenses.</p>
          <button
            onClick={() => setShowAddForm(true)}
            className="mt-2 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-300 flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Add Your First Category
          </button>
        </div>
      ) : (
        <div className="overflow-hidden">
          <ul className="divide-y divide-gray-200">
            {categories.map((category) => (
              <li key={category.category_id} className="p-4 hover:bg-gray-50">
                <div className="flex items-center justify-between">
                  <div className="flex items-center">
                    <div
                      className="w-8 h-8 rounded-full mr-3 flex-shrink-0"
                      style={{ backgroundColor: category.color || '#CCCCCC' }}
                    />
                    <div>
                      <h3 className="font-medium text-gray-900">{category.name}</h3>
                      {category.description && (
                        <p className="text-sm text-gray-500 mt-1">{category.description}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex space-x-3">
                    <button
                      onClick={() => setEditingCategory(category)}
                      className="text-blue-600 hover:text-blue-800 flex items-center text-sm"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Edit
                    </button>
                    <button
                      onClick={() => handleDelete(category.category_id)}
                      className="text-red-600 hover:text-red-800 flex items-center text-sm"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Delete
                    </button>
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

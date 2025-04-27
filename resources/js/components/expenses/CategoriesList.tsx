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
      setCategories(response.data.data);
    } catch (err) {
      setError('Failed to load categories');
      console.error('Failed to fetch categories', err);
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
    <div className="bg-white rounded-lg shadow">
      <div className="p-4 border-b flex justify-between items-center">
        <h2 className="text-xl font-semibold">Expense Categories</h2>
        <button
          onClick={() => setShowAddForm(true)}
          className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition duration-300"
        >
          Add New Category
        </button>
      </div>

      {categories.length === 0 ? (
        <div className="p-8 text-center text-gray-500">
          No categories found. Create your first category to get started.
        </div>
      ) : (
        <div className="overflow-hidden">
          <ul className="divide-y divide-gray-200">
            {categories.map((category) => (
              <li key={category.category_id} className="p-4 hover:bg-gray-50">
                <div className="flex items-center justify-between">
                  <div className="flex items-center">
                    <div
                      className="w-6 h-6 rounded-full mr-3"
                      style={{ backgroundColor: category.color || '#CCCCCC' }}
                    />
                    <div>
                      <h3 className="font-medium">{category.name}</h3>
                      {category.description && (
                        <p className="text-sm text-gray-500">{category.description}</p>
                      )}
                    </div>
                  </div>
                  <div className="flex space-x-2">
                    <button
                      onClick={() => setEditingCategory(category)}
                      className="text-blue-600 hover:text-blue-800"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => handleDelete(category.category_id)}
                      className="text-red-600 hover:text-red-800"
                    >
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

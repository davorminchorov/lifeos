import React, { useState } from 'react';
import { CategoriesList } from '../components/expenses/CategoriesList';

export const CategoriesPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);

  return (
    <div className="container mx-auto p-4">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Expense Categories</h1>
        <p className="text-gray-600">Manage the categories used to organize your expenses</p>
      </div>

      <CategoriesList refreshTrigger={refreshTrigger} />
    </div>
  );
};

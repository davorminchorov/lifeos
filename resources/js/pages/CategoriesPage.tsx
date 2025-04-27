import React, { useState } from 'react';
import { CategoriesList } from '../components/expenses/CategoriesList';
import { Link } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '../ui';

export const CategoriesPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex flex-col space-y-4 mb-8">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 sm:mb-0 text-on-surface">Expense Categories</h1>
            <div className="flex space-x-4 mt-2">
              <Link to="/expenses" className="text-primary hover:text-primary/80 font-medium">
                View Expenses
              </Link>
              <Link to="/budgets" className="text-primary hover:text-primary/80 font-medium">
                Manage Budgets
              </Link>
            </div>
          </div>
        </div>

        <p className="text-on-surface-variant">Manage the categories used to organize your expenses</p>
      </div>

      <Card variant="elevated">
        <CardHeader>
          <CardTitle>Categories</CardTitle>
        </CardHeader>
        <CardContent>
          <CategoriesList refreshTrigger={refreshTrigger} />
        </CardContent>
      </Card>
    </div>
  );
};

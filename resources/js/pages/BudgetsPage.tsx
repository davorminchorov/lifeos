import React, { useState } from 'react';
import { BudgetsList } from '../components/expenses/BudgetsList';

export const BudgetsPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);

  return (
    <div className="container mx-auto p-4">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Budget Management</h1>
        <p className="text-gray-600">Set and track spending limits for your expenses</p>
      </div>

      <BudgetsList refreshTrigger={refreshTrigger} />
    </div>
  );
};

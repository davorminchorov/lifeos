import React, { useState } from 'react';
import { BudgetsList } from '../components/expenses/BudgetsList';
import { Card, CardContent, CardHeader, CardTitle } from '../ui';

export const BudgetsPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);

  return (
    <div className="container mx-auto p-4 max-w-6xl">
      <div className="mb-6">
        <h1 className="text-3xl font-bold text-on-surface mb-2">Budget Management</h1>
        <p className="text-on-surface-variant">Set and track spending limits for your expenses</p>
      </div>

      <Card variant="elevated">
        <CardHeader>
          <CardTitle>Budgets</CardTitle>
        </CardHeader>
        <CardContent>
          <BudgetsList refreshTrigger={refreshTrigger} />
        </CardContent>
      </Card>
    </div>
  );
};

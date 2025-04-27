import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { BudgetsList } from '../components/expenses/BudgetsList';
import { Button } from '../ui';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/Card';
import { PageContainer } from '../ui/PageContainer';

export const BudgetsPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);

  return (
    <PageContainer
      title="Budget Management"
      subtitle="Set and track spending limits for your expenses"
      actions={
        <Link to="/expenses">
          <Button variant="outlined"
            icon={<svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clipRule="evenodd" />
            </svg>}
          >
            Back to Expenses
          </Button>
        </Link>
      }
    >
      <div className="flex space-x-4 mb-6">
        <Link to="/expenses">
          <Button variant="text">View Expenses</Button>
        </Link>
        <Link to="/categories">
          <Button variant="text">Manage Categories</Button>
        </Link>
      </div>

      <Card variant="elevated">
        <CardHeader>
          <CardTitle>Budgets</CardTitle>
        </CardHeader>
        <CardContent>
          <BudgetsList refreshTrigger={refreshTrigger} />
        </CardContent>
      </Card>
    </PageContainer>
  );
};

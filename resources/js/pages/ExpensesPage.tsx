import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import ExpenseForm from '../components/expenses/ExpenseForm';
import { ExpensesList } from '../components/expenses/ExpensesList';
import { MonthlySummaryCard } from '../components/expenses/MonthlySummaryCard';
import { BudgetStatusCard } from '../components/expenses/BudgetStatusCard';
import { Button } from '../ui';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/Card';
import { PageContainer, PageSection } from '../ui/PageContainer';

export const ExpensesPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);
  const [showAddForm, setShowAddForm] = useState(false);

  const handleExpenseAdded = () => {
    setRefreshTrigger(prev => prev + 1);
    setShowAddForm(false);
  };

  return (
    <PageContainer
      title="Expense Tracking"
      subtitle="Track, categorize and analyze your expenses to manage your finances better."
      actions={
        <Button
          onClick={() => setShowAddForm(!showAddForm)}
          variant="filled"
          icon={
            showAddForm
              ? <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
              : <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clipRule="evenodd" />
                </svg>
          }
        >
          {showAddForm ? 'Cancel' : 'Add New Expense'}
        </Button>
      }
    >
      <div className="flex space-x-4 mb-6">
        <Link to="/budgets">
          <Button variant="text">Manage Budgets</Button>
        </Link>
        <Link to="/categories">
          <Button variant="text">Manage Categories</Button>
        </Link>
      </div>

      {showAddForm && (
        <PageSection title="Add New Expense">
          <Card variant="elevated">
            <CardContent>
              <ExpenseForm onSuccess={handleExpenseAdded} />
            </CardContent>
          </Card>
        </PageSection>
      )}

      <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div className="md:col-span-8">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Expenses List</CardTitle>
            </CardHeader>
            <CardContent>
              <ExpensesList refreshTrigger={refreshTrigger} />
            </CardContent>
          </Card>
        </div>

        <div className="md:col-span-4 space-y-6">
          <Card variant="filled">
            <CardHeader>
              <CardTitle>Monthly Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <MonthlySummaryCard />
            </CardContent>
          </Card>

          <Card variant="outlined">
            <CardHeader>
              <CardTitle>Budget Status</CardTitle>
            </CardHeader>
            <CardContent>
              <BudgetStatusCard />
            </CardContent>
          </Card>
        </div>
      </div>

      {!showAddForm && (
        <div className="mt-8 text-center">
          <p className="text-on-surface-variant mb-2">Want to see more detailed analytics?</p>
          <p className="text-on-surface mb-4">Visit the reports section for comprehensive spending analysis.</p>
          <Link to="/reports">
            <Button variant="outlined">View Reports</Button>
          </Link>
        </div>
      )}
    </PageContainer>
  );
};

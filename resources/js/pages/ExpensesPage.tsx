import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import ExpenseForm from '../components/expenses/ExpenseForm';
import { ExpensesList } from '../components/expenses/ExpensesList';
import { MonthlySummaryCard } from '../components/expenses/MonthlySummaryCard';
import { BudgetStatusCard } from '../components/expenses/BudgetStatusCard';
import { Button, Card, CardContent, CardHeader, CardTitle } from '../ui';

export const ExpensesPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);
  const [showAddForm, setShowAddForm] = useState(false);

  const handleExpenseAdded = () => {
    setRefreshTrigger(prev => prev + 1);
    setShowAddForm(false);
  };

  return (
    <div className="container mx-auto px-4 py-8 max-w-6xl">
      <div className="flex flex-col space-y-4 mb-8">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2 sm:mb-0 text-on-surface">Expense Tracking</h1>
            <div className="flex space-x-4 mt-2">
              <Link to="/budgets" className="text-primary hover:text-primary/80 font-medium">
                Manage Budgets
              </Link>
              <Link to="/categories" className="text-primary hover:text-primary/80 font-medium">
                Manage Categories
              </Link>
            </div>
          </div>

          <Button
            onClick={() => setShowAddForm(!showAddForm)}
            variant="filled"
            className="whitespace-nowrap"
          >
            {showAddForm ? 'Cancel' : 'Add New Expense'}
          </Button>
        </div>

        <p className="text-on-surface-variant">Track, categorize and analyze your expenses to manage your finances better.</p>
      </div>

      {showAddForm && (
        <Card variant="elevated" className="mb-6">
          <CardContent>
            <ExpenseForm onSuccess={handleExpenseAdded} />
          </CardContent>
        </Card>
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
    </div>
  );
};

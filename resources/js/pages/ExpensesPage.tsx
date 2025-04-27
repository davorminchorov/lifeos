import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import ExpenseForm from '../components/expenses/ExpenseForm';
import { ExpensesList } from '../components/expenses/ExpensesList';
import { MonthlySummaryCard } from '../components/expenses/MonthlySummaryCard';
import { BudgetStatusCard } from '../components/expenses/BudgetStatusCard';
import { Button } from '../ui/Button/Button';
import { Card } from '../ui/Card';

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
            <h1 className="text-3xl font-bold mb-2 sm:mb-0">Expense Tracking</h1>
            <div className="flex space-x-4 mt-2">
              <Link to="/budgets" className="text-blue-600 hover:text-blue-800">
                Manage Budgets
              </Link>
              <Link to="/categories" className="text-blue-600 hover:text-blue-800">
                Manage Categories
              </Link>
            </div>
          </div>

          <Button
            onClick={() => setShowAddForm(!showAddForm)}
            className="whitespace-nowrap"
          >
            {showAddForm ? 'Cancel' : 'Add New Expense'}
          </Button>
        </div>

        <p className="text-gray-600">Track, categorize and analyze your expenses to manage your finances better.</p>
      </div>

      {showAddForm && (
        <Card className="mb-6 border border-gray-200 shadow-sm">
          <div className="p-6">
            <ExpenseForm onSuccess={handleExpenseAdded} />
          </div>
        </Card>
      )}

      <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
        <div className="md:col-span-8">
          <Card className="border border-gray-200 shadow-sm">
            <div className="p-6">
              <ExpensesList refreshTrigger={refreshTrigger} />
            </div>
          </Card>
        </div>

        <div className="md:col-span-4 space-y-6">
          <Card className="border border-gray-200 shadow-sm">
            <div className="p-6">
              <h2 className="text-xl font-semibold mb-4">Monthly Summary</h2>
              <MonthlySummaryCard />
            </div>
          </Card>

          <Card className="border border-gray-200 shadow-sm">
            <div className="p-6">
              <h2 className="text-xl font-semibold mb-4">Budget Status</h2>
              <BudgetStatusCard />
            </div>
          </Card>
        </div>
      </div>

      {!showAddForm && (
        <div className="mt-8 text-center">
          <p className="text-gray-500 mb-2">Want to see more detailed analytics?</p>
          <p className="text-gray-700 mb-4">Visit the reports section for comprehensive spending analysis.</p>
          <Link to="/reports">
            <Button variant="outlined">View Reports</Button>
          </Link>
        </div>
      )}
    </div>
  );
};

import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { ExpenseForm } from '../components/expenses/ExpenseForm';
import { ExpensesList } from '../components/expenses/ExpensesList';
import { MonthlySummaryCard } from '../components/expenses/MonthlySummaryCard';
import { BudgetStatusCard } from '../components/expenses/BudgetStatusCard';

export const ExpensesPage: React.FC = () => {
  const [refreshTrigger, setRefreshTrigger] = useState(0);
  const [showAddForm, setShowAddForm] = useState(false);

  const handleExpenseAdded = () => {
    setRefreshTrigger(prev => prev + 1);
    setShowAddForm(false);
  };

  return (
    <div className="container mx-auto p-4">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Expense Tracking</h1>
          <div className="mt-2 flex space-x-4">
            <Link to="/budgets" className="text-blue-600 hover:text-blue-800">
              Manage Budgets
            </Link>
            <Link to="/categories" className="text-blue-600 hover:text-blue-800">
              Manage Categories
            </Link>
          </div>
        </div>
        <button
          className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md"
          onClick={() => setShowAddForm(!showAddForm)}
        >
          {showAddForm ? 'Cancel' : 'Add New Expense'}
        </button>
      </div>

      {showAddForm && (
        <div className="mb-6">
          <ExpenseForm onSuccess={handleExpenseAdded} />
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div className="lg:col-span-8">
          <ExpensesList refreshTrigger={refreshTrigger} />
        </div>

        <div className="lg:col-span-4">
          <div className="bg-white rounded-lg shadow p-4 mb-6">
            <h2 className="text-xl font-semibold mb-4">Monthly Summary</h2>
            <MonthlySummaryCard />
          </div>

          <div className="bg-white rounded-lg shadow p-4">
            <h2 className="text-xl font-semibold mb-4">Budget Status</h2>
            <BudgetStatusCard />
          </div>
        </div>
      </div>
    </div>
  );
};

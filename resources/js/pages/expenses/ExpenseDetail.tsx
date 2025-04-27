import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { useToast } from '../../ui/Toast';

interface Category {
  id: string;
  name: string;
  color: string;
}

interface Expense {
  id: string;
  title: string;
  amount: number;
  currency: string;
  date: string;
  category: Category | null;
  description: string;
  payment_method: string;
  receipt_url: string | null;
  created_at: string;
  updated_at: string;
}

const ExpenseDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [expense, setExpense] = useState<Expense | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [deleting, setDeleting] = useState(false);

  useEffect(() => {
    const fetchExpense = async () => {
      setLoading(true);
      try {
        const response = await axios.get(`/api/expenses/${id}`);
        setExpense(response.data.data);
        setError(null);
      } catch (err) {
        setError('Failed to load expense details');
        console.error(err);
        toast({
          title: "Error",
          description: "Failed to load expense details",
          variant: "destructive",
        });
      } finally {
        setLoading(false);
      }
    };

    fetchExpense();
  }, [id]);

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this expense?')) {
      return;
    }

    setDeleting(true);
    try {
      await axios.delete(`/api/expenses/${id}`);
      toast({
        title: "Success",
        description: "Expense deleted successfully",
      });
      navigate('/expenses');
    } catch (err) {
      toast({
        title: "Error",
        description: "Failed to delete expense",
        variant: "destructive",
      });
      console.error(err);
      setDeleting(false);
    }
  };

  const formatPaymentMethod = (method: string) => {
    if (!method) return '-';
    return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  };

  const getCategoryStyle = (category: Category | null) => {
    if (!category) {
      return {
        backgroundColor: '#e5e7eb',
        color: '#374151'
      };
    }

    return {
      backgroundColor: category.color || '#e5e7eb',
      color: getContrastColor(category.color || '#e5e7eb')
    };
  };

  // Helper function to determine text color based on background color
  const getContrastColor = (hexColor: string) => {
    // Convert hex to RGB
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);

    // Calculate luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    // Return black or white based on luminance
    return luminance > 0.5 ? '#000000' : '#ffffff';
  };

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="flex justify-between items-center mb-6">
          <h1 className="text-3xl font-bold animate-pulse bg-gray-200 h-10 w-64 rounded"></h1>
        </div>
        <Card className="border border-gray-200 shadow-sm">
          <div className="animate-pulse p-6 space-y-4">
            <div className="h-8 bg-gray-200 rounded w-1/4 mb-4"></div>
            <div className="h-8 bg-gray-200 rounded w-1/2"></div>
            <div className="h-8 bg-gray-200 rounded w-1/3"></div>
            <div className="h-24 bg-gray-200 rounded w-full"></div>
            <div className="h-8 bg-gray-200 rounded w-1/4"></div>
            <div className="h-8 bg-gray-200 rounded w-1/2"></div>
          </div>
        </Card>
      </div>
    );
  }

  if (error || !expense) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <Card className="border border-gray-200 shadow-sm p-6">
          <div className="text-center py-10">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p className="text-xl font-medium text-gray-900 mb-2">Failed to load expense</p>
            <p className="text-gray-600 mb-6">{error || 'The expense could not be found'}</p>
            <div className="flex justify-center space-x-4">
              <Button onClick={() => navigate('/expenses')} variant="outlined">
                Back to Expenses
              </Button>
              <Button onClick={() => window.location.reload()}>
                Try Again
              </Button>
            </div>
          </div>
        </Card>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-4xl">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
          <Link to="/expenses" className="text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Back to expenses
          </Link>
          <h1 className="text-3xl font-bold">{expense.title}</h1>
        </div>

        <div className="flex space-x-3">
          <Link to={`/expenses/${id}/edit`}>
            <Button variant="outlined">
              Edit Expense
            </Button>
          </Link>
          <Button
            onClick={handleDelete}
            disabled={deleting}
          >
            {deleting ? 'Deleting...' : 'Delete Expense'}
          </Button>
        </div>
      </div>

      <Card className="border border-gray-200 shadow-sm mb-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
          <div>
            <h2 className="font-medium text-gray-500 text-sm mb-1">Amount</h2>
            <p className="text-2xl font-bold text-gray-900">{formatCurrency(expense.amount, expense.currency)}</p>
          </div>

          <div>
            <h2 className="font-medium text-gray-500 text-sm mb-1">Date</h2>
            <p className="text-gray-900">{formatDate(expense.date)}</p>
          </div>

          <div>
            <h2 className="font-medium text-gray-500 text-sm mb-1">Category</h2>
            {expense.category ? (
              <span
                className="inline-flex px-2.5 py-1 text-sm font-medium rounded-full"
                style={getCategoryStyle(expense.category)}
              >
                {expense.category.name}
              </span>
            ) : (
              <span className="inline-flex px-2.5 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                Uncategorized
              </span>
            )}
          </div>

          <div>
            <h2 className="font-medium text-gray-500 text-sm mb-1">Payment Method</h2>
            <p className="text-gray-900">{formatPaymentMethod(expense.payment_method)}</p>
          </div>

          {expense.description && (
            <div className="col-span-1 md:col-span-2">
              <h2 className="font-medium text-gray-500 text-sm mb-1">Description</h2>
              <p className="text-gray-900 whitespace-pre-line">{expense.description}</p>
            </div>
          )}

          {expense.receipt_url && (
            <div className="col-span-1 md:col-span-2">
              <h2 className="font-medium text-gray-500 text-sm mb-1">Receipt</h2>
              <a
                href={expense.receipt_url}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center text-indigo-600 hover:text-indigo-800"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                View Receipt
              </a>
            </div>
          )}
        </div>
      </Card>

      <Card className="border border-gray-200 shadow-sm">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
          <div>
            <h2 className="font-medium text-gray-500 text-sm mb-1">Created At</h2>
            <p className="text-gray-900">{formatDate(expense.created_at)}</p>
          </div>

          <div>
            <h2 className="font-medium text-gray-500 text-sm mb-1">Last Updated</h2>
            <p className="text-gray-900">{formatDate(expense.updated_at)}</p>
          </div>
        </div>
      </Card>
    </div>
  );
};

export default ExpenseDetail;

import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import toast from 'react-hot-toast';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { FileList } from '../../components/common/FileList';
import { FileUpload } from '../../components/common/FileUpload';

interface Expense {
  id: string;
  title: string;
  amount: number;
  currency: string;
  date: string;
  category: {
    id: string;
    name: string;
    color: string;
  };
  description: string;
  payment_method: string;
  receipt_url: string;
  created_at: string;
  updated_at: string;
}

const ExpenseDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [expense, setExpense] = useState<Expense | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [deleting, setDeleting] = useState(false);

  useEffect(() => {
    const fetchExpense = async () => {
      setLoading(true);
      try {
        const response = await axios.get(`/api/expenses/${id}`);
        setExpense(response.data);
        setError(null);
      } catch (err) {
        setError('Failed to load expense details');
        console.error(err);
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
      toast.success('Expense deleted successfully');
      navigate('/expenses');
    } catch (err) {
      toast.error('Failed to delete expense');
      console.error(err);
      setDeleting(false);
    }
  };

  const formatPaymentMethod = (method: string) => {
    if (!method) return '-';
    return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
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
            variant="contained"
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
            <span
              className="inline-flex px-2.5 py-1 text-sm font-medium rounded-full"
              style={{
                backgroundColor: `${expense.category.color}20`,
                color: expense.category.color,
              }}
            >
              {expense.category.name}
            </span>
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

          {/* File Attachments Section */}
          <Card className="border border-gray-200 shadow-sm mt-6">
            <div className="p-6">
              <h2 className="text-lg font-medium text-gray-900 mb-4">Attachments</h2>

              <div className="space-y-4">
                <FileList
                  entityId={id || ''}
                  entityType="expense"
                  showEmpty={false}
                />

                <FileUpload
                  entityId={id || ''}
                  entityType="expense"
                  buttonText="Attach New File"
                  allowedTypes={['image/jpeg', 'image/png', 'application/pdf']}
                  maxSize={5}
                  onUploadSuccess={() => {
                    // Refresh the page or update the file list
                    window.location.reload();
                  }}
                />
              </div>
            </div>
          </Card>
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

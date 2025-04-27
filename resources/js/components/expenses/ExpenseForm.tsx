import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { FileUpload, FileData } from '../common/FileUpload';
import { FileList } from '../common/FileList';
import { useToast } from '../../ui/Toast';

interface ExpenseFormProps {
  initialData?: {
    id?: string;
    title: string;
    amount: number;
    currency: string;
    date: string;
    category_id: string;
    description: string;
    payment_method: string;
    receipt_url?: string;
    notes?: string;
  };
  isEditing?: boolean;
  categories?: { id: string; name: string }[];
  onSuccess?: () => void;
}

const ExpenseForm: React.FC<ExpenseFormProps> = ({
  initialData,
  isEditing = false,
  categories = [],
  onSuccess
}) => {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    title: initialData?.title || '',
    amount: initialData?.amount || 0,
    currency: initialData?.currency || 'USD',
    date: initialData?.date || new Date().toISOString().split('T')[0],
    category_id: initialData?.category_id || '',
    description: initialData?.description || '',
    payment_method: initialData?.payment_method || '',
    receipt_url: initialData?.receipt_url || '',
    notes: initialData?.notes || '',
  });

  const [availableCategories, setAvailableCategories] = useState(categories);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [isLoadingCategories, setIsLoadingCategories] = useState(categories.length === 0);

  useEffect(() => {
    // Fetch categories if not provided
    if (categories.length === 0) {
      fetchCategories();
    }
  }, [categories]);

  const fetchCategories = async () => {
    try {
      setIsLoadingCategories(true);
      const response = await axios.get('/api/categories');
      setAvailableCategories(Array.isArray(response.data) ? response.data :
                             (response.data?.data ? response.data.data : []));
    } catch (error) {
      console.error('Failed to fetch categories:', error);
      setSubmitError('Failed to load expense categories. Please try again.');
      toast({
        title: "Error",
        description: "Failed to load expense categories",
        variant: "destructive",
      });
    } finally {
      setIsLoadingCategories(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;

    // Handle numeric input for the amount field
    if (name === 'amount') {
      setFormData((prev) => ({
        ...prev,
        [name]: value === '' ? 0 : parseFloat(value)
      }));
    } else {
      setFormData((prev) => ({ ...prev, [name]: value }));
    }

    // Clear error for this field when user updates it
    if (errors[name]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  const validate = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.title.trim()) {
      newErrors.title = 'Title is required';
    }

    if (formData.amount <= 0) {
      newErrors.amount = 'Amount must be greater than 0';
    }

    if (!formData.date) {
      newErrors.date = 'Date is required';
    }

    if (!formData.currency) {
      newErrors.currency = 'Currency is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!validate()) {
      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);

    try {
      if (isEditing && initialData?.id) {
        // Update existing expense
        await axios.put(`/api/expenses/${initialData.id}`, formData);
        toast({
          title: "Success",
          description: "Expense updated successfully",
          variant: "success",
        });
        navigate(`/expenses/${initialData.id}`);
      } else {
        // Create new expense
        const response = await axios.post('/api/expenses', formData);
        toast({
          title: "Success",
          description: "Expense created successfully",
          variant: "success",
        });
        navigate(`/expenses/${response.data.id}`);
      }
      if (onSuccess) {
        onSuccess();
      }
    } catch (error: any) {
      console.error('Submission error:', error);
      if (error.response?.data?.errors) {
        // Handle validation errors from the server
        const serverErrors = error.response.data.errors;
        const formattedErrors: Record<string, string> = {};

        Object.entries(serverErrors).forEach(([key, messages]: [string, any]) => {
          formattedErrors[key] = Array.isArray(messages) ? messages[0] : messages;
        });

        setErrors(formattedErrors);
        toast({
          title: "Validation Error",
          description: "Please correct the errors in the form",
          variant: "destructive",
        });
      } else {
        setSubmitError(error.response?.data?.error || 'An unexpected error occurred. Please try again.');
        toast({
          title: "Error",
          description: error.response?.data?.error || 'An unexpected error occurred. Please try again.',
          variant: "destructive",
        });
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const currencyOptions = [
    { value: 'USD', label: 'USD - US Dollar' },
    { value: 'EUR', label: 'EUR - Euro' },
    { value: 'GBP', label: 'GBP - British Pound' },
    { value: 'CAD', label: 'CAD - Canadian Dollar' },
    { value: 'AUD', label: 'AUD - Australian Dollar' },
    { value: 'JPY', label: 'JPY - Japanese Yen' },
  ];

  const paymentMethodOptions = [
    { value: 'credit_card', label: 'Credit Card' },
    { value: 'debit_card', label: 'Debit Card' },
    { value: 'cash', label: 'Cash' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'mobile_payment', label: 'Mobile Payment' },
    { value: 'other', label: 'Other' },
  ];

  return (
    <Card className="max-w-2xl mx-auto shadow-elevation-2 border border-outline/40">
      <div className="px-6 py-4 border-b border-outline-variant/60">
        <h2 className="text-headline-small font-medium text-on-surface">
          {isEditing ? 'Edit Expense' : 'Add New Expense'}
        </h2>
      </div>

      <form onSubmit={handleSubmit} className="p-6">
        {submitError && (
          <div className="mb-4 p-3 bg-error-container border border-error/50 text-on-error-container rounded shadow-elevation-1">
            {submitError}
          </div>
        )}

        <div className="space-y-6">
          {/* Title */}
          <div>
            <label htmlFor="title" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Title <span className="text-error">*</span>
            </label>
            <input
              type="text"
              id="title"
              name="title"
              value={formData.title}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.title ? 'border-error' : 'border-outline/50'
              } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
              placeholder="e.g. Groceries, Restaurant, Taxi"
            />
            {errors.title && (
              <p className="mt-1 text-body-small text-error">{errors.title}</p>
            )}
          </div>

          {/* Amount and Currency (side by side) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="amount" className="block text-body-medium font-medium text-on-surface-variant mb-1">
                Amount <span className="text-error">*</span>
              </label>
              <input
                type="number"
                id="amount"
                name="amount"
                value={formData.amount}
                onChange={handleChange}
                min="0.01"
                step="0.01"
                className={`w-full rounded-md border ${
                  errors.amount ? 'border-error' : 'border-outline/50'
                } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
                placeholder="0.00"
              />
              {errors.amount && (
                <p className="mt-1 text-body-small text-error">{errors.amount}</p>
              )}
            </div>

            <div>
              <label htmlFor="currency" className="block text-body-medium font-medium text-on-surface-variant mb-1">
                Currency <span className="text-error">*</span>
              </label>
              <select
                id="currency"
                name="currency"
                value={formData.currency}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.currency ? 'border-error' : 'border-outline/50'
                } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
              >
                {currencyOptions.map(option => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
              {errors.currency && (
                <p className="mt-1 text-body-small text-error">{errors.currency}</p>
              )}
            </div>
          </div>

          {/* Date and Category (side by side) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="date" className="block text-body-medium font-medium text-on-surface-variant mb-1">
                Date <span className="text-error">*</span>
              </label>
              <input
                type="date"
                id="date"
                name="date"
                value={formData.date}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.date ? 'border-error' : 'border-outline/50'
                } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
              />
              {errors.date && (
                <p className="mt-1 text-body-small text-error">{errors.date}</p>
              )}
            </div>

            <div>
              <label htmlFor="category_id" className="block text-body-medium font-medium text-on-surface-variant mb-1">
                Category
              </label>
              <select
                id="category_id"
                name="category_id"
                value={formData.category_id}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.category_id ? 'border-error' : 'border-outline/50'
                } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
                disabled={isLoadingCategories}
              >
                <option value="">Select a category</option>
                {isLoadingCategories ? (
                  <option disabled>Loading categories...</option>
                ) : (
                  availableCategories.map(category => (
                    <option key={category.id} value={category.id}>
                      {category.name}
                    </option>
                  ))
                )}
              </select>
              {errors.category_id && (
                <p className="mt-1 text-body-small text-error">{errors.category_id}</p>
              )}
            </div>
          </div>

          {/* Payment Method */}
          <div>
            <label htmlFor="payment_method" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Payment Method
            </label>
            <select
              id="payment_method"
              name="payment_method"
              value={formData.payment_method}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.payment_method ? 'border-error' : 'border-outline/50'
              } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
            >
              <option value="">Select payment method</option>
              {paymentMethodOptions.map(option => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
            {errors.payment_method && (
              <p className="mt-1 text-body-small text-error">{errors.payment_method}</p>
            )}
          </div>

          {/* Description */}
          <div>
            <label htmlFor="description" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Description
            </label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              rows={3}
              className={`w-full rounded-md border ${
                errors.description ? 'border-error' : 'border-outline/50'
              } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
              placeholder="Detailed description of the expense"
            />
            {errors.description && (
              <p className="mt-1 text-body-small text-error">{errors.description}</p>
            )}
          </div>

          {/* Notes */}
          <div>
            <label htmlFor="notes" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Notes
            </label>
            <textarea
              id="notes"
              name="notes"
              value={formData.notes}
              onChange={handleChange}
              rows={2}
              className={`w-full rounded-md border ${
                errors.notes ? 'border-error' : 'border-outline/50'
              } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
              placeholder="Any additional notes"
            />
            {errors.notes && (
              <p className="mt-1 text-body-small text-error">{errors.notes}</p>
            )}
          </div>

          {/* Receipt URL */}
          <div>
            <label htmlFor="receipt_url" className="block text-body-medium font-medium text-on-surface-variant mb-1">
              Receipt URL
            </label>
            <input
              type="url"
              id="receipt_url"
              name="receipt_url"
              value={formData.receipt_url}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.receipt_url ? 'border-error' : 'border-outline/50'
              } shadow-elevation-1 p-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary`}
              placeholder="https://example.com/receipt.pdf"
            />
            {errors.receipt_url && (
              <p className="mt-1 text-body-small text-error">{errors.receipt_url}</p>
            )}
            <p className="mt-1 text-body-small text-on-surface-variant">
              Link to an online receipt if available
            </p>
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <Button
              type="button"
              onClick={() => navigate('/expenses')}
              disabled={isSubmitting}
            >
              Cancel
            </Button>
            <Button
              type="submit"
              disabled={isSubmitting}
            >
              {isSubmitting
                ? (isEditing ? 'Updating...' : 'Creating...')
                : (isEditing ? 'Update Expense' : 'Create Expense')}
            </Button>
          </div>
        </div>
      </form>
    </Card>
  );
};

export default ExpenseForm;

import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';

interface UtilityBillFormProps {
  initialData?: {
    id?: string;
    name: string;
    description: string;
    amount: number | null;
    currency: string;
    due_date: string;
    reminder_days: number;
    provider: string;
    account_number?: string;
    payment_method?: string;
    category?: string;
  };
  isEditing?: boolean;
}

const UtilityBillForm: React.FC<UtilityBillFormProps> = ({
  initialData,
  isEditing = false
}) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: initialData?.name || '',
    description: initialData?.description || '',
    amount: initialData?.amount || null,
    currency: initialData?.currency || 'USD',
    due_date: initialData?.due_date || new Date().toISOString().split('T')[0],
    reminder_days: initialData?.reminder_days || 7,
    provider: initialData?.provider || '',
    account_number: initialData?.account_number || '',
    payment_method: initialData?.payment_method || '',
    category: initialData?.category || 'electricity',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));

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

    if (!formData.name.trim()) {
      newErrors.name = 'Name is required';
    }

    if (!formData.provider.trim()) {
      newErrors.provider = 'Provider is required';
    }

    if (!formData.due_date) {
      newErrors.due_date = 'Due date is required';
    }

    if (formData.reminder_days < 0 || formData.reminder_days > 30) {
      newErrors.reminder_days = 'Reminder days must be between 0 and 30';
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
        // Update existing utility bill
        await axios.put(`/api/utility-bills/${initialData.id}`, formData);
        navigate(`/utility-bills/${initialData.id}`);
      } else {
        // Create new utility bill
        const response = await axios.post('/api/utility-bills', formData);
        navigate(`/utility-bills/${response.data.utility_bill_id}`);
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
      } else {
        setSubmitError(error.response?.data?.error || 'An unexpected error occurred. Please try again.');
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

  const categoryOptions = [
    { value: 'electricity', label: 'Electricity' },
    { value: 'water', label: 'Water' },
    { value: 'gas', label: 'Gas' },
    { value: 'internet', label: 'Internet' },
    { value: 'phone', label: 'Phone' },
    { value: 'rent', label: 'Rent' },
    { value: 'mortgage', label: 'Mortgage' },
    { value: 'other', label: 'Other' },
  ];

  const paymentMethodOptions = [
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'credit_card', label: 'Credit Card' },
    { value: 'debit_card', label: 'Debit Card' },
    { value: 'direct_debit', label: 'Direct Debit' },
    { value: 'cash', label: 'Cash' },
    { value: 'check', label: 'Check' },
    { value: 'other', label: 'Other' },
  ];

  return (
    <Card className="max-w-2xl mx-auto bg-surface shadow-elevation-1">
      <div className="px-6 py-4 border-b border-outline-variant">
        <h2 className="text-headline-small text-on-surface font-medium">
          {isEditing ? 'Edit Utility Bill' : 'Add New Utility Bill'}
        </h2>
      </div>

      <form onSubmit={handleSubmit} className="p-6">
        {submitError && (
          <div className="mb-4 p-3 bg-error-container border border-error text-on-error-container rounded">
            {submitError}
          </div>
        )}

        <div className="space-y-6">
          {/* Name */}
          <div>
            <label htmlFor="name" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Name <span className="text-error">*</span>
            </label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.name ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
              placeholder="e.g. Electric Bill, Water Bill, etc."
            />
            {errors.name && (
              <p className="mt-1 text-body-small text-error">{errors.name}</p>
            )}
          </div>

          {/* Category */}
          <div>
            <label htmlFor="category" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Category <span className="text-error">*</span>
            </label>
            <select
              id="category"
              name="category"
              value={formData.category}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.category ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
            >
              {categoryOptions.map(option => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
            {errors.category && (
              <p className="mt-1 text-body-small text-error">{errors.category}</p>
            )}
          </div>

          {/* Provider */}
          <div>
            <label htmlFor="provider" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Provider <span className="text-error">*</span>
            </label>
            <input
              type="text"
              id="provider"
              name="provider"
              value={formData.provider}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.provider ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
              placeholder="e.g. Electric Company, Water Authority"
            />
            {errors.provider && (
              <p className="mt-1 text-body-small text-error">{errors.provider}</p>
            )}
          </div>

          {/* Account Number */}
          <div>
            <label htmlFor="account_number" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Account Number
            </label>
            <input
              type="text"
              id="account_number"
              name="account_number"
              value={formData.account_number}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.account_number ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
              placeholder="Your account/customer number"
            />
            {errors.account_number && (
              <p className="mt-1 text-body-small text-error">{errors.account_number}</p>
            )}
          </div>

          {/* Description */}
          <div>
            <label htmlFor="description" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Description
            </label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              rows={3}
              className={`w-full rounded-md border ${
                errors.description ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
              placeholder="Add details about this bill"
            />
            {errors.description && (
              <p className="mt-1 text-body-small text-error">{errors.description}</p>
            )}
          </div>

          {/* Amount and Currency (side by side) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="amount" className="block text-label-large text-on-surface-variant font-medium mb-1">
                Amount (leave empty if variable)
              </label>
              <input
                type="number"
                id="amount"
                name="amount"
                value={formData.amount !== null ? formData.amount : ''}
                onChange={handleChange}
                min="0.01"
                step="0.01"
                className={`w-full rounded-md border ${
                  errors.amount ? 'border-error' : 'border-outline'
                } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
                placeholder="0.00"
              />
              {errors.amount && (
                <p className="mt-1 text-body-small text-error">{errors.amount}</p>
              )}
            </div>

            <div>
              <label htmlFor="currency" className="block text-label-large text-on-surface-variant font-medium mb-1">
                Currency <span className="text-error">*</span>
              </label>
              <select
                id="currency"
                name="currency"
                value={formData.currency}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.currency ? 'border-error' : 'border-outline'
                } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
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

          {/* Due Date */}
          <div>
            <label htmlFor="due_date" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Due Date <span className="text-error">*</span>
            </label>
            <input
              type="date"
              id="due_date"
              name="due_date"
              value={formData.due_date}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.due_date ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
            />
            {errors.due_date && (
              <p className="mt-1 text-body-small text-error">{errors.due_date}</p>
            )}
          </div>

          {/* Payment Method */}
          <div>
            <label htmlFor="payment_method" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Preferred Payment Method
            </label>
            <select
              id="payment_method"
              name="payment_method"
              value={formData.payment_method}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.payment_method ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
            >
              <option value="">Select a payment method</option>
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

          {/* Reminder Days */}
          <div>
            <label htmlFor="reminder_days" className="block text-label-large text-on-surface-variant font-medium mb-1">
              Reminder (days before due date) <span className="text-error">*</span>
            </label>
            <input
              type="number"
              id="reminder_days"
              name="reminder_days"
              value={formData.reminder_days}
              onChange={handleChange}
              min="0"
              max="30"
              className={`w-full rounded-md border ${
                errors.reminder_days ? 'border-error' : 'border-outline'
              } shadow-sm p-2 bg-surface-variant text-on-surface-variant focus:border-primary focus:ring focus:ring-primary/20`}
            />
            {errors.reminder_days && (
              <p className="mt-1 text-body-small text-error">{errors.reminder_days}</p>
            )}
            <p className="mt-1 text-body-small text-on-surface-variant/75">
              Set to 0 if you don't want a reminder
            </p>
          </div>

          <div className="flex justify-end space-x-3 pt-4">
            <Button
              variant="outlined"
              onClick={() => navigate('/utility-bills')}
              type="button"
              className="text-primary border-primary hover:bg-primary/10"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              disabled={isSubmitting}
              className="bg-primary text-on-primary hover:bg-primary/90"
            >
              {isSubmitting ? 'Saving...' : isEditing ? 'Update Bill' : 'Add Bill'}
            </Button>
          </div>
        </div>
      </form>
    </Card>
  );
};

export default UtilityBillForm;

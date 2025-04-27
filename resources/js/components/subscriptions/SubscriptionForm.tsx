import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import Button from '../../ui/Button/Button';
import Card from '../../ui/Card/Card';

interface SubscriptionFormProps {
  initialData?: {
    id?: string;
    name: string;
    description: string;
    amount: number;
    currency: string;
    billing_cycle: string;
    start_date: string;
    website?: string;
    category?: string;
  };
  isEditing?: boolean;
}

const SubscriptionForm: React.FC<SubscriptionFormProps> = ({
  initialData,
  isEditing = false
}) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: initialData?.name || '',
    description: initialData?.description || '',
    amount: initialData?.amount || 0,
    currency: initialData?.currency || 'USD',
    billing_cycle: initialData?.billing_cycle || 'monthly',
    start_date: initialData?.start_date || new Date().toISOString().split('T')[0],
    website: initialData?.website || '',
    category: initialData?.category || '',
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

    if (!formData.description.trim()) {
      newErrors.description = 'Description is required';
    }

    if (!formData.amount || formData.amount <= 0) {
      newErrors.amount = 'Amount must be greater than zero';
    }

    if (!formData.currency) {
      newErrors.currency = 'Currency is required';
    }

    if (!formData.billing_cycle) {
      newErrors.billing_cycle = 'Billing cycle is required';
    }

    if (!formData.start_date) {
      newErrors.start_date = 'Start date is required';
    }

    if (formData.website && !/^https?:\/\/.*/.test(formData.website)) {
      newErrors.website = 'Website must be a valid URL starting with http:// or https://';
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
        // Update existing subscription
        await axios.put(`/api/subscriptions/${initialData.id}`, formData);
        navigate(`/subscriptions/${initialData.id}`);
      } else {
        // Create new subscription
        const response = await axios.post('/api/subscriptions', formData);
        navigate(`/subscriptions/${response.data.subscription_id}`);
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

  const billingCycleOptions = [
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'biweekly', label: 'Biweekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'bimonthly', label: 'Bimonthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'semiannually', label: 'Semiannually' },
    { value: 'annually', label: 'Annually' },
  ];

  const currencyOptions = [
    { value: 'USD', label: 'USD - US Dollar' },
    { value: 'EUR', label: 'EUR - Euro' },
    { value: 'GBP', label: 'GBP - British Pound' },
    { value: 'CAD', label: 'CAD - Canadian Dollar' },
    { value: 'AUD', label: 'AUD - Australian Dollar' },
    { value: 'JPY', label: 'JPY - Japanese Yen' },
  ];

  const categoryOptions = [
    { value: 'streaming', label: 'Streaming Services' },
    { value: 'software', label: 'Software & Apps' },
    { value: 'hosting', label: 'Web Hosting' },
    { value: 'utilities', label: 'Utilities' },
    { value: 'memberships', label: 'Memberships' },
    { value: 'other', label: 'Other' },
  ];

  return (
    <Card className="max-w-2xl mx-auto">
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-lg font-medium text-gray-900">
          {isEditing ? 'Edit Subscription' : 'Add New Subscription'}
        </h2>
      </div>

      <form onSubmit={handleSubmit} className="p-6">
        {submitError && (
          <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {submitError}
          </div>
        )}

        <div className="space-y-6">
          {/* Name */}
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
              Name <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.name ? 'border-red-500' : 'border-gray-300'
              } shadow-sm p-2`}
              placeholder="e.g. Netflix, Spotify, etc."
            />
            {errors.name && (
              <p className="mt-1 text-sm text-red-600">{errors.name}</p>
            )}
          </div>

          {/* Description */}
          <div>
            <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-1">
              Description <span className="text-red-500">*</span>
            </label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              rows={3}
              className={`w-full rounded-md border ${
                errors.description ? 'border-red-500' : 'border-gray-300'
              } shadow-sm p-2`}
              placeholder="Add details about this subscription"
            />
            {errors.description && (
              <p className="mt-1 text-sm text-red-600">{errors.description}</p>
            )}
          </div>

          {/* Amount and Currency (side by side) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="amount" className="block text-sm font-medium text-gray-700 mb-1">
                Amount <span className="text-red-500">*</span>
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
                  errors.amount ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
                placeholder="0.00"
              />
              {errors.amount && (
                <p className="mt-1 text-sm text-red-600">{errors.amount}</p>
              )}
            </div>

            <div>
              <label htmlFor="currency" className="block text-sm font-medium text-gray-700 mb-1">
                Currency <span className="text-red-500">*</span>
              </label>
              <select
                id="currency"
                name="currency"
                value={formData.currency}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.currency ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
              >
                {currencyOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
              {errors.currency && (
                <p className="mt-1 text-sm text-red-600">{errors.currency}</p>
              )}
            </div>
          </div>

          {/* Billing Cycle and Start Date (side by side) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="billing_cycle" className="block text-sm font-medium text-gray-700 mb-1">
                Billing Cycle <span className="text-red-500">*</span>
              </label>
              <select
                id="billing_cycle"
                name="billing_cycle"
                value={formData.billing_cycle}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.billing_cycle ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
              >
                {billingCycleOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
              {errors.billing_cycle && (
                <p className="mt-1 text-sm text-red-600">{errors.billing_cycle}</p>
              )}
            </div>

            <div>
              <label htmlFor="start_date" className="block text-sm font-medium text-gray-700 mb-1">
                Start Date <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                id="start_date"
                name="start_date"
                value={formData.start_date}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.start_date ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
              />
              {errors.start_date && (
                <p className="mt-1 text-sm text-red-600">{errors.start_date}</p>
              )}
            </div>
          </div>

          {/* Website and Category (side by side) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label htmlFor="website" className="block text-sm font-medium text-gray-700 mb-1">
                Website (Optional)
              </label>
              <input
                type="url"
                id="website"
                name="website"
                value={formData.website}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.website ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
                placeholder="https://example.com"
              />
              {errors.website && (
                <p className="mt-1 text-sm text-red-600">{errors.website}</p>
              )}
            </div>

            <div>
              <label htmlFor="category" className="block text-sm font-medium text-gray-700 mb-1">
                Category (Optional)
              </label>
              <select
                id="category"
                name="category"
                value={formData.category}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.category ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
              >
                <option value="">Select a category</option>
                {categoryOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
              {errors.category && (
                <p className="mt-1 text-sm text-red-600">{errors.category}</p>
              )}
            </div>
          </div>
        </div>

        <div className="mt-8 flex justify-end space-x-3">
          <Button
            variant="outline"
            onClick={() => navigate('/subscriptions')}
            type="button"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            isLoading={isSubmitting}
            disabled={isSubmitting}
          >
            {isEditing ? 'Update Subscription' : 'Create Subscription'}
          </Button>
        </div>
      </form>
    </Card>
  );
};

export default SubscriptionForm;

import React, { useState, FormEvent, ChangeEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui';
import { useToast } from '../../ui/Toast';

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
  const { toast } = useToast();
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
        toast({
          title: "Success",
          description: "Subscription updated successfully",
          variant: "success",
        });
        navigate(`/subscriptions/${initialData.id}`);
      } else {
        // Create new subscription
        const response = await axios.post('/api/subscriptions', formData);
        toast({
          title: "Success",
          description: "Subscription created successfully",
          variant: "success",
        });
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
    <Card variant="elevated" className="max-w-2xl mx-auto">
      <CardHeader>
        <CardTitle>{isEditing ? 'Edit Subscription' : 'Add New Subscription'}</CardTitle>
      </CardHeader>

      <CardContent>
        <form onSubmit={handleSubmit}>
          {submitError && (
            <div className="mb-6 p-3 bg-error-container border border-error text-on-error-container rounded">
              {submitError}
            </div>
          )}

          <div className="space-y-6">
            {/* Name */}
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-on-surface-variant mb-1">
                Name <span className="text-error">*</span>
              </label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.name ? 'border-error' : 'border-outline border-opacity-30'
                } shadow-sm p-2 bg-surface text-on-surface`}
                placeholder="e.g. Netflix, Spotify, etc."
              />
              {errors.name && (
                <p className="mt-1 text-sm text-error">{errors.name}</p>
              )}
            </div>

            {/* Description */}
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-on-surface-variant mb-1">
                Description <span className="text-error">*</span>
              </label>
              <textarea
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows={3}
                className={`w-full rounded-md border ${
                  errors.description ? 'border-error' : 'border-outline border-opacity-30'
                } shadow-sm p-2 bg-surface text-on-surface`}
                placeholder="Brief description of the subscription"
              />
              {errors.description && (
                <p className="mt-1 text-sm text-error">{errors.description}</p>
              )}
            </div>

            {/* Amount and Currency */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label htmlFor="amount" className="block text-sm font-medium text-on-surface-variant mb-1">
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
                    errors.amount ? 'border-error' : 'border-outline border-opacity-30'
                  } shadow-sm p-2 bg-surface text-on-surface`}
                  placeholder="0.00"
                />
                {errors.amount && (
                  <p className="mt-1 text-sm text-error">{errors.amount}</p>
                )}
              </div>

              <div>
                <label htmlFor="currency" className="block text-sm font-medium text-on-surface-variant mb-1">
                  Currency <span className="text-error">*</span>
                </label>
                <select
                  id="currency"
                  name="currency"
                  value={formData.currency}
                  onChange={handleChange}
                  className={`w-full rounded-md border ${
                    errors.currency ? 'border-error' : 'border-outline border-opacity-30'
                  } shadow-sm p-2 bg-surface text-on-surface`}
                >
                  {currencyOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
                {errors.currency && (
                  <p className="mt-1 text-sm text-error">{errors.currency}</p>
                )}
              </div>
            </div>

            {/* Billing Cycle and Start Date */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label htmlFor="billing_cycle" className="block text-sm font-medium text-on-surface-variant mb-1">
                  Billing Cycle <span className="text-error">*</span>
                </label>
                <select
                  id="billing_cycle"
                  name="billing_cycle"
                  value={formData.billing_cycle}
                  onChange={handleChange}
                  className={`w-full rounded-md border ${
                    errors.billing_cycle ? 'border-error' : 'border-outline border-opacity-30'
                  } shadow-sm p-2 bg-surface text-on-surface`}
                >
                  {billingCycleOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
                {errors.billing_cycle && (
                  <p className="mt-1 text-sm text-error">{errors.billing_cycle}</p>
                )}
              </div>

              <div>
                <label htmlFor="start_date" className="block text-sm font-medium text-on-surface-variant mb-1">
                  Start Date <span className="text-error">*</span>
                </label>
                <input
                  type="date"
                  id="start_date"
                  name="start_date"
                  value={formData.start_date}
                  onChange={handleChange}
                  className={`w-full rounded-md border ${
                    errors.start_date ? 'border-error' : 'border-outline border-opacity-30'
                  } shadow-sm p-2 bg-surface text-on-surface`}
                />
                {errors.start_date && (
                  <p className="mt-1 text-sm text-error">{errors.start_date}</p>
                )}
              </div>
            </div>

            {/* Website */}
            <div>
              <label htmlFor="website" className="block text-sm font-medium text-on-surface-variant mb-1">
                Website (Optional)
              </label>
              <input
                type="text"
                id="website"
                name="website"
                value={formData.website}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.website ? 'border-error' : 'border-outline border-opacity-30'
                } shadow-sm p-2 bg-surface text-on-surface`}
                placeholder="https://example.com"
              />
              {errors.website && (
                <p className="mt-1 text-sm text-error">{errors.website}</p>
              )}
            </div>

            {/* Category */}
            <div>
              <label htmlFor="category" className="block text-sm font-medium text-on-surface-variant mb-1">
                Category (Optional)
              </label>
              <select
                id="category"
                name="category"
                value={formData.category}
                onChange={handleChange}
                className={`w-full rounded-md border ${
                  errors.category ? 'border-error' : 'border-outline border-opacity-30'
                } shadow-sm p-2 bg-surface text-on-surface`}
              >
                <option value="">Select a category</option>
                {categoryOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
              {errors.category && (
                <p className="mt-1 text-sm text-error">{errors.category}</p>
              )}
            </div>

            {/* Form Actions */}
            <div className="flex justify-end space-x-3 pt-6">
              <Button
                type="button"
                variant="text"
                onClick={() => navigate('/subscriptions')}
                disabled={isSubmitting}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant="filled"
                disabled={isSubmitting}
              >
                {isSubmitting ? 'Saving...' : isEditing ? 'Update Subscription' : 'Create Subscription'}
              </Button>
            </div>
          </div>
        </form>
      </CardContent>
    </Card>
  );
};

export default SubscriptionForm;

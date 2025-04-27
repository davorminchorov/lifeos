import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { formatCurrency } from '../../utils/format';

interface PaymentFormProps {
  subscriptionId: string;
  subscriptionName: string;
  defaultAmount: number;
  currency: string;
  onSuccess?: () => void;
  onCancel?: () => void;
}

interface FormData {
  amount: number;
  payment_date: string;
  notes: string;
}

const PaymentForm: React.FC<PaymentFormProps> = ({
  subscriptionId,
  subscriptionName,
  defaultAmount,
  currency,
  onSuccess,
  onCancel
}) => {
  const [formData, setFormData] = useState<FormData>({
    amount: defaultAmount,
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));

    // Clear error for this field when user updates it
    if (errors[name]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  const validate = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.amount || formData.amount <= 0) {
      newErrors.amount = 'Amount must be greater than zero';
    }

    if (!formData.payment_date) {
      newErrors.payment_date = 'Payment date is required';
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
      await axios.post(`/api/subscriptions/${subscriptionId}/payments`, formData);

      if (onSuccess) {
        onSuccess();
      }
    } catch (error: any) {
      console.error('Payment submission error:', error);
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

  return (
    <Card>
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-lg font-medium text-gray-900">
          Record Payment for {subscriptionName}
        </h2>
      </div>

      <form onSubmit={handleSubmit} className="p-6">
        {submitError && (
          <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {submitError}
          </div>
        )}

        <div className="space-y-6">
          {/* Amount */}
          <div>
            <label htmlFor="amount" className="block text-sm font-medium text-gray-700 mb-1">
              Amount <span className="text-red-500">*</span>
            </label>
            <div className="relative rounded-md shadow-sm">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span className="text-gray-500 sm:text-sm">
                  {currency === 'USD' ? '$' : currency}
                </span>
              </div>
              <input
                type="number"
                id="amount"
                name="amount"
                value={formData.amount}
                onChange={handleChange}
                className={`pl-7 w-full rounded-md border ${
                  errors.amount ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
                placeholder="0.00"
                min="0.01"
                step="0.01"
              />
            </div>
            {errors.amount && (
              <p className="mt-1 text-sm text-red-600">{errors.amount}</p>
            )}
          </div>

          {/* Payment Date */}
          <div>
            <label htmlFor="payment_date" className="block text-sm font-medium text-gray-700 mb-1">
              Payment Date <span className="text-red-500">*</span>
            </label>
            <input
              type="date"
              id="payment_date"
              name="payment_date"
              value={formData.payment_date}
              onChange={handleChange}
              className={`w-full rounded-md border ${
                errors.payment_date ? 'border-red-500' : 'border-gray-300'
              } shadow-sm p-2`}
            />
            {errors.payment_date && (
              <p className="mt-1 text-sm text-red-600">{errors.payment_date}</p>
            )}
          </div>

          {/* Notes */}
          <div>
            <label htmlFor="notes" className="block text-sm font-medium text-gray-700 mb-1">
              Notes (Optional)
            </label>
            <textarea
              id="notes"
              name="notes"
              value={formData.notes}
              onChange={handleChange}
              rows={3}
              className="w-full rounded-md border border-gray-300 shadow-sm p-2"
              placeholder="Add any additional details about this payment"
            />
          </div>
        </div>

        <div className="mt-8 flex justify-end space-x-3">
          <Button
            variant="outlined"
            type="button"
            onClick={onCancel}
          >
            Cancel
          </Button>
          <Button
            type="submit"
            isLoading={isSubmitting}
            disabled={isSubmitting}
          >
            Record Payment
          </Button>
        </div>
      </form>
    </Card>
  );
};

export default PaymentForm;

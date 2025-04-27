import React from 'react';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { formatCurrency } from '../../utils/format';
import { usePaymentStore } from '../../store/paymentStore';
import { useRecordPayment } from '../../queries/paymentQueries';

interface PaymentFormProps {
  subscriptionId: string;
  subscriptionName: string;
  defaultAmount: number;
  currency: string;
  onSuccess?: () => void;
  onCancel?: () => void;
}

const PaymentForm: React.FC<PaymentFormProps> = ({
  subscriptionId,
  subscriptionName,
  defaultAmount,
  currency,
  onSuccess,
  onCancel
}) => {
  const [state, actions] = usePaymentStore();
  const { formData, formErrors, isSubmitting, submitError } = state;

  // Initialize form data if needed
  React.useEffect(() => {
    if (formData.amount === 0) {
      actions.updateFormField({ name: 'amount', value: defaultAmount });
    }
  }, [defaultAmount, formData.amount, actions]);

  // Use mutation hook
  const recordPayment = useRecordPayment();

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    actions.updateFormField({ name, value });

    // Clear error for this field when user updates it
    if (formErrors[name]) {
      actions.clearFormError(name);
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

    actions.setFormErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!validate()) {
      return;
    }

    actions.setIsSubmitting(true);
    actions.setSubmitError(null);

    recordPayment.mutate(
      { subscriptionId, formData },
      {
        onSuccess: () => {
          if (onSuccess) {
            onSuccess();
          }
        },
        onError: (error: any) => {
          console.error('Payment submission error:', error);
          if (error.response?.data?.errors) {
            // Handle validation errors from the server
            const serverErrors = error.response.data.errors;
            const formattedErrors: Record<string, string> = {};

            Object.entries(serverErrors).forEach(([key, messages]: [string, any]) => {
              formattedErrors[key] = Array.isArray(messages) ? messages[0] : messages;
            });

            actions.setFormErrors(formattedErrors);
          } else {
            actions.setSubmitError(error.response?.data?.error || 'An unexpected error occurred. Please try again.');
          }
        },
        onSettled: () => {
          actions.setIsSubmitting(false);
        }
      }
    );
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
                  formErrors.amount ? 'border-red-500' : 'border-gray-300'
                } shadow-sm p-2`}
                placeholder="0.00"
                min="0.01"
                step="0.01"
              />
            </div>
            {formErrors.amount && (
              <p className="mt-1 text-sm text-red-600">{formErrors.amount}</p>
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
                formErrors.payment_date ? 'border-red-500' : 'border-gray-300'
              } shadow-sm p-2`}
            />
            {formErrors.payment_date && (
              <p className="mt-1 text-sm text-red-600">{formErrors.payment_date}</p>
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

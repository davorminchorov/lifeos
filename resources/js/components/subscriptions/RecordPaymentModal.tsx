import React, { useState } from 'react';
import { Button } from '../../ui';

interface RecordPaymentModalProps {
  onClose: () => void;
  onSubmit: (data: RecordPaymentFormData) => void;
  defaultAmount: number;
  defaultCurrency?: string;
  isSubmitting?: boolean;
  error?: string | null;
}

export interface RecordPaymentFormData {
  amount: number;
  payment_date: string;
  notes: string;
}

const RecordPaymentModal: React.FC<RecordPaymentModalProps> = ({
  onClose,
  onSubmit,
  defaultAmount,
  defaultCurrency,
  isSubmitting = false,
  error = null,
}) => {
  const [formData, setFormData] = useState<RecordPaymentFormData>({
    amount: defaultAmount,
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
  });

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === 'amount' ? parseFloat(value) : value,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-surface rounded-lg shadow-elevation-3 w-full max-w-md">
        <div className="flex justify-between items-center px-6 py-4 border-b border-outline border-opacity-20">
          <h3 className="text-lg font-medium text-on-surface">Record Payment</h3>
          <button
            onClick={onClose}
            className="text-on-surface-variant hover:text-on-surface focus:outline-none"
          >
            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6">
          {error && (
            <div className="mb-4 p-3 bg-error-container border border-error text-on-error-container rounded">
              {error}
            </div>
          )}

          <div className="space-y-4">
            <div>
              <label htmlFor="amount" className="block text-sm font-medium text-on-surface-variant mb-1">
                Payment Amount {defaultCurrency && `(${defaultCurrency})`}
              </label>
              <input
                type="number"
                id="amount"
                name="amount"
                value={formData.amount}
                onChange={handleChange}
                min="0.01"
                step="0.01"
                className="w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface"
                required
              />
            </div>

            <div>
              <label htmlFor="payment_date" className="block text-sm font-medium text-on-surface-variant mb-1">
                Payment Date
              </label>
              <input
                type="date"
                id="payment_date"
                name="payment_date"
                value={formData.payment_date}
                onChange={handleChange}
                className="w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface"
                required
              />
            </div>

            <div>
              <label htmlFor="notes" className="block text-sm font-medium text-on-surface-variant mb-1">
                Notes (Optional)
              </label>
              <textarea
                id="notes"
                name="notes"
                value={formData.notes}
                onChange={handleChange}
                rows={3}
                className="w-full rounded-md border border-outline border-opacity-30 shadow-sm p-2 bg-surface text-on-surface"
                placeholder="Add any notes about this payment"
              />
            </div>
          </div>

          <div className="mt-6 flex justify-end space-x-3">
            <Button variant="text" onClick={onClose} disabled={isSubmitting}>
              Cancel
            </Button>
            <Button variant="filled" type="submit" disabled={isSubmitting}>
              {isSubmitting ? 'Recording...' : 'Record Payment'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default RecordPaymentModal;

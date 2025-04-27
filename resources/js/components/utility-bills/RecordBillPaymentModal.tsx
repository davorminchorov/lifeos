import React, { useState } from 'react';
import { Button } from '../../ui/Button/Button';

interface RecordBillPaymentModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: BillPaymentFormData) => void;
  initialAmount: number | null;
  currency: string;
  isLoading: boolean;
  error: string | null;
}

export interface BillPaymentFormData {
  amount: number;
  payment_date: string;
  payment_method: string;
  reference_number: string;
  notes: string;
}

const RecordBillPaymentModal: React.FC<RecordBillPaymentModalProps> = ({
  isOpen,
  onClose,
  onSubmit,
  initialAmount,
  currency,
  isLoading,
  error,
}) => {
  const [formData, setFormData] = useState<BillPaymentFormData>({
    amount: initialAmount || 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    reference_number: '',
    notes: '',
  });

  if (!isOpen) return null;

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
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
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div className="flex justify-between items-center px-6 py-4 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">Record Bill Payment</h3>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-500 focus:outline-none"
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
            <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
              {error}
            </div>
          )}

          <div className="space-y-4">
            <div>
              <label htmlFor="amount" className="block text-sm font-medium text-gray-700 mb-1">
                Payment Amount ({currency})
              </label>
              <input
                type="number"
                id="amount"
                name="amount"
                value={formData.amount}
                onChange={handleChange}
                min="0.01"
                step="0.01"
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                required
              />
            </div>

            <div>
              <label htmlFor="payment_date" className="block text-sm font-medium text-gray-700 mb-1">
                Payment Date
              </label>
              <input
                type="date"
                id="payment_date"
                name="payment_date"
                value={formData.payment_date}
                onChange={handleChange}
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                required
              />
            </div>

            <div>
              <label htmlFor="payment_method" className="block text-sm font-medium text-gray-700 mb-1">
                Payment Method
              </label>
              <select
                id="payment_method"
                name="payment_method"
                value={formData.payment_method}
                onChange={handleChange}
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                required
              >
                <option value="">Select a payment method</option>
                {paymentMethodOptions.map(option => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label htmlFor="reference_number" className="block text-sm font-medium text-gray-700 mb-1">
                Reference Number (Optional)
              </label>
              <input
                type="text"
                id="reference_number"
                name="reference_number"
                value={formData.reference_number}
                onChange={handleChange}
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                placeholder="e.g. transaction ID, confirmation code"
              />
            </div>

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
                placeholder="Add any notes about this payment"
              />
            </div>
          </div>

          <div className="mt-6 flex justify-end space-x-3">
            <Button variant="outlined" onClick={onClose} disabled={isLoading}>
              Cancel
            </Button>
            <Button type="submit" disabled={isLoading}>
              {isLoading ? 'Recording...' : 'Record Payment'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default RecordBillPaymentModal;

import React, { useState } from 'react';
import axios from 'axios';

interface ValuationFormProps {
  investmentId: string;
  onValuationAdded: () => void;
  onCancel: () => void;
}

const ValuationForm: React.FC<ValuationFormProps> = ({
  investmentId,
  onValuationAdded,
  onCancel,
}) => {
  const [formData, setFormData] = useState({
    value: '',
    date: new Date().toISOString().split('T')[0],
    notes: '',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });

    // Clear error when field is modified
    if (errors[name]) {
      const updatedErrors = { ...errors };
      delete updatedErrors[name];
      setErrors(updatedErrors);
    }
  };

  const validate = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.value || isNaN(Number(formData.value)) || Number(formData.value) <= 0) {
      newErrors.value = 'Value must be a positive number';
    }

    if (!formData.date) {
      newErrors.date = 'Date is required';
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

    try {
      await axios.post(`/api/investments/${investmentId}/valuations`, {
        ...formData,
        value: parseFloat(formData.value),
      });

      onValuationAdded();
    } catch (error: any) {
      console.error('Error adding valuation:', error);

      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        setErrors({
          general: 'Failed to add valuation. Please try again.',
        });
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {errors.general && (
        <div className="bg-red-50 border-l-4 border-red-400 p-4">
          <p className="text-sm text-red-700">{errors.general}</p>
        </div>
      )}

      <div className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
        <div className="sm:col-span-3">
          <label htmlFor="value" className="block text-sm font-medium text-gray-700">
            Current Value
          </label>
          <div className="mt-1 relative rounded-md shadow-sm">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span className="text-gray-500 sm:text-sm">$</span>
            </div>
            <input
              type="number"
              name="value"
              id="value"
              value={formData.value}
              onChange={handleChange}
              className="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
              placeholder="0.00"
              step="0.01"
            />
          </div>
          {errors.value && <p className="mt-2 text-sm text-red-600">{errors.value}</p>}
        </div>

        <div className="sm:col-span-3">
          <label htmlFor="date" className="block text-sm font-medium text-gray-700">
            Valuation Date
          </label>
          <div className="mt-1">
            <input
              type="date"
              name="date"
              id="date"
              value={formData.date}
              onChange={handleChange}
              className="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
            />
          </div>
          {errors.date && <p className="mt-2 text-sm text-red-600">{errors.date}</p>}
        </div>

        <div className="sm:col-span-6">
          <label htmlFor="notes" className="block text-sm font-medium text-gray-700">
            Notes
          </label>
          <div className="mt-1">
            <textarea
              id="notes"
              name="notes"
              rows={3}
              value={formData.notes}
              onChange={handleChange}
              className="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
              placeholder="Enter any notes about this valuation"
            />
          </div>
        </div>
      </div>

      <div className="flex justify-end space-x-3">
        <button
          type="button"
          onClick={onCancel}
          className="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Cancel
        </button>
        <button
          type="submit"
          disabled={isSubmitting}
          className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-75"
        >
          {isSubmitting ? 'Saving...' : 'Save Valuation'}
        </button>
      </div>
    </form>
  );
};

export default ValuationForm;

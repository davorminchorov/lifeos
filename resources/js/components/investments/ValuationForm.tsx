import React, { useState } from 'react';
import axios from 'axios';
import { useForm } from 'react-hook-form';
import { useToast } from '../../ui/Toast';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardFooter } from '../../ui/Card';
import { DollarSign } from 'lucide-react';

interface ValuationFormProps {
  investmentId: string;
  onSuccess?: () => void;
  onCancel?: () => void;
  initialValue?: number;
}

const ValuationForm: React.FC<ValuationFormProps> = ({
  investmentId,
  onSuccess,
  onCancel,
  initialValue = 0
}) => {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [serverError, setServerError] = useState<string | null>(null);
  const { toast } = useToast();

  const { register, handleSubmit, formState: { errors } } = useForm({
    defaultValues: {
      value: initialValue ? initialValue.toString() : '',
      date: new Date().toISOString().split('T')[0],
      notes: '',
    }
  });

  const onSubmit = async (data: any) => {
    setIsSubmitting(true);
    setServerError(null);

    try {
      await axios.post(`/api/investments/${investmentId}/valuations`, {
        ...data,
        value: parseFloat(data.value),
      });

      toast({
        title: "Success",
        description: "Valuation added successfully",
        variant: "success",
      });

      if (onSuccess) {
        onSuccess();
      }
    } catch (error: any) {
      console.error('Error adding valuation:', error);

      if (error.response?.data?.errors) {
        // Handle field-specific errors if needed
        setServerError('Please check the form for errors and try again.');
        toast({
          title: "Validation Error",
          description: "Please check the form for errors and try again.",
          variant: "destructive",
        });
      } else {
        setServerError('Failed to add valuation. Please try again.');
        toast({
          title: "Error",
          description: "Failed to add valuation. Please try again.",
          variant: "destructive",
        });
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <Card className="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
      <form onSubmit={handleSubmit(onSubmit)}>
        <CardContent className="p-6">
          <div className="space-y-6">
            {serverError && (
              <div className="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <p className="text-sm text-red-700">{serverError}</p>
              </div>
            )}

            <div>
              <label htmlFor="value" className="block text-sm font-medium text-gray-700 mb-1">
                Current Value
              </label>
              <div className="relative rounded-md shadow-sm">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span className="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  type="number"
                  id="value"
                  step="0.01"
                  min="0.01"
                  placeholder="0.00"
                  className={`block w-full pl-8 pr-12 py-3 border ${
                    errors.value ? 'border-red-300' : 'border-gray-300'
                  } rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm`}
                  {...register('value', {
                    required: 'Value is required',
                    min: { value: 0.01, message: 'Value must be greater than 0' },
                    validate: (value) => !isNaN(Number(value)) || 'Value must be a number'
                  })}
                />
                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span className="text-gray-500 sm:text-sm">USD</span>
                </div>
              </div>
              {errors.value && (
                <p className="mt-1 text-sm text-red-600">
                  {errors.value.message?.toString() || 'Please enter a valid value'}
                </p>
              )}
            </div>

            <div>
              <label htmlFor="date" className="block text-sm font-medium text-gray-700 mb-1">
                Valuation Date
              </label>
              <input
                type="date"
                id="date"
                className={`block w-full py-3 px-4 border ${
                  errors.date ? 'border-red-300' : 'border-gray-300'
                } rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm`}
                {...register('date', { required: 'Date is required' })}
              />
              {errors.date && (
                <p className="mt-1 text-sm text-red-600">
                  {errors.date.message?.toString() || 'Please select a date'}
                </p>
              )}
            </div>

            <div>
              <label htmlFor="notes" className="block text-sm font-medium text-gray-700 mb-1">
                Notes (Optional)
              </label>
              <textarea
                id="notes"
                rows={3}
                className="block w-full py-3 px-4 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Add any notes about this valuation"
                {...register('notes')}
              />
            </div>
          </div>
        </CardContent>

        <CardFooter className="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
          <Button
            type="button"
            variant="outlined"
            onClick={onCancel}
            disabled={isSubmitting}
          >
            Cancel
          </Button>
          <Button
            type="submit"
            variant="filled"
            disabled={isSubmitting}
          >
            {isSubmitting ? 'Saving...' : 'Save Valuation'}
          </Button>
        </CardFooter>
      </form>
    </Card>
  );
};

export default ValuationForm;

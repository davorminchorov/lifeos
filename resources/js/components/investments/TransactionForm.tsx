import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardFooter } from '../../ui/Card';
import { ArrowDownCircle, ArrowUpCircle, PiggyBank, Banknote, AlertCircle } from 'lucide-react';

interface TransactionFormData {
  type: 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee';
  amount: number;
  date: string;
  notes?: string;
}

interface TransactionFormProps {
  onSubmit: (data: TransactionFormData) => void;
  onCancel: () => void;
  onSuccess?: () => void;
  initialData?: Partial<TransactionFormData>;
  investmentId: string;
}

const transactionTypes = [
  {
    value: 'deposit',
    label: 'Deposit',
    description: 'Add funds to the investment',
    icon: ArrowDownCircle,
    color: 'text-green-600 bg-green-50'
  },
  {
    value: 'withdrawal',
    label: 'Withdrawal',
    description: 'Remove funds from the investment',
    icon: ArrowUpCircle,
    color: 'text-red-600 bg-red-50'
  },
  {
    value: 'dividend',
    label: 'Dividend',
    description: 'Record a dividend payment',
    icon: PiggyBank,
    color: 'text-blue-600 bg-blue-50'
  },
  {
    value: 'interest',
    label: 'Interest',
    description: 'Record interest earned',
    icon: Banknote,
    color: 'text-purple-600 bg-purple-50'
  },
  {
    value: 'fee',
    label: 'Fee',
    description: 'Record fees or expenses',
    icon: AlertCircle,
    color: 'text-orange-600 bg-orange-50'
  }
];

const TransactionForm: React.FC<TransactionFormProps> = ({
  onSubmit,
  onCancel,
  onSuccess,
  initialData,
  investmentId
}) => {
  const [selectedType, setSelectedType] = useState<string>(initialData?.type || 'deposit');

  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<TransactionFormData>({
    defaultValues: {
      type: initialData?.type || 'deposit',
      amount: initialData?.amount || undefined,
      date: initialData?.date || new Date().toISOString().split('T')[0],
      notes: initialData?.notes || ''
    }
  });

  const submitHandler = (data: TransactionFormData) => {
    onSubmit({
      ...data,
      type: data.type as 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee',
      amount: Number(data.amount)
    });

    if (onSuccess) {
      onSuccess();
    }
  };

  return (
    <Card className="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
      <form onSubmit={handleSubmit(submitHandler)}>
        <CardContent className="p-6">
          <div className="space-y-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Transaction Type
              </label>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                {transactionTypes.map((type) => {
                  const Icon = type.icon;
                  return (
                    <div key={type.value} className="relative">
                      <input
                        type="radio"
                        id={`type-${type.value}`}
                        value={type.value}
                        className="sr-only"
                        {...register('type')}
                        onChange={() => setSelectedType(type.value)}
                        checked={selectedType === type.value}
                      />
                      <label
                        htmlFor={`type-${type.value}`}
                        className={`block p-4 rounded-lg border-2 ${
                          selectedType === type.value
                            ? 'border-indigo-600 ring-2 ring-indigo-200'
                            : 'border-gray-200 hover:border-gray-300'
                        } cursor-pointer transition-all`}
                      >
                        <div className="flex items-center">
                          <div className={`p-2 rounded-full ${type.color.split(' ')[1]} mr-3`}>
                            <Icon className={`h-5 w-5 ${type.color.split(' ')[0]}`} />
                          </div>
                          <div>
                            <span className="block text-sm font-medium text-gray-900">
                              {type.label}
                            </span>
                            <span className="block text-xs text-gray-500 mt-0.5">
                              {type.description}
                            </span>
                          </div>
                        </div>
                      </label>
                    </div>
                  );
                })}
              </div>
              {errors.type && (
                <p className="mt-1 text-sm text-red-600">
                  {errors.type.message || 'Please select a transaction type'}
                </p>
              )}
            </div>

            <div>
              <label htmlFor="amount" className="block text-sm font-medium text-gray-700 mb-1">
                Amount
              </label>
              <div className="relative rounded-md shadow-sm">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span className="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  type="number"
                  id="amount"
                  step="0.01"
                  min="0.01"
                  placeholder="0.00"
                  className={`block w-full pl-8 pr-12 py-3 border ${
                    errors.amount ? 'border-red-300' : 'border-gray-300'
                  } rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm`}
                  {...register('amount', {
                    required: 'Amount is required',
                    min: { value: 0.01, message: 'Amount must be greater than 0' }
                  })}
                />
                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span className="text-gray-500 sm:text-sm">USD</span>
                </div>
              </div>
              {errors.amount && (
                <p className="mt-1 text-sm text-red-600">
                  {errors.amount.message || 'Please enter a valid amount'}
                </p>
              )}
            </div>

            <div>
              <label htmlFor="date" className="block text-sm font-medium text-gray-700 mb-1">
                Date
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
                  {errors.date.message || 'Please select a date'}
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
                placeholder="Add any additional information about this transaction"
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
            disabled={isSubmitting}
            className="bg-indigo-600 hover:bg-indigo-700 text-white"
          >
            {isSubmitting ? 'Saving...' : 'Save Transaction'}
          </Button>
        </CardFooter>
      </form>
    </Card>
  );
};

export default TransactionForm;

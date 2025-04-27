import React from 'react';
import { formatCurrency, formatDate } from '../../utils/format';
import { Card } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';

interface BillSummaryCardProps {
  name: string;
  provider: string;
  category: string;
  dueDate: string;
  amount: number | null;
  currency: string;
  status: string;
  reminderDays: number;
  reminderDate: string | null;
  hasReminder: boolean;
  onScheduleReminder: () => void;
}

const BillSummaryCard: React.FC<BillSummaryCardProps> = ({
  name,
  provider,
  category,
  dueDate,
  amount,
  currency,
  status,
  reminderDays,
  reminderDate,
  hasReminder,
  onScheduleReminder,
}) => {
  const formatCategory = (category: string): string => {
    return category.charAt(0).toUpperCase() + category.slice(1);
  };

  const renderStatusBadge = (status: string) => {
    let className = '';

    switch (status) {
      case 'paid':
        className = 'bg-green-100 text-green-800';
        break;
      case 'due':
        className = 'bg-yellow-100 text-yellow-800';
        break;
      case 'overdue':
        className = 'bg-red-100 text-red-800';
        break;
      case 'upcoming':
        className = 'bg-blue-100 text-blue-800';
        break;
      default:
        className = 'bg-gray-100 text-gray-800';
    }

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${className}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  return (
    <Card>
      <div className="px-6 py-4 border-b border-gray-200">
        <h3 className="text-lg font-medium text-gray-900">Bill Summary</h3>
      </div>

      <div className="p-6 space-y-4">
        <div className="flex justify-between items-start">
          <div>
            <p className="text-sm text-gray-500">Status</p>
            <div className="mt-1">{renderStatusBadge(status)}</div>
          </div>

          <div className="text-right">
            <p className="text-sm text-gray-500">Amount</p>
            <p className="text-xl font-semibold">
              {amount !== null ? formatCurrency(amount, currency) : 'Variable'}
            </p>
          </div>
        </div>

        <div>
          <p className="text-sm text-gray-500">Provider</p>
          <p>{provider}</p>
        </div>

        <div>
          <p className="text-sm text-gray-500">Category</p>
          <p>{formatCategory(category)}</p>
        </div>

        <div>
          <p className="text-sm text-gray-500">Due Date</p>
          <p className="font-medium">{formatDate(dueDate)}</p>
        </div>

        <div>
          <p className="text-sm text-gray-500">Reminder</p>
          {hasReminder ? (
            <div>
              <p>{reminderDays} days before due date</p>
              {reminderDate && (
                <p className="text-sm text-gray-500">
                  Reminder scheduled for {formatDate(reminderDate)}
                </p>
              )}
            </div>
          ) : (
            <p className="text-gray-500">No reminder set</p>
          )}
        </div>

        <div className="pt-2">
          <Button
            size="sm"
            variant={hasReminder ? "outlined" : "contained"}
            onClick={onScheduleReminder}
          >
            {hasReminder ? 'Edit Reminder' : 'Schedule Reminder'}
          </Button>
        </div>
      </div>
    </Card>
  );
};

export default BillSummaryCard;

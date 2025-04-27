import React, { useState } from 'react';
import { Button } from '../../ui/Button/Button';

interface ScheduleReminderModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: ReminderFormData) => void;
  currentReminderDays: number;
  dueDate: string;
  isLoading: boolean;
  error: string | null;
}

export interface ReminderFormData {
  reminder_days: number;
  reminder_method: string;
  reminder_email?: string;
  reminder_phone?: string;
  additional_notes: string;
}

const ScheduleReminderModal: React.FC<ScheduleReminderModalProps> = ({
  isOpen,
  onClose,
  onSubmit,
  currentReminderDays,
  dueDate,
  isLoading,
  error,
}) => {
  const [formData, setFormData] = useState<ReminderFormData>({
    reminder_days: currentReminderDays,
    reminder_method: 'email',
    reminder_email: '',
    reminder_phone: '',
    additional_notes: '',
  });

  if (!isOpen) return null;

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: name === 'reminder_days' ? parseInt(value, 10) : value,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  // Calculate reminder date based on due date and reminder days
  const calculateReminderDate = () => {
    if (!dueDate || formData.reminder_days <= 0) return 'Same day as due date';

    const dueDateObj = new Date(dueDate);
    const reminderDateObj = new Date(dueDateObj);
    reminderDateObj.setDate(dueDateObj.getDate() - formData.reminder_days);

    return reminderDateObj.toLocaleDateString();
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div className="flex justify-between items-center px-6 py-4 border-b border-gray-200">
          <h3 className="text-lg font-medium text-gray-900">Schedule Payment Reminder</h3>
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
              <label htmlFor="reminder_days" className="block text-sm font-medium text-gray-700 mb-1">
                Reminder (days before due date)
              </label>
              <input
                type="number"
                id="reminder_days"
                name="reminder_days"
                value={formData.reminder_days}
                onChange={handleChange}
                min="0"
                max="30"
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                required
              />
              <p className="mt-1 text-xs text-gray-500">
                You'll be reminded on: {calculateReminderDate()}
              </p>
            </div>

            <div>
              <label htmlFor="reminder_method" className="block text-sm font-medium text-gray-700 mb-1">
                Reminder Method
              </label>
              <select
                id="reminder_method"
                name="reminder_method"
                value={formData.reminder_method}
                onChange={handleChange}
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                required
              >
                <option value="email">Email</option>
                <option value="sms">SMS</option>
                <option value="both">Both Email & SMS</option>
              </select>
            </div>

            {(formData.reminder_method === 'email' || formData.reminder_method === 'both') && (
              <div>
                <label htmlFor="reminder_email" className="block text-sm font-medium text-gray-700 mb-1">
                  Email Address
                </label>
                <input
                  type="email"
                  id="reminder_email"
                  name="reminder_email"
                  value={formData.reminder_email}
                  onChange={handleChange}
                  className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                  placeholder="your@email.com"
                  required={formData.reminder_method === 'email' || formData.reminder_method === 'both'}
                />
              </div>
            )}

            {(formData.reminder_method === 'sms' || formData.reminder_method === 'both') && (
              <div>
                <label htmlFor="reminder_phone" className="block text-sm font-medium text-gray-700 mb-1">
                  Phone Number
                </label>
                <input
                  type="tel"
                  id="reminder_phone"
                  name="reminder_phone"
                  value={formData.reminder_phone}
                  onChange={handleChange}
                  className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                  placeholder="+1 (555) 123-4567"
                  required={formData.reminder_method === 'sms' || formData.reminder_method === 'both'}
                />
              </div>
            )}

            <div>
              <label htmlFor="additional_notes" className="block text-sm font-medium text-gray-700 mb-1">
                Additional Notes (Optional)
              </label>
              <textarea
                id="additional_notes"
                name="additional_notes"
                value={formData.additional_notes}
                onChange={handleChange}
                rows={3}
                className="w-full rounded-md border border-gray-300 shadow-sm p-2"
                placeholder="Any additional information for the reminder"
              />
            </div>
          </div>

          <div className="mt-6 flex justify-end space-x-3">
            <Button variant="outlined" onClick={onClose} disabled={isLoading}>
              Cancel
            </Button>
            <Button type="submit" disabled={isLoading}>
              {isLoading ? 'Scheduling...' : 'Schedule Reminder'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ScheduleReminderModal;

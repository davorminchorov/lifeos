import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import { Button } from '../../ui/Button/Button';
import { Reminder } from './ReminderCard';

interface AddReminderModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSave: (data: Omit<Reminder, 'id' | 'sent_at' | 'status'>) => void;
  initialData?: Partial<Reminder>;
  isLoading?: boolean;
}

const AddReminderModal: React.FC<AddReminderModalProps> = ({
  isOpen,
  onClose,
  onSave,
  initialData,
  isLoading = false,
}) => {
  const [formData, setFormData] = useState<Omit<Reminder, 'id' | 'sent_at' | 'status'>>({
    reminder_date: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Tomorrow
    reminder_message: '',
    entity_type: initialData?.entity_type || '',
    entity_id: initialData?.entity_id || '',
  });

  useEffect(() => {
    if (initialData) {
      setFormData({
        reminder_date: initialData.reminder_date || new Date(Date.now() + 86400000).toISOString().split('T')[0],
        reminder_message: initialData.reminder_message || '',
        entity_type: initialData.entity_type || '',
        entity_id: initialData.entity_id || '',
      });
    }
  }, [initialData, isOpen]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave(formData);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div className="bg-surface rounded-lg shadow-elevation-3 p-6 max-w-md w-full">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-headline-medium font-medium text-on-surface">
            {initialData?.id ? 'Edit Reminder' : 'New Reminder'}
          </h2>
          <button
            onClick={onClose}
            className="p-2 rounded-full hover:bg-surface-variant/20 text-on-surface-variant"
            aria-label="Close"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit}>
          <div className="space-y-4">
            <div>
              <label htmlFor="reminder_date" className="block text-label-large font-medium text-on-surface mb-1">
                Reminder Date
              </label>
              <input
                type="date"
                id="reminder_date"
                name="reminder_date"
                value={formData.reminder_date}
                onChange={handleChange}
                className="w-full p-3 rounded-md border border-outline/50 bg-surface focus:border-primary focus:ring-1 focus:ring-primary"
                min={new Date().toISOString().split('T')[0]}
                required
              />
            </div>

            <div>
              <label htmlFor="reminder_message" className="block text-label-large font-medium text-on-surface mb-1">
                Reminder Message
              </label>
              <textarea
                id="reminder_message"
                name="reminder_message"
                value={formData.reminder_message}
                onChange={handleChange}
                rows={3}
                className="w-full p-3 rounded-md border border-outline/50 bg-surface focus:border-primary focus:ring-1 focus:ring-primary"
                placeholder="What do you need to remember?"
                required
              />
            </div>

            {/* Hidden fields for entity reference */}
            {formData.entity_type && (
              <input type="hidden" name="entity_type" value={formData.entity_type} />
            )}

            {formData.entity_id && (
              <input type="hidden" name="entity_id" value={formData.entity_id} />
            )}
          </div>

          <div className="mt-6 flex justify-end space-x-3">
            <Button
              type="button"
              variant="text"
              onClick={onClose}
              disabled={isLoading}
            >
              Cancel
            </Button>
            <Button
              type="submit"
              className="bg-primary text-on-primary"
              disabled={isLoading}
            >
              {isLoading ? 'Saving...' : initialData?.id ? 'Update Reminder' : 'Save Reminder'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddReminderModal;

import React from 'react';
import { Bell, Check, X, CalendarClock, Edit, Trash } from 'lucide-react';
import { Card } from '../../ui/Card';
import { Button } from '../../ui/Button/Button';

export interface Reminder {
  id: string;
  reminder_date: string;
  reminder_message: string;
  status: 'scheduled' | 'sent' | 'cancelled';
  sent_at: string | null;
  entity_type?: string;
  entity_id?: string;
}

interface ReminderCardProps {
  reminders: Reminder[];
  title?: string;
  onAddReminder?: () => void;
  onEditReminder?: (reminder: Reminder) => void;
  onDeleteReminder?: (reminderId: string) => void;
  showEmptyState?: boolean;
}

const ReminderCard: React.FC<ReminderCardProps> = ({
  reminders,
  title = 'Upcoming Reminders',
  onAddReminder,
  onEditReminder,
  onDeleteReminder,
  showEmptyState = true,
}) => {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'scheduled':
        return 'bg-secondary-container text-on-secondary-container';
      case 'sent':
        return 'bg-tertiary-container text-on-tertiary-container';
      case 'cancelled':
        return 'bg-error-container text-on-error-container';
      default:
        return 'bg-surface-variant text-on-surface-variant';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'scheduled':
        return 'Scheduled';
      case 'sent':
        return 'Sent';
      case 'cancelled':
        return 'Cancelled';
      default:
        return 'Unknown';
    }
  };

  // Sort reminders by date (scheduled reminders first, then by date)
  const sortedReminders = [...reminders].sort((a, b) => {
    // Put scheduled reminders first
    if (a.status === 'scheduled' && b.status !== 'scheduled') return -1;
    if (a.status !== 'scheduled' && b.status === 'scheduled') return 1;

    // Then sort by date (newest first)
    return new Date(b.reminder_date).getTime() - new Date(a.reminder_date).getTime();
  });

  return (
    <Card className="shadow-elevation-2 border border-outline/40">
      <div className="px-6 py-4 border-b border-outline-variant/60 flex justify-between items-center">
        <h3 className="text-headline-small font-medium text-on-surface">{title}</h3>
        {onAddReminder && (
          <Button
            onClick={onAddReminder}
            size="sm"
            className="bg-secondary text-on-secondary shadow-elevation-1 hover:shadow-elevation-2"
          >
            <Bell className="h-4 w-4 mr-2" />
            Add Reminder
          </Button>
        )}
      </div>

      <div className="p-6">
        {reminders.length === 0 ? (
          showEmptyState ? (
            <div className="py-8 flex flex-col items-center justify-center text-center border-2 border-dashed border-outline/40 rounded-lg bg-surface-container">
              <Bell className="h-12 w-12 text-on-surface-variant/40 mb-4" />
              <p className="text-body-medium text-on-surface-variant mb-4">No reminders scheduled</p>
              {onAddReminder && (
                <Button
                  onClick={onAddReminder}
                  size="sm"
                  className="bg-secondary text-on-secondary shadow-elevation-1 hover:shadow-elevation-2"
                >
                  Schedule a Reminder
                </Button>
              )}
            </div>
          ) : (
            <div className="p-6 text-center text-body-medium text-on-surface-variant">No reminders available.</div>
          )
        ) : (
          <div className="divide-y divide-outline/40">
            {sortedReminders.map((reminder) => (
              <div key={reminder.id} className="py-4 first:pt-0 last:pb-0">
                <div className="flex justify-between items-start">
                  <div className="space-y-1">
                    <div className="flex items-center space-x-2">
                      <CalendarClock className="h-4 w-4 text-on-surface-variant" />
                      <p className="text-body-large font-medium text-on-surface">
                        {formatDate(reminder.reminder_date)}
                      </p>
                    </div>
                    <p className="text-body-medium text-on-surface-variant">
                      {reminder.reminder_message}
                    </p>
                    {reminder.sent_at && (
                      <p className="text-body-small text-on-surface-variant">
                        Sent on {formatDate(reminder.sent_at)}
                      </p>
                    )}
                  </div>
                  <div className="flex flex-col items-end space-y-2">
                    <span className={`text-label-small px-3 py-1 rounded-full font-medium inline-block shadow-elevation-1 ${getStatusColor(reminder.status)}`}>
                      {getStatusLabel(reminder.status)}
                    </span>

                    {reminder.status === 'scheduled' && (
                      <div className="flex space-x-1">
                        {onEditReminder && (
                          <button
                            onClick={() => onEditReminder(reminder)}
                            className="p-1 rounded-full hover:bg-surface-variant/20"
                            aria-label="Edit reminder"
                          >
                            <Edit className="h-4 w-4 text-on-surface-variant" />
                          </button>
                        )}
                        {onDeleteReminder && (
                          <button
                            onClick={() => onDeleteReminder(reminder.id)}
                            className="p-1 rounded-full hover:bg-surface-variant/20"
                            aria-label="Delete reminder"
                          >
                            <Trash className="h-4 w-4 text-error" />
                          </button>
                        )}
                      </div>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </Card>
  );
};

export default ReminderCard;

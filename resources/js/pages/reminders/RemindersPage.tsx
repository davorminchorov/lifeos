import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Bell, Plus } from 'lucide-react';
import ReminderCard, { Reminder } from '../../components/reminders/ReminderCard';
import AddReminderModal from '../../components/reminders/AddReminderModal';
import { Button } from '../../ui/Button/Button';
import PageTitle from '../../components/common/PageTitle';
import PageLayout from '../../components/common/PageLayout';
import Skeleton from '../../ui/Skeleton';
import { useToast } from '../../ui/Toast';

export default function RemindersPage() {
  const [reminders, setReminders] = useState<Reminder[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showAddReminderModal, setShowAddReminderModal] = useState(false);
  const [selectedReminder, setSelectedReminder] = useState<Reminder | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    fetchReminders();
  }, []);

  const fetchReminders = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await axios.get('/api/reminders');
      setReminders(response.data.data);
    } catch (error) {
      console.error('Error fetching reminders:', error);
      setError('Failed to load reminders. Please try again later.');
      toast({
        title: 'Error',
        description: 'Failed to load reminders',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleAddReminder = () => {
    setSelectedReminder(null);
    setShowAddReminderModal(true);
  };

  const handleEditReminder = (reminder: Reminder) => {
    setSelectedReminder(reminder);
    setShowAddReminderModal(true);
  };

  const handleSaveReminder = async (data: Omit<Reminder, 'id' | 'sent_at' | 'status'>) => {
    setIsSaving(true);
    try {
      if (selectedReminder?.id) {
        // Update existing reminder
        const response = await axios.put(`/api/reminders/${selectedReminder.id}`, data);
        setReminders(reminders.map(r => r.id === selectedReminder.id ? response.data.data : r));
        toast({
          title: 'Success',
          description: 'Reminder updated successfully',
          variant: 'default'
        });
      } else {
        // Create new reminder
        const response = await axios.post('/api/reminders', data);
        setReminders([...reminders, response.data.data]);
        toast({
          title: 'Success',
          description: 'Reminder created successfully',
          variant: 'default'
        });
      }
      setShowAddReminderModal(false);
    } catch (error) {
      console.error('Error saving reminder:', error);
      toast({
        title: 'Error',
        description: selectedReminder?.id ? 'Failed to update reminder' : 'Failed to create reminder',
        variant: 'destructive'
      });
    } finally {
      setIsSaving(false);
    }
  };

  const handleDeleteReminder = async (reminderId: string) => {
    if (window.confirm('Are you sure you want to delete this reminder?')) {
      try {
        await axios.delete(`/api/reminders/${reminderId}`);
        setReminders(reminders.filter(reminder => reminder.id !== reminderId));
        toast({
          title: 'Success',
          description: 'Reminder deleted successfully',
          variant: 'default'
        });
      } catch (error) {
        console.error('Error deleting reminder:', error);
        toast({
          title: 'Error',
          description: 'Failed to delete reminder',
          variant: 'destructive'
        });
      }
    }
  };

  // Group reminders by status
  const scheduledReminders = reminders.filter(r => r.status === 'scheduled');
  const sentReminders = reminders.filter(r => r.status === 'sent');

  return (
    <PageLayout>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center mb-8">
          <PageTitle
            title="Reminders"
            description="Manage your scheduled reminders and see past reminder history"
            icon={<Bell className="h-8 w-8" />}
          />
          <Button
            onClick={handleAddReminder}
            className="bg-primary text-on-primary shadow-elevation-1 hover:shadow-elevation-2"
          >
            <Plus className="h-4 w-4 mr-2" />
            New Reminder
          </Button>
        </div>

        {loading ? (
          <div className="space-y-6">
            <Skeleton className="h-64 w-full rounded-lg" />
            <Skeleton className="h-64 w-full rounded-lg" />
          </div>
        ) : error ? (
          <div className="bg-error-container text-on-error-container p-6 rounded-lg shadow-elevation-1">
            <p>{error}</p>
            <Button
              onClick={fetchReminders}
              variant="text"
              className="mt-2"
            >
              Try Again
            </Button>
          </div>
        ) : (
          <div className="space-y-8">
            <ReminderCard
              reminders={scheduledReminders}
              title="Upcoming Reminders"
              onAddReminder={handleAddReminder}
              onEditReminder={handleEditReminder}
              onDeleteReminder={handleDeleteReminder}
            />

            <ReminderCard
              reminders={sentReminders}
              title="Reminder History"
              showEmptyState={false}
            />
          </div>
        )}
      </div>

      <AddReminderModal
        isOpen={showAddReminderModal}
        onClose={() => setShowAddReminderModal(false)}
        onSave={handleSaveReminder}
        initialData={selectedReminder || undefined}
        isLoading={isSaving}
      />
    </PageLayout>
  );
}

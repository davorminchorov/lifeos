import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';

// Reminder data interfaces
export interface Reminder {
  id: string;
  title: string;
  description?: string;
  due_date: string;
  status: 'pending' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high';
  related_type?: string;
  related_id?: string;
  related_name?: string;
  created_at: string;
  updated_at: string;
}

export interface ReminderFormData {
  title: string;
  description?: string;
  due_date: string;
  status: 'pending' | 'completed' | 'cancelled';
  priority: 'low' | 'medium' | 'high';
  related_type?: string;
  related_id?: string;
}

// Query keys
export const reminderKeys = {
  all: ['reminders'] as const,
  lists: () => [...reminderKeys.all, 'list'] as const,
  list: (filters: any) => [...reminderKeys.lists(), { filters }] as const,
  details: () => [...reminderKeys.all, 'detail'] as const,
  detail: (id: string) => [...reminderKeys.details(), id] as const,
  upcoming: () => [...reminderKeys.all, 'upcoming'] as const,
};

// Basic API functions
const getReminders = async (filters = {}): Promise<{ data: Reminder[]; meta: any }> => {
  const { data } = await axios.get('/api/reminders', { params: filters });
  return data;
};

const getReminder = async (id: string): Promise<Reminder> => {
  const { data } = await axios.get(`/api/reminders/${id}`);
  return data.data;
};

const getUpcomingReminders = async (): Promise<Reminder[]> => {
  const { data } = await axios.get('/api/reminders/upcoming');
  return data.data;
};

const createReminder = async (reminderData: ReminderFormData): Promise<Reminder> => {
  const { data } = await axios.post('/api/reminders', reminderData);
  return data.data;
};

const updateReminder = async ({ id, reminderData }: { id: string; reminderData: ReminderFormData }): Promise<Reminder> => {
  const { data } = await axios.put(`/api/reminders/${id}`, reminderData);
  return data.data;
};

const updateReminderStatus = async ({ id, status }: { id: string; status: 'pending' | 'completed' | 'cancelled' }): Promise<Reminder> => {
  const { data } = await axios.patch(`/api/reminders/${id}/status`, { status });
  return data.data;
};

const deleteReminder = async (id: string): Promise<void> => {
  await axios.delete(`/api/reminders/${id}`);
};

// Query hooks
export const useReminders = (filters = {}) => {
  return useQuery({
    queryKey: reminderKeys.list(filters),
    queryFn: () => getReminders(filters)
  });
};

export const useReminder = (id: string) => {
  return useQuery({
    queryKey: reminderKeys.detail(id),
    queryFn: () => getReminder(id),
    enabled: !!id
  });
};

export const useUpcomingReminders = () => {
  return useQuery({
    queryKey: reminderKeys.upcoming(),
    queryFn: getUpcomingReminders
  });
};

// Mutation hooks
export const useCreateReminder = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createReminder,
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: reminderKeys.lists()
      });
      queryClient.invalidateQueries({
        queryKey: reminderKeys.upcoming()
      });
    }
  });
};

export const useUpdateReminder = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateReminder,
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: reminderKeys.detail(variables.id)
      });
      queryClient.invalidateQueries({
        queryKey: reminderKeys.lists()
      });
      queryClient.invalidateQueries({
        queryKey: reminderKeys.upcoming()
      });
    }
  });
};

export const useUpdateReminderStatus = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateReminderStatus,
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: reminderKeys.detail(variables.id)
      });
      queryClient.invalidateQueries({
        queryKey: reminderKeys.lists()
      });
      queryClient.invalidateQueries({
        queryKey: reminderKeys.upcoming()
      });
    }
  });
};

export const useDeleteReminder = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteReminder,
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: reminderKeys.lists()
      });
      queryClient.invalidateQueries({
        queryKey: reminderKeys.upcoming()
      });
    }
  });
};

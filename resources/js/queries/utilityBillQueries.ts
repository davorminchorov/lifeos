import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import {
  UtilityBill as UtilityBillType,
  UtilityBillFormData,
  PaymentFormData,
  ReminderFormData
} from '../store/utilityBillStore';

// Filters interface
interface UtilityBillsFilters {
  status?: string;
  category?: string;
  search?: string;
}

// Params interfaces
interface PayBillParams {
  id: string;
  paymentData: PaymentFormData;
}

interface ScheduleReminderParams {
  id: string;
  reminderData: ReminderFormData;
}

// Query keys
export const utilityBillKeys = {
  all: ['utility-bills'] as const,
  lists: () => [...utilityBillKeys.all, 'list'] as const,
  list: (filters: UtilityBillsFilters) => [...utilityBillKeys.lists(), filters] as const,
  pending: () => [...utilityBillKeys.all, 'pending'] as const,
  reminders: () => [...utilityBillKeys.all, 'reminders'] as const,
  payments: () => [...utilityBillKeys.all, 'payments'] as const,
  details: () => [...utilityBillKeys.all, 'detail'] as const,
  detail: (id: string) => [...utilityBillKeys.details(), id] as const,
  billPayments: (id: string) => [...utilityBillKeys.detail(id), 'payments'] as const,
  billReminders: (id: string) => [...utilityBillKeys.detail(id), 'reminders'] as const,
};

// Basic API functions
const getBill = async (id: string): Promise<UtilityBillType> => {
  const { data } = await axios.get(`/api/utility-bills/${id}`);
  return data;
};

const getBillPayments = async (id: string) => {
  const { data } = await axios.get(`/api/utility-bills/${id}/payments`);
  return data;
};

const getBillReminders = async (id: string) => {
  const { data } = await axios.get(`/api/utility-bills/${id}/reminders`);
  return data;
};

const payBill = async ({ id, paymentData }: PayBillParams): Promise<void> => {
  await axios.post(`/api/utility-bills/${id}/pay`, paymentData);
};

const scheduleReminder = async ({ id, reminderData }: ScheduleReminderParams): Promise<void> => {
  await axios.post(`/api/utility-bills/${id}/remind`, reminderData);
};

// React Query hooks
export const useUtilityBills = (filters: UtilityBillsFilters = {}) => {
  return useQuery({
    queryKey: utilityBillKeys.list(filters),
    queryFn: async () => {
      const response = await axios.get('/api/utility-bills', { params: filters });
      return response.data;
    }
  });
};

export const usePendingBills = () => {
  return useQuery({
    queryKey: utilityBillKeys.pending(),
    queryFn: async () => {
      const response = await axios.get('/api/utility-bills/pending');
      return response.data;
    }
  });
};

export const useUpcomingReminders = () => {
  return useQuery({
    queryKey: utilityBillKeys.reminders(),
    queryFn: async () => {
      const response = await axios.get('/api/utility-bills/reminders');
      return response.data;
    }
  });
};

export const usePaymentHistory = () => {
  return useQuery({
    queryKey: utilityBillKeys.payments(),
    queryFn: async () => {
      const response = await axios.get('/api/utility-bills/payments');
      return response.data;
    }
  });
};

export const useUtilityBillDetail = (id: string) => {
  return useQuery({
    queryKey: utilityBillKeys.detail(id),
    queryFn: () => getBill(id),
    enabled: !!id
  });
};

export const useUtilityBillPayments = (id: string) => {
  return useQuery({
    queryKey: utilityBillKeys.billPayments(id),
    queryFn: () => getBillPayments(id),
    enabled: !!id
  });
};

export const useUtilityBillReminders = (id: string) => {
  return useQuery({
    queryKey: utilityBillKeys.billReminders(id),
    queryFn: () => getBillReminders(id),
    enabled: !!id
  });
};

export const useUtilityBillCategories = () => {
  return useQuery({
    queryKey: ['utility-bill-categories'],
    queryFn: async () => {
      const response = await axios.get('/api/utility-bills/categories');
      return response.data.categories || [];
    }
  });
};

export const useCreateUtilityBill = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: UtilityBillFormData) => {
      const response = await axios.post('/api/utility-bills', formData);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.all });
    }
  });
};

export const useUpdateUtilityBill = (billId: string | undefined) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: UtilityBillFormData) => {
      if (!billId) throw new Error("Bill ID is required");
      const response = await axios.put(`/api/utility-bills/${billId}`, formData);
      return response.data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.all });
      if (billId) {
        queryClient.invalidateQueries({ queryKey: utilityBillKeys.detail(billId) });
      }
    }
  });
};

export const usePayBill = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (params: PayBillParams) => payBill(params),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.detail(variables.id) });
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.billPayments(variables.id) });
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.all });
    }
  });
};

export const useScheduleReminder = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (params: ScheduleReminderParams) => scheduleReminder(params),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.detail(variables.id) });
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.billReminders(variables.id) });
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.reminders() });
    }
  });
};

export const useDeleteUtilityBill = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: string) => {
      await axios.delete(`/api/utility-bills/${id}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: utilityBillKeys.all });
    }
  });
};

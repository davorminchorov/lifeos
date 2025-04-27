import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axios';

interface FormData {
  amount: number;
  payment_date: string;
  notes: string;
}

interface PaymentPayload {
  subscriptionId: string;
  formData: FormData;
}

// Fetch payment history with filters
export const usePaymentHistory = (filters) => {
  return useQuery({
    queryKey: ['payments', filters],
    queryFn: async () => {
      const response = await axiosClient.get('/api/payment-history', { params: filters });
      return response.data;
    }
  });
};

// Fetch subscriptions list for dropdown
export const useSubscriptionsList = () => {
  return useQuery({
    queryKey: ['subscriptions', 'list'],
    queryFn: async () => {
      const response = await axiosClient.get('/api/subscriptions/list');
      return response.data.subscriptions || [];
    }
  });
};

// Export payment history to CSV
export const useExportPaymentHistory = () => {
  return useMutation({
    mutationFn: async (filters) => {
      const response = await axiosClient.get('/api/payment-history/export', {
        params: filters,
        responseType: 'blob'
      });
      return response.data;
    }
  });
};

// Fetch a single subscription
export const useSubscription = (subscriptionId) => {
  return useQuery({
    queryKey: ['subscription', subscriptionId],
    queryFn: async () => {
      if (!subscriptionId) return null;
      const response = await axiosClient.get(`/api/subscriptions/${subscriptionId}`);
      return response.data;
    },
    enabled: !!subscriptionId
  });
};

// Record a payment
export const useRecordPayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ subscriptionId, formData }: PaymentPayload) => {
      const response = await axiosClient.post(`/api/subscriptions/${subscriptionId}/payments`, formData);
      return response.data;
    },
    onSuccess: () => {
      // Invalidate affected queries
      queryClient.invalidateQueries({ queryKey: ['payments'] });
      queryClient.invalidateQueries({ queryKey: ['subscription'] });
    }
  });
};

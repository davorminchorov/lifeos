import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axios';
import { Subscription as SubscriptionStore, SubscriptionFormData } from '../store/subscriptionStore';
import axios from 'axios';

interface SubscriptionsFilters {
  status?: string;
  category?: string;
  sort_by?: string;
}

// Types
interface Subscription {
  id: string;
  name: string;
  description: string;
  amount: number;
  currency: string;
  billing_cycle: string;
  start_date: string;
  end_date: string | null;
  status: string;
  website: string | null;
  category: string | null;
  next_payment_date: string | null;
  total_paid: number;
}

interface SubscriptionPayment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  notes: string | null;
  created_at: string;
}

interface RecordPaymentParams {
  subscriptionId: string;
  amount: number;
  payment_date: string;
  notes?: string;
}

interface CancelSubscriptionParams {
  id: string;
  end_date: string;
}

// Query keys
export const subscriptionKeys = {
  all: ['subscriptions'] as const,
  lists: () => [...subscriptionKeys.all, 'list'] as const,
  detail: (id: string) => [...subscriptionKeys.all, 'detail', id] as const,
  payments: (id: string) => [...subscriptionKeys.all, 'payments', id] as const,
};

// Query functions
const getSubscription = async (id: string): Promise<Subscription> => {
  const { data } = await axios.get(`/api/subscriptions/${id}`);
  return data;
};

const getSubscriptionPayments = async (id: string): Promise<SubscriptionPayment[]> => {
  const { data } = await axios.get(`/api/subscriptions/${id}/payments`);
  return data;
};

const recordSubscriptionPayment = async (params: RecordPaymentParams): Promise<void> => {
  await axios.post(`/api/subscriptions/${params.subscriptionId}/payments`, {
    amount: params.amount,
    payment_date: params.payment_date,
    notes: params.notes,
  });
};

const cancelSubscription = async (params: CancelSubscriptionParams): Promise<void> => {
  await axios.post(`/api/subscriptions/${params.id}/cancel`, {
    end_date: params.end_date,
  });
};

// Fetch all subscriptions with optional filters
export const useSubscriptions = (filters: SubscriptionsFilters = {}) => {
  return useQuery({
    queryKey: ['subscriptions', filters],
    queryFn: async () => {
      const response = await axiosClient.get('/api/subscriptions', {
        params: filters
      });
      return response.data;
    }
  });
};

// Fetch a single subscription by ID
export const useSubscriptionDetail = (id: string) => {
  return useQuery({
    queryKey: subscriptionKeys.detail(id),
    queryFn: () => getSubscription(id),
    enabled: !!id,
  });
};

// Fetch subscription payments history
export const useSubscriptionPayments = (id: string) => {
  return useQuery({
    queryKey: subscriptionKeys.payments(id),
    queryFn: () => getSubscriptionPayments(id),
    enabled: !!id,
  });
};

// Fetch subscription categories for filters
export const useSubscriptionCategories = () => {
  return useQuery({
    queryKey: ['subscription-categories'],
    queryFn: async () => {
      const response = await axiosClient.get('/api/subscriptions/categories');
      return response.data.categories || [];
    }
  });
};

// Create a new subscription
export const useCreateSubscription = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: SubscriptionFormData) => {
      const response = await axiosClient.post('/api/subscriptions', formData);
      return response.data;
    },
    onSuccess: () => {
      // Invalidate subscriptions queries to refresh the list
      queryClient.invalidateQueries({ queryKey: ['subscriptions'] });
    }
  });
};

// Update an existing subscription
export const useUpdateSubscription = (subscriptionId: string | undefined) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: SubscriptionFormData) => {
      if (!subscriptionId) throw new Error("Subscription ID is required");
      const response = await axiosClient.put(`/api/subscriptions/${subscriptionId}`, formData);
      return response.data;
    },
    onSuccess: (data) => {
      // Invalidate related queries to refresh data
      queryClient.invalidateQueries({ queryKey: ['subscriptions'] });
      queryClient.invalidateQueries({ queryKey: ['subscription', subscriptionId] });
    }
  });
};

// Record a subscription payment
export const useRecordPayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (params: RecordPaymentParams) => recordSubscriptionPayment(params),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: subscriptionKeys.detail(variables.subscriptionId) });
      queryClient.invalidateQueries({ queryKey: subscriptionKeys.payments(variables.subscriptionId) });
    },
  });
};

// Cancel a subscription
export const useCancelSubscription = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (params: CancelSubscriptionParams) => cancelSubscription(params),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: subscriptionKeys.detail(variables.id) });
    },
  });
};

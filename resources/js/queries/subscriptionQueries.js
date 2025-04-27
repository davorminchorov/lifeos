import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { axiosClient } from '../lib/axios';
import axios from 'axios';
// Query keys
export const subscriptionKeys = {
    all: ['subscriptions'],
    lists: () => [...subscriptionKeys.all, 'list'],
    detail: (id) => [...subscriptionKeys.all, 'detail', id],
    payments: (id) => [...subscriptionKeys.all, 'payments', id],
};
// Query functions
const getSubscription = async (id) => {
    const { data } = await axios.get(`/api/subscriptions/${id}`);
    return data;
};
const getSubscriptionPayments = async (id) => {
    const { data } = await axios.get(`/api/subscriptions/${id}/payments`);
    return data;
};
const recordSubscriptionPayment = async (params) => {
    await axios.post(`/api/subscriptions/${params.subscriptionId}/payments`, {
        amount: params.amount,
        payment_date: params.payment_date,
        notes: params.notes,
    });
};
const cancelSubscription = async (params) => {
    await axios.post(`/api/subscriptions/${params.id}/cancel`, {
        end_date: params.end_date,
    });
};
// Fetch all subscriptions with optional filters
export const useSubscriptions = (filters = {}) => {
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
export const useSubscriptionDetail = (id) => {
    return useQuery({
        queryKey: subscriptionKeys.detail(id),
        queryFn: () => getSubscription(id),
        enabled: !!id,
    });
};
// Fetch subscription payments history
export const useSubscriptionPayments = (id) => {
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
        mutationFn: async (formData) => {
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
export const useUpdateSubscription = (subscriptionId) => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (formData) => {
            if (!subscriptionId)
                throw new Error("Subscription ID is required");
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
        mutationFn: (params) => recordSubscriptionPayment(params),
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
        mutationFn: (params) => cancelSubscription(params),
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({ queryKey: subscriptionKeys.detail(variables.id) });
        },
    });
};

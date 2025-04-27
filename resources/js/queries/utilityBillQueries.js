import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const utilityBillKeys = {
    all: ['utility-bills'],
    lists: () => [...utilityBillKeys.all, 'list'],
    list: (filters) => [...utilityBillKeys.lists(), filters],
    pending: () => [...utilityBillKeys.all, 'pending'],
    reminders: () => [...utilityBillKeys.all, 'reminders'],
    payments: () => [...utilityBillKeys.all, 'payments'],
    details: () => [...utilityBillKeys.all, 'detail'],
    detail: (id) => [...utilityBillKeys.details(), id],
    billPayments: (id) => [...utilityBillKeys.detail(id), 'payments'],
    billReminders: (id) => [...utilityBillKeys.detail(id), 'reminders'],
};
// Basic API functions
const getBill = async (id) => {
    const { data } = await axios.get(`/api/utility-bills/${id}`);
    return data;
};
const getBillPayments = async (id) => {
    const { data } = await axios.get(`/api/utility-bills/${id}/payments`);
    return data;
};
const getBillReminders = async (id) => {
    const { data } = await axios.get(`/api/utility-bills/${id}/reminders`);
    return data;
};
const payBill = async ({ id, paymentData }) => {
    await axios.post(`/api/utility-bills/${id}/pay`, paymentData);
};
const scheduleReminder = async ({ id, reminderData }) => {
    await axios.post(`/api/utility-bills/${id}/remind`, reminderData);
};
// React Query hooks
export const useUtilityBills = (filters = {}) => {
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
export const useUtilityBillDetail = (id) => {
    return useQuery({
        queryKey: utilityBillKeys.detail(id),
        queryFn: () => getBill(id),
        enabled: !!id
    });
};
export const useUtilityBillPayments = (id) => {
    return useQuery({
        queryKey: utilityBillKeys.billPayments(id),
        queryFn: () => getBillPayments(id),
        enabled: !!id
    });
};
export const useUtilityBillReminders = (id) => {
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
        mutationFn: async (formData) => {
            const response = await axios.post('/api/utility-bills', formData);
            return response.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: utilityBillKeys.all });
        }
    });
};
export const useUpdateUtilityBill = (billId) => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (formData) => {
            if (!billId)
                throw new Error("Bill ID is required");
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
        mutationFn: (params) => payBill(params),
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
        mutationFn: (params) => scheduleReminder(params),
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
        mutationFn: async (id) => {
            await axios.delete(`/api/utility-bills/${id}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: utilityBillKeys.all });
        }
    });
};

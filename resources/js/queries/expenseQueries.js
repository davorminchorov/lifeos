import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const expenseKeys = {
    all: ['expenses'],
    lists: () => [...expenseKeys.all, 'list'],
    list: (filters) => [...expenseKeys.lists(), filters],
    monthlySummary: () => [...expenseKeys.all, 'monthly-summary'],
    budgetStatus: () => [...expenseKeys.all, 'budget-status'],
    categories: () => [...expenseKeys.all, 'categories'],
    details: () => [...expenseKeys.all, 'detail'],
    detail: (id) => [...expenseKeys.details(), id],
};
// Basic API functions
const getExpense = async (id) => {
    const { data } = await axios.get(`/api/expenses/${id}`);
    return data.data;
};
const getCategories = async () => {
    const { data } = await axios.get('/api/categories');
    return data.data || [];
};
const getMonthlySummary = async () => {
    const { data } = await axios.get('/api/expenses/monthly-summary');
    return data;
};
const getBudgetStatus = async () => {
    const { data } = await axios.get('/api/expenses/budget-status');
    return data;
};
const categorizeExpense = async ({ expenseId, categoryId }) => {
    await axios.post(`/api/expenses/${expenseId}/categorize`, {
        category_id: categoryId,
    });
};
// React Query hooks
export const useExpenses = (filters = {}) => {
    return useQuery({
        queryKey: expenseKeys.list(filters),
        queryFn: async () => {
            const response = await axios.get('/api/expenses', { params: filters });
            return response.data;
        }
    });
};
export const useExpenseCategories = () => {
    return useQuery({
        queryKey: expenseKeys.categories(),
        queryFn: getCategories
    });
};
export const useExpenseDetail = (id) => {
    return useQuery({
        queryKey: expenseKeys.detail(id),
        queryFn: () => getExpense(id),
        enabled: !!id
    });
};
export const useMonthlySummary = () => {
    return useQuery({
        queryKey: expenseKeys.monthlySummary(),
        queryFn: getMonthlySummary
    });
};
export const useBudgetStatus = () => {
    return useQuery({
        queryKey: expenseKeys.budgetStatus(),
        queryFn: getBudgetStatus
    });
};
export const useCreateExpense = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (formData) => {
            const response = await axios.post('/api/expenses', formData);
            return response.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: expenseKeys.all });
        }
    });
};
export const useUpdateExpense = (expenseId) => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (formData) => {
            if (!expenseId)
                throw new Error("Expense ID is required");
            const response = await axios.put(`/api/expenses/${expenseId}`, formData);
            return response.data;
        },
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({ queryKey: expenseKeys.all });
            if (expenseId) {
                queryClient.invalidateQueries({ queryKey: expenseKeys.detail(expenseId) });
            }
        }
    });
};
export const useCategorizeExpense = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (params) => categorizeExpense(params),
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({ queryKey: expenseKeys.detail(variables.expenseId) });
            queryClient.invalidateQueries({ queryKey: expenseKeys.lists() });
        }
    });
};
export const useDeleteExpense = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (id) => {
            await axios.delete(`/api/expenses/${id}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: expenseKeys.all });
        }
    });
};
export const useUploadReceipt = (expenseId) => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (file) => {
            const formData = new FormData();
            formData.append('receipt', file);
            if (expenseId) {
                const response = await axios.post(`/api/expenses/${expenseId}/receipt`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                return response.data;
            }
            else {
                // Temp upload for new expenses
                const response = await axios.post('/api/expenses/upload-receipt', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                return response.data;
            }
        },
        onSuccess: () => {
            if (expenseId) {
                queryClient.invalidateQueries({ queryKey: expenseKeys.detail(expenseId) });
            }
        }
    });
};

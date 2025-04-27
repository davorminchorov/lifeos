import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const budgetKeys = {
    all: ['budgets'],
    lists: () => [...budgetKeys.all, 'list'],
    list: (filters) => [...budgetKeys.lists(), { filters }],
    details: () => [...budgetKeys.all, 'detail'],
    detail: (id) => [...budgetKeys.details(), id],
};
// Basic API functions
const getBudgets = async (filters = {}) => {
    const { data } = await axios.get('/api/budgets', { params: filters });
    return data;
};
const getBudget = async (id) => {
    const { data } = await axios.get(`/api/budgets/${id}`);
    return data.data;
};
const createBudget = async (budgetData) => {
    const { data } = await axios.post('/api/budgets', budgetData);
    return data.data;
};
const updateBudget = async ({ id, budgetData }) => {
    const { data } = await axios.put(`/api/budgets/${id}`, budgetData);
    return data.data;
};
const deleteBudget = async (id) => {
    await axios.delete(`/api/budgets/${id}`);
};
// Query hooks
export const useBudgets = (filters = {}) => {
    return useQuery({
        queryKey: budgetKeys.list(filters),
        queryFn: () => getBudgets(filters)
    });
};
export const useBudget = (id) => {
    return useQuery({
        queryKey: budgetKeys.detail(id),
        queryFn: () => getBudget(id),
        enabled: !!id
    });
};
// Mutation hooks
export const useCreateBudget = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: createBudget,
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: budgetKeys.lists()
            });
        }
    });
};
export const useUpdateBudget = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: updateBudget,
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: budgetKeys.detail(variables.id)
            });
            queryClient.invalidateQueries({
                queryKey: budgetKeys.lists()
            });
        }
    });
};
export const useDeleteBudget = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: deleteBudget,
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: budgetKeys.lists()
            });
        }
    });
};

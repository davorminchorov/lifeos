import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const categoryKeys = {
    all: ['categories'],
    lists: () => [...categoryKeys.all, 'list'],
    list: (filters) => [...categoryKeys.lists(), { filters }],
    details: () => [...categoryKeys.all, 'detail'],
    detail: (id) => [...categoryKeys.details(), id],
};
// Basic API functions
const getCategories = async (filters = {}) => {
    const { data } = await axios.get('/api/categories', { params: filters });
    return data;
};
const getCategory = async (id) => {
    const { data } = await axios.get(`/api/categories/${id}`);
    return data.data;
};
const createCategory = async (categoryData) => {
    const { data } = await axios.post('/api/categories', categoryData);
    return data.data;
};
const updateCategory = async ({ id, categoryData }) => {
    const { data } = await axios.put(`/api/categories/${id}`, categoryData);
    return data.data;
};
const deleteCategory = async (id) => {
    await axios.delete(`/api/categories/${id}`);
};
// Query hooks
export const useCategories = (filters = {}) => {
    return useQuery({
        queryKey: categoryKeys.list(filters),
        queryFn: () => getCategories(filters)
    });
};
export const useCategory = (id) => {
    return useQuery({
        queryKey: categoryKeys.detail(id),
        queryFn: () => getCategory(id),
        enabled: !!id
    });
};
// Mutation hooks
export const useCreateCategory = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: createCategory,
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: categoryKeys.lists()
            });
        }
    });
};
export const useUpdateCategory = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: updateCategory,
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: categoryKeys.detail(variables.id)
            });
            queryClient.invalidateQueries({
                queryKey: categoryKeys.lists()
            });
        }
    });
};
export const useDeleteCategory = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: deleteCategory,
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: categoryKeys.lists()
            });
        }
    });
};

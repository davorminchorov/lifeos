import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';

// Category data interfaces
export interface Category {
  id: string;
  name: string;
  description?: string;
  color?: string;
  icon?: string;
  parent_id?: string;
  parent_name?: string;
  count?: number;
  total_amount?: number;
  created_at: string;
  updated_at: string;
}

export interface CategoryFormData {
  name: string;
  description?: string;
  color?: string;
  icon?: string;
  parent_id?: string;
}

// Query keys
export const categoryKeys = {
  all: ['categories'] as const,
  lists: () => [...categoryKeys.all, 'list'] as const,
  list: (filters: any) => [...categoryKeys.lists(), { filters }] as const,
  details: () => [...categoryKeys.all, 'detail'] as const,
  detail: (id: string) => [...categoryKeys.details(), id] as const,
};

// Basic API functions
const getCategories = async (filters = {}): Promise<{ data: Category[]; meta: any }> => {
  const { data } = await axios.get('/api/categories', { params: filters });
  return data;
};

const getCategory = async (id: string): Promise<Category> => {
  const { data } = await axios.get(`/api/categories/${id}`);
  return data.data;
};

const createCategory = async (categoryData: CategoryFormData): Promise<Category> => {
  const { data } = await axios.post('/api/categories', categoryData);
  return data.data;
};

const updateCategory = async ({ id, categoryData }: { id: string; categoryData: CategoryFormData }): Promise<Category> => {
  const { data } = await axios.put(`/api/categories/${id}`, categoryData);
  return data.data;
};

const deleteCategory = async (id: string): Promise<void> => {
  await axios.delete(`/api/categories/${id}`);
};

// Query hooks
export const useCategories = (filters = {}) => {
  return useQuery({
    queryKey: categoryKeys.list(filters),
    queryFn: () => getCategories(filters)
  });
};

export const useCategory = (id: string) => {
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

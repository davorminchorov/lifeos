import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';

// Budget data interfaces
export interface Budget {
  id: string;
  name: string;
  amount: number;
  start_date: string;
  end_date: string;
  category_id: string;
  category_name?: string;
  description?: string;
  spent_amount: number;
  remaining_amount: number;
  percentage_used: number;
  created_at: string;
  updated_at: string;
}

export interface BudgetFormData {
  name: string;
  amount: number;
  start_date: string;
  end_date: string;
  category_id: string;
  description?: string;
}

// Query keys
export const budgetKeys = {
  all: ['budgets'] as const,
  lists: () => [...budgetKeys.all, 'list'] as const,
  list: (filters: any) => [...budgetKeys.lists(), { filters }] as const,
  details: () => [...budgetKeys.all, 'detail'] as const,
  detail: (id: string) => [...budgetKeys.details(), id] as const,
};

// Basic API functions
const getBudgets = async (filters = {}): Promise<{ data: Budget[]; meta: any }> => {
  const { data } = await axios.get('/api/budgets', { params: filters });
  return data;
};

const getBudget = async (id: string): Promise<Budget> => {
  const { data } = await axios.get(`/api/budgets/${id}`);
  return data.data;
};

const createBudget = async (budgetData: BudgetFormData): Promise<Budget> => {
  const { data } = await axios.post('/api/budgets', budgetData);
  return data.data;
};

const updateBudget = async ({ id, budgetData }: { id: string; budgetData: BudgetFormData }): Promise<Budget> => {
  const { data } = await axios.put(`/api/budgets/${id}`, budgetData);
  return data.data;
};

const deleteBudget = async (id: string): Promise<void> => {
  await axios.delete(`/api/budgets/${id}`);
};

// Query hooks
export const useBudgets = (filters = {}) => {
  return useQuery({
    queryKey: budgetKeys.list(filters),
    queryFn: () => getBudgets(filters)
  });
};

export const useBudget = (id: string) => {
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

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import {
  Expense as ExpenseType,
  Category as CategoryType,
  ExpenseFormData
} from '../store/expenseStore';

// Filters interface
interface ExpensesFilters {
  category_id?: string;
  date_from?: string;
  date_to?: string;
  search?: string;
  sort_by?: string;
  sort_order?: string;
  page?: number;
  per_page?: number;
}

// Params interfaces
interface CategorizeExpenseParams {
  expenseId: string;
  categoryId: string;
}

// Query keys
export const expenseKeys = {
  all: ['expenses'] as const,
  lists: () => [...expenseKeys.all, 'list'] as const,
  list: (filters: ExpensesFilters) => [...expenseKeys.lists(), filters] as const,
  monthlySummary: () => [...expenseKeys.all, 'monthly-summary'] as const,
  budgetStatus: () => [...expenseKeys.all, 'budget-status'] as const,
  categories: () => [...expenseKeys.all, 'categories'] as const,
  details: () => [...expenseKeys.all, 'detail'] as const,
  detail: (id: string) => [...expenseKeys.details(), id] as const,
};

// Basic API functions
const getExpense = async (id: string): Promise<ExpenseType> => {
  const { data } = await axios.get(`/api/expenses/${id}`);
  return data.data;
};

const getCategories = async (): Promise<CategoryType[]> => {
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

const categorizeExpense = async ({ expenseId, categoryId }: CategorizeExpenseParams): Promise<void> => {
  await axios.post(`/api/expenses/${expenseId}/categorize`, {
    category_id: categoryId,
  });
};

// React Query hooks
export const useExpenses = (filters: ExpensesFilters = {}) => {
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

export const useExpenseDetail = (id: string) => {
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
    mutationFn: async (formData: ExpenseFormData) => {
      const response = await axios.post('/api/expenses', formData);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: expenseKeys.all });
    }
  });
};

export const useUpdateExpense = (expenseId: string | undefined) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: ExpenseFormData) => {
      if (!expenseId) throw new Error("Expense ID is required");
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
    mutationFn: (params: CategorizeExpenseParams) => categorizeExpense(params),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: expenseKeys.detail(variables.expenseId) });
      queryClient.invalidateQueries({ queryKey: expenseKeys.lists() });
    }
  });
};

export const useDeleteExpense = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: string) => {
      await axios.delete(`/api/expenses/${id}`);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: expenseKeys.all });
    }
  });
};

export const useUploadReceipt = (expenseId?: string) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (file: File) => {
      const formData = new FormData();
      formData.append('receipt', file);

      if (expenseId) {
        const response = await axios.post(`/api/expenses/${expenseId}/receipt`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        return response.data;
      } else {
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

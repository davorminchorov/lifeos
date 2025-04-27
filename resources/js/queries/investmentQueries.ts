import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import { Investment, Transaction, Valuation, InvestmentFormData, TransactionFormData, ValuationFormData } from '../store/investmentStore';

// Filters interface
interface InvestmentsFilters {
  type?: string;
  search?: string;
  date_from?: string;
  date_to?: string;
  sort_by?: string;
  sort_order?: string;
  page?: number;
  per_page?: number;
}

// Params interfaces
interface UpdateStatusParams {
  id: string;
  status: string;
}

interface AddTransactionParams {
  investmentId: string;
  transactionData: TransactionFormData;
}

interface UpdateTransactionParams {
  investmentId: string;
  transactionId: string;
  transactionData: TransactionFormData;
}

// Query keys
export const investmentKeys = {
  all: ['investments'] as const,
  lists: () => [...investmentKeys.all, 'list'] as const,
  list: (filters: any) => [...investmentKeys.lists(), { filters }] as const,
  details: () => [...investmentKeys.all, 'detail'] as const,
  detail: (id: string) => [...investmentKeys.details(), id] as const,
};

export const transactionKeys = {
  all: ['transactions'] as const,
  lists: () => [...transactionKeys.all, 'list'] as const,
  list: (investmentId: string) => [...transactionKeys.lists(), investmentId] as const,
  details: () => [...transactionKeys.all, 'detail'] as const,
  detail: (id: string) => [...transactionKeys.details(), id] as const,
};

export const valuationKeys = {
  all: ['valuations'] as const,
  lists: () => [...valuationKeys.all, 'list'] as const,
  list: (investmentId: string) => [...valuationKeys.lists(), investmentId] as const,
  details: () => [...valuationKeys.all, 'detail'] as const,
  detail: (id: string) => [...valuationKeys.details(), id] as const,
};

// Basic API functions
const getInvestment = async (id: string): Promise<Investment> => {
  const { data } = await axios.get(`/api/investments/${id}`);
  return data.data;
};

const getTransactions = async (investmentId: string): Promise<Transaction[]> => {
  const { data } = await axios.get(`/api/investments/${investmentId}/transactions`);
  return data.data || [];
};

const getStats = async () => {
  const { data } = await axios.get('/api/investments/stats');
  return data;
};

const updateStatus = async ({ id, status }: UpdateStatusParams): Promise<void> => {
  await axios.patch(`/api/investments/${id}/status`, { status });
};

const addTransaction = async ({ investmentId, transactionData }: AddTransactionParams): Promise<void> => {
  await axios.post(`/api/investments/${investmentId}/transactions`, transactionData);
};

const updateTransaction = async ({ investmentId, transactionId, transactionData }: UpdateTransactionParams): Promise<void> => {
  await axios.put(`/api/investments/${investmentId}/transactions/${transactionId}`, transactionData);
};

// Investment queries
export const useInvestments = (filters = {}) => {
  return useQuery({
    queryKey: investmentKeys.list(filters),
    queryFn: async () => {
      const { data } = await axios.get('/api/investments', { params: filters });
      return data;
    }
  });
};

export const useInvestment = (id: string) => {
  return useQuery({
    queryKey: investmentKeys.detail(id),
    queryFn: async () => {
      const { data } = await axios.get(`/api/investments/${id}`);
      return data;
    },
    enabled: !!id
  });
};

// Transaction queries
export const useTransactions = (investmentId: string) => {
  return useQuery({
    queryKey: transactionKeys.list(investmentId),
    queryFn: async () => {
      const { data } = await axios.get(`/api/investments/${investmentId}/transactions`);
      return data;
    },
    enabled: !!investmentId
  });
};

export const useTransaction = (id: string) => {
  return useQuery({
    queryKey: transactionKeys.detail(id),
    queryFn: async () => {
      const { data } = await axios.get(`/api/transactions/${id}`);
      return data;
    },
    enabled: !!id
  });
};

// Valuation queries
export const useValuations = (investmentId: string) => {
  return useQuery({
    queryKey: valuationKeys.list(investmentId),
    queryFn: async () => {
      const { data } = await axios.get(`/api/investments/${investmentId}/valuations`);
      return data;
    },
    enabled: !!investmentId
  });
};

export const useValuation = (id: string) => {
  return useQuery({
    queryKey: valuationKeys.detail(id),
    queryFn: async () => {
      const { data } = await axios.get(`/api/valuations/${id}`);
      return data;
    },
    enabled: !!id
  });
};

// Mutations
export const useCreateInvestment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: InvestmentFormData) => {
      const { data } = await axios.post('/api/investments', formData);
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: investmentKeys.lists()
      });
    }
  });
};

export const useUpdateInvestment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, formData }: { id: string; formData: InvestmentFormData }) => {
      const { data } = await axios.put(`/api/investments/${id}`, formData);
      return data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(variables.id)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.lists()
      });
    }
  });
};

export const useDeleteInvestment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: string) => {
      await axios.delete(`/api/investments/${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: investmentKeys.lists()
      });
    }
  });
};

// Transaction mutations
export const useCreateTransaction = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ investmentId, formData }: { investmentId: string; formData: TransactionFormData }) => {
      const { data } = await axios.post(`/api/investments/${investmentId}/transactions`, formData);
      return data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: transactionKeys.list(variables.investmentId)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(variables.investmentId)
      });
    }
  });
};

export const useUpdateTransaction = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, formData }: { id: string; formData: TransactionFormData }) => {
      const { data } = await axios.put(`/api/transactions/${id}`, formData);
      return data;
    },
    onSuccess: (data) => {
      const investmentId = data.investment_id;
      queryClient.invalidateQueries({
        queryKey: transactionKeys.detail(data.id)
      });
      queryClient.invalidateQueries({
        queryKey: transactionKeys.list(investmentId)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(investmentId)
      });
    }
  });
};

export const useDeleteTransaction = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, investmentId }: { id: string; investmentId: string }) => {
      await axios.delete(`/api/transactions/${id}`);
      return { id, investmentId };
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: transactionKeys.list(variables.investmentId)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(variables.investmentId)
      });
    }
  });
};

// Valuation mutations
export const useCreateValuation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ investmentId, formData }: { investmentId: string; formData: ValuationFormData }) => {
      const { data } = await axios.post(`/api/investments/${investmentId}/valuations`, formData);
      return data;
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: valuationKeys.list(variables.investmentId)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(variables.investmentId)
      });
    }
  });
};

export const useUpdateValuation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, formData }: { id: string; formData: ValuationFormData }) => {
      const { data } = await axios.put(`/api/valuations/${id}`, formData);
      return data;
    },
    onSuccess: (data) => {
      const investmentId = data.investment_id;
      queryClient.invalidateQueries({
        queryKey: valuationKeys.detail(data.id)
      });
      queryClient.invalidateQueries({
        queryKey: valuationKeys.list(investmentId)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(investmentId)
      });
    }
  });
};

export const useDeleteValuation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, investmentId }: { id: string; investmentId: string }) => {
      await axios.delete(`/api/valuations/${id}`);
      return { id, investmentId };
    },
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({
        queryKey: valuationKeys.list(variables.investmentId)
      });
      queryClient.invalidateQueries({
        queryKey: investmentKeys.detail(variables.investmentId)
      });
    }
  });
};

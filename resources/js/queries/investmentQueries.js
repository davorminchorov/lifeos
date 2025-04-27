import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const investmentKeys = {
    all: ['investments'],
    lists: () => [...investmentKeys.all, 'list'],
    list: (filters) => [...investmentKeys.lists(), { filters }],
    details: () => [...investmentKeys.all, 'detail'],
    detail: (id) => [...investmentKeys.details(), id],
};
export const transactionKeys = {
    all: ['transactions'],
    lists: () => [...transactionKeys.all, 'list'],
    list: (investmentId) => [...transactionKeys.lists(), investmentId],
    details: () => [...transactionKeys.all, 'detail'],
    detail: (id) => [...transactionKeys.details(), id],
};
export const valuationKeys = {
    all: ['valuations'],
    lists: () => [...valuationKeys.all, 'list'],
    list: (investmentId) => [...valuationKeys.lists(), investmentId],
    details: () => [...valuationKeys.all, 'detail'],
    detail: (id) => [...valuationKeys.details(), id],
};
// Basic API functions
const getInvestment = async (id) => {
    const { data } = await axios.get(`/api/investments/${id}`);
    return data.data;
};
const getTransactions = async (investmentId) => {
    const { data } = await axios.get(`/api/investments/${investmentId}/transactions`);
    return data.data || [];
};
const getStats = async () => {
    const { data } = await axios.get('/api/investments/stats');
    return data;
};
const updateStatus = async ({ id, status }) => {
    await axios.patch(`/api/investments/${id}/status`, { status });
};
const addTransaction = async ({ investmentId, transactionData }) => {
    await axios.post(`/api/investments/${investmentId}/transactions`, transactionData);
};
const updateTransaction = async ({ investmentId, transactionId, transactionData }) => {
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
export const useInvestment = (id) => {
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
export const useTransactions = (investmentId) => {
    return useQuery({
        queryKey: transactionKeys.list(investmentId),
        queryFn: async () => {
            const { data } = await axios.get(`/api/investments/${investmentId}/transactions`);
            return data;
        },
        enabled: !!investmentId
    });
};
export const useTransaction = (id) => {
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
export const useValuations = (investmentId) => {
    return useQuery({
        queryKey: valuationKeys.list(investmentId),
        queryFn: async () => {
            const { data } = await axios.get(`/api/investments/${investmentId}/valuations`);
            return data;
        },
        enabled: !!investmentId
    });
};
export const useValuation = (id) => {
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
        mutationFn: async (formData) => {
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
        mutationFn: async ({ id, formData }) => {
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
        mutationFn: async (id) => {
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
        mutationFn: async ({ investmentId, formData }) => {
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
        mutationFn: async ({ id, formData }) => {
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
        mutationFn: async ({ id, investmentId }) => {
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
        mutationFn: async ({ investmentId, formData }) => {
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
        mutationFn: async ({ id, formData }) => {
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
        mutationFn: async ({ id, investmentId }) => {
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

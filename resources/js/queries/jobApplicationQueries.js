import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const jobApplicationKeys = {
    all: ['job-applications'],
    lists: () => [...jobApplicationKeys.all, 'list'],
    list: (filters) => [...jobApplicationKeys.lists(), filters],
    stats: () => [...jobApplicationKeys.all, 'stats'],
    details: () => [...jobApplicationKeys.all, 'detail'],
    detail: (id) => [...jobApplicationKeys.details(), id],
    interviews: (id) => [...jobApplicationKeys.detail(id), 'interviews']
};
// Basic API functions
const getJobApplication = async (id) => {
    const { data } = await axios.get(`/api/job-applications/${id}`);
    return data.data;
};
const getInterviews = async (jobApplicationId) => {
    const { data } = await axios.get(`/api/job-applications/${jobApplicationId}/interviews`);
    return data.data || [];
};
const getStats = async () => {
    const { data } = await axios.get('/api/job-applications/stats');
    return data;
};
const updateStatus = async ({ id, status }) => {
    await axios.patch(`/api/job-applications/${id}/status`, { status });
};
const addInterview = async ({ jobApplicationId, interviewData }) => {
    await axios.post(`/api/job-applications/${jobApplicationId}/interviews`, interviewData);
};
const updateInterview = async ({ jobApplicationId, interviewId, interviewData }) => {
    await axios.put(`/api/job-applications/${jobApplicationId}/interviews/${interviewId}`, interviewData);
};
// React Query hooks
export const useJobApplications = (filters = {}) => {
    return useQuery({
        queryKey: jobApplicationKeys.list(filters),
        queryFn: async () => {
            const response = await axios.get('/api/job-applications', { params: filters });
            return response.data;
        }
    });
};
export const useJobApplicationStats = () => {
    return useQuery({
        queryKey: jobApplicationKeys.stats(),
        queryFn: getStats
    });
};
export const useJobApplicationDetail = (id) => {
    return useQuery({
        queryKey: jobApplicationKeys.detail(id),
        queryFn: () => getJobApplication(id),
        enabled: !!id
    });
};
export const useJobApplicationInterviews = (id) => {
    return useQuery({
        queryKey: jobApplicationKeys.interviews(id),
        queryFn: () => getInterviews(id),
        enabled: !!id
    });
};
export const useCreateJobApplication = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (formData) => {
            const response = await axios.post('/api/job-applications', formData);
            return response.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: jobApplicationKeys.all });
        }
    });
};
export const useUpdateJobApplication = (jobApplicationId) => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (formData) => {
            if (!jobApplicationId)
                throw new Error("Job Application ID is required");
            const response = await axios.put(`/api/job-applications/${jobApplicationId}`, formData);
            return response.data;
        },
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({ queryKey: jobApplicationKeys.all });
            if (jobApplicationId) {
                queryClient.invalidateQueries({ queryKey: jobApplicationKeys.detail(jobApplicationId) });
            }
        }
    });
};
export const useUpdateStatus = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (params) => updateStatus(params),
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({ queryKey: jobApplicationKeys.detail(variables.id) });
            queryClient.invalidateQueries({ queryKey: jobApplicationKeys.lists() });
            queryClient.invalidateQueries({ queryKey: jobApplicationKeys.stats() });
        }
    });
};
export const useDeleteJobApplication = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async (id) => {
            await axios.delete(`/api/job-applications/${id}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: jobApplicationKeys.all });
        }
    });
};
export const useAddInterview = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (params) => addInterview(params),
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: jobApplicationKeys.interviews(variables.jobApplicationId)
            });
            queryClient.invalidateQueries({
                queryKey: jobApplicationKeys.detail(variables.jobApplicationId)
            });
        }
    });
};
export const useUpdateInterview = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (params) => updateInterview(params),
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: jobApplicationKeys.interviews(variables.jobApplicationId)
            });
            queryClient.invalidateQueries({
                queryKey: jobApplicationKeys.detail(variables.jobApplicationId)
            });
        }
    });
};
export const useDeleteInterview = () => {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: async ({ jobApplicationId, interviewId }) => {
            await axios.delete(`/api/job-applications/${jobApplicationId}/interviews/${interviewId}`);
        },
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({
                queryKey: jobApplicationKeys.interviews(variables.jobApplicationId)
            });
            queryClient.invalidateQueries({
                queryKey: jobApplicationKeys.detail(variables.jobApplicationId)
            });
        }
    });
};

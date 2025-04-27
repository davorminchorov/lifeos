import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import {
  JobApplication as JobApplicationType,
  Interview as InterviewType,
  JobApplicationFormData,
  InterviewFormData
} from '../store/jobApplicationStore';

// Filters interface
interface JobApplicationsFilters {
  status?: string;
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

interface AddInterviewParams {
  jobApplicationId: string;
  interviewData: InterviewFormData;
}

interface UpdateInterviewParams {
  jobApplicationId: string;
  interviewId: string;
  interviewData: InterviewFormData;
}

// Query keys
export const jobApplicationKeys = {
  all: ['job-applications'] as const,
  lists: () => [...jobApplicationKeys.all, 'list'] as const,
  list: (filters: JobApplicationsFilters) => [...jobApplicationKeys.lists(), filters] as const,
  stats: () => [...jobApplicationKeys.all, 'stats'] as const,
  details: () => [...jobApplicationKeys.all, 'detail'] as const,
  detail: (id: string) => [...jobApplicationKeys.details(), id] as const,
  interviews: (id: string) => [...jobApplicationKeys.detail(id), 'interviews'] as const
};

// Basic API functions
const getJobApplication = async (id: string): Promise<JobApplicationType> => {
  const { data } = await axios.get(`/api/job-applications/${id}`);
  return data.data;
};

const getInterviews = async (jobApplicationId: string): Promise<InterviewType[]> => {
  const { data } = await axios.get(`/api/job-applications/${jobApplicationId}/interviews`);
  return data.data || [];
};

const getStats = async () => {
  const { data } = await axios.get('/api/job-applications/stats');
  return data;
};

const updateStatus = async ({ id, status }: UpdateStatusParams): Promise<void> => {
  await axios.patch(`/api/job-applications/${id}/status`, { status });
};

const addInterview = async ({ jobApplicationId, interviewData }: AddInterviewParams): Promise<void> => {
  await axios.post(`/api/job-applications/${jobApplicationId}/interviews`, interviewData);
};

const updateInterview = async ({ jobApplicationId, interviewId, interviewData }: UpdateInterviewParams): Promise<void> => {
  await axios.put(`/api/job-applications/${jobApplicationId}/interviews/${interviewId}`, interviewData);
};

// React Query hooks
export const useJobApplications = (filters: JobApplicationsFilters = {}) => {
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

export const useJobApplicationDetail = (id: string) => {
  return useQuery({
    queryKey: jobApplicationKeys.detail(id),
    queryFn: () => getJobApplication(id),
    enabled: !!id
  });
};

export const useJobApplicationInterviews = (id: string) => {
  return useQuery({
    queryKey: jobApplicationKeys.interviews(id),
    queryFn: () => getInterviews(id),
    enabled: !!id
  });
};

export const useCreateJobApplication = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: JobApplicationFormData) => {
      const response = await axios.post('/api/job-applications', formData);
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: jobApplicationKeys.all });
    }
  });
};

export const useUpdateJobApplication = (jobApplicationId: string | undefined) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (formData: JobApplicationFormData) => {
      if (!jobApplicationId) throw new Error("Job Application ID is required");
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
    mutationFn: (params: UpdateStatusParams) => updateStatus(params),
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
    mutationFn: async (id: string) => {
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
    mutationFn: (params: AddInterviewParams) => addInterview(params),
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
    mutationFn: (params: UpdateInterviewParams) => updateInterview(params),
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
    mutationFn: async ({ jobApplicationId, interviewId }: { jobApplicationId: string; interviewId: string }) => {
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

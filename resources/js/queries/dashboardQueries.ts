import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';

// Dashboard data interfaces
export interface DashboardSummary {
  totalSubscriptions: number;
  activeSubscriptions: number;
  upcomingPayments: number;
  monthlyCost: number;
  pendingBills: number;
  upcomingReminders: number;
  totalInvestments: number;
  totalInvestmentValue: number;
  totalJobApplications: number;
  activeJobApplications: number;
  totalExpenses: number;
  totalExpensesAmount: number;
}

export interface DashboardWidget {
  id: string;
  type: string;
  title: string;
  position: number;
  size: 'small' | 'medium' | 'large';
  visible: boolean;
  settings?: Record<string, any>;
}

export interface DashboardLayout {
  columns: number;
  widgets: DashboardWidget[];
}

export interface FinancialData {
  investmentPerformance: any[];
  expensesByCategory: any[];
  cashFlow: any[];
}

// Query keys
export const dashboardKeys = {
  all: ['dashboard'] as const,
  summary: () => [...dashboardKeys.all, 'summary'] as const,
  layout: () => [...dashboardKeys.all, 'layout'] as const,
  widgets: () => [...dashboardKeys.all, 'widgets'] as const,
  financial: () => [...dashboardKeys.all, 'financial'] as const,
};

// Basic API functions
const getDashboardSummary = async (): Promise<DashboardSummary> => {
  const { data } = await axios.get('/api/dashboard/summary');
  return data;
};

const getDashboardLayout = async (): Promise<DashboardLayout> => {
  const { data } = await axios.get('/api/dashboard/layout');
  return data;
};

const getFinancialData = async (): Promise<FinancialData> => {
  const { data } = await axios.get('/api/dashboard/financial');
  return data;
};

const updateDashboardLayout = async (layout: DashboardLayout): Promise<DashboardLayout> => {
  const { data } = await axios.post('/api/dashboard/layout', layout);
  return data;
};

const updateWidgetSettings = async ({ widgetId, settings }: { widgetId: string; settings: Record<string, any> }): Promise<DashboardWidget> => {
  const { data } = await axios.post(`/api/dashboard/widgets/${widgetId}/settings`, { settings });
  return data;
};

// Query hooks
export const useDashboardSummary = (options = {}) => {
  return useQuery({
    queryKey: dashboardKeys.summary(),
    queryFn: getDashboardSummary,
    ...options
  });
};

export const useDashboardLayout = (options = {}) => {
  return useQuery({
    queryKey: dashboardKeys.layout(),
    queryFn: getDashboardLayout,
    ...options
  });
};

export const useFinancialData = (options = {}) => {
  return useQuery({
    queryKey: dashboardKeys.financial(),
    queryFn: getFinancialData,
    ...options
  });
};

// Mutation hooks
export const useUpdateDashboardLayout = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateDashboardLayout,
    onSuccess: (data) => {
      queryClient.invalidateQueries({
        queryKey: dashboardKeys.layout()
      });
    }
  });
};

export const useUpdateWidgetSettings = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateWidgetSettings,
    onSuccess: (data) => {
      queryClient.invalidateQueries({
        queryKey: dashboardKeys.widgets()
      });
      queryClient.invalidateQueries({
        queryKey: dashboardKeys.layout()
      });
    }
  });
};

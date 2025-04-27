import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

// Report data interfaces
export interface ReportData {
  title: string;
  type: string;
  data: any;
  meta?: any;
}

export interface ReportFilters {
  date_from?: string;
  date_to?: string;
  period?: 'day' | 'week' | 'month' | 'quarter' | 'year';
  group_by?: string;
  category_id?: string;
  subscription_id?: string;
  investment_id?: string;
}

// Query keys
export const reportKeys = {
  all: ['reports'] as const,
  payment: (filters?: ReportFilters) => [...reportKeys.all, 'payment', { filters }] as const,
  expense: (filters?: ReportFilters) => [...reportKeys.all, 'expense', { filters }] as const,
  budget: (filters?: ReportFilters) => [...reportKeys.all, 'budget', { filters }] as const,
  investment: (filters?: ReportFilters) => [...reportKeys.all, 'investment', { filters }] as const,
  dashboard: (filters?: ReportFilters) => [...reportKeys.all, 'dashboard', { filters }] as const,
};

// Basic API functions
const getPaymentReports = async (filters: ReportFilters = {}): Promise<ReportData> => {
  const { data } = await axios.get('/api/reports/payments', { params: filters });
  return data;
};

const getExpenseReports = async (filters: ReportFilters = {}): Promise<ReportData> => {
  const { data } = await axios.get('/api/reports/expenses', { params: filters });
  return data;
};

const getBudgetReports = async (filters: ReportFilters = {}): Promise<ReportData> => {
  const { data } = await axios.get('/api/reports/budgets', { params: filters });
  return data;
};

const getInvestmentReports = async (filters: ReportFilters = {}): Promise<ReportData> => {
  const { data } = await axios.get('/api/reports/investments', { params: filters });
  return data;
};

const getDashboardReports = async (filters: ReportFilters = {}): Promise<ReportData> => {
  const { data } = await axios.get('/api/reports/dashboard', { params: filters });
  return data;
};

// Query hooks
export const usePaymentReports = (filters: ReportFilters = {}) => {
  return useQuery({
    queryKey: reportKeys.payment(filters),
    queryFn: () => getPaymentReports(filters)
  });
};

export const useExpenseReports = (filters: ReportFilters = {}) => {
  return useQuery({
    queryKey: reportKeys.expense(filters),
    queryFn: () => getExpenseReports(filters)
  });
};

export const useBudgetReports = (filters: ReportFilters = {}) => {
  return useQuery({
    queryKey: reportKeys.budget(filters),
    queryFn: () => getBudgetReports(filters)
  });
};

export const useInvestmentReports = (filters: ReportFilters = {}) => {
  return useQuery({
    queryKey: reportKeys.investment(filters),
    queryFn: () => getInvestmentReports(filters)
  });
};

export const useDashboardReports = (filters: ReportFilters = {}) => {
  return useQuery({
    queryKey: reportKeys.dashboard(filters),
    queryFn: () => getDashboardReports(filters)
  });
};

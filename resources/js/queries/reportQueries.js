import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const reportKeys = {
    all: ['reports'],
    payment: (filters) => [...reportKeys.all, 'payment', { filters }],
    expense: (filters) => [...reportKeys.all, 'expense', { filters }],
    budget: (filters) => [...reportKeys.all, 'budget', { filters }],
    investment: (filters) => [...reportKeys.all, 'investment', { filters }],
    dashboard: (filters) => [...reportKeys.all, 'dashboard', { filters }],
};
// Basic API functions
const getPaymentReports = async (filters = {}) => {
    const { data } = await axios.get('/api/reports/payments', { params: filters });
    return data;
};
const getExpenseReports = async (filters = {}) => {
    const { data } = await axios.get('/api/reports/expenses', { params: filters });
    return data;
};
const getBudgetReports = async (filters = {}) => {
    const { data } = await axios.get('/api/reports/budgets', { params: filters });
    return data;
};
const getInvestmentReports = async (filters = {}) => {
    const { data } = await axios.get('/api/reports/investments', { params: filters });
    return data;
};
const getDashboardReports = async (filters = {}) => {
    const { data } = await axios.get('/api/reports/dashboard', { params: filters });
    return data;
};
// Query hooks
export const usePaymentReports = (filters = {}) => {
    return useQuery({
        queryKey: reportKeys.payment(filters),
        queryFn: () => getPaymentReports(filters)
    });
};
export const useExpenseReports = (filters = {}) => {
    return useQuery({
        queryKey: reportKeys.expense(filters),
        queryFn: () => getExpenseReports(filters)
    });
};
export const useBudgetReports = (filters = {}) => {
    return useQuery({
        queryKey: reportKeys.budget(filters),
        queryFn: () => getBudgetReports(filters)
    });
};
export const useInvestmentReports = (filters = {}) => {
    return useQuery({
        queryKey: reportKeys.investment(filters),
        queryFn: () => getInvestmentReports(filters)
    });
};
export const useDashboardReports = (filters = {}) => {
    return useQuery({
        queryKey: reportKeys.dashboard(filters),
        queryFn: () => getDashboardReports(filters)
    });
};

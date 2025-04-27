import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
// Query keys
export const dashboardKeys = {
    all: ['dashboard'],
    summary: () => [...dashboardKeys.all, 'summary'],
    layout: () => [...dashboardKeys.all, 'layout'],
    widgets: () => [...dashboardKeys.all, 'widgets'],
    financial: () => [...dashboardKeys.all, 'financial'],
};
// Basic API functions
const getDashboardSummary = async () => {
    const { data } = await axios.get('/api/dashboard/summary');
    return data;
};
const getDashboardLayout = async () => {
    const { data } = await axios.get('/api/dashboard/layout');
    return data;
};
const getFinancialData = async () => {
    const { data } = await axios.get('/api/dashboard/financial');
    return data;
};
const updateDashboardLayout = async (layout) => {
    const { data } = await axios.post('/api/dashboard/layout', layout);
    return data;
};
const updateWidgetSettings = async ({ widgetId, settings }) => {
    const { data } = await axios.post(`/api/dashboard/widgets/${widgetId}/settings`, { settings });
    return data;
};
// Query hooks
export const useDashboardSummary = (options = {}) => {
    return useQuery(Object.assign({ queryKey: dashboardKeys.summary(), queryFn: getDashboardSummary }, options));
};
export const useDashboardLayout = (options = {}) => {
    return useQuery(Object.assign({ queryKey: dashboardKeys.layout(), queryFn: getDashboardLayout }, options));
};
export const useFinancialData = (options = {}) => {
    return useQuery(Object.assign({ queryKey: dashboardKeys.financial(), queryFn: getFinancialData }, options));
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

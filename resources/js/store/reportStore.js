import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
    currentReport: 'payments',
    reportData: null,
    filters: {
        date_from: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0],
        date_to: new Date().toISOString().split('T')[0],
        period: 'month',
        group_by: 'date',
    },
    loading: false,
    error: null,
    chartConfig: {
        chartType: 'bar',
        stacked: false,
        showLegend: true,
        showDataLabels: false,
        colorScheme: 'default'
    }
};
// Type-safe actions
const actions = {
    // Data actions
    setCurrentReport: (state, currentReport) => (Object.assign(Object.assign({}, state), { currentReport })),
    setReportData: (state, reportData) => (Object.assign(Object.assign({}, state), { reportData })),
    // Loading actions
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    // Filter actions
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), filters) })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    resetFilters: (state) => (Object.assign(Object.assign({}, state), { filters: initialState.filters })),
    // Chart config actions
    setChartConfig: (state, chartConfig) => (Object.assign(Object.assign({}, state), { chartConfig })),
    updateChartConfig: (state, { name, value }) => (Object.assign(Object.assign({}, state), { chartConfig: Object.assign(Object.assign({}, state.chartConfig), { [name]: value }) })),
    resetChartConfig: (state) => (Object.assign(Object.assign({}, state), { chartConfig: initialState.chartConfig }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const reportStore = createStore({
    name: 'report',
    initialState,
    actions
});
export const useReportStore = reportStore.useStore;

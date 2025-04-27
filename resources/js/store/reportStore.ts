import { createStore } from '../utils/xstate-store-adapter';
import { ReportData, ReportFilters } from '../queries/reportQueries';

export interface ReportState {
  // Current report
  currentReport: string;
  reportData: ReportData | null;

  // Filters
  filters: ReportFilters;

  // Loading states
  loading: boolean;
  error: string | null;

  // Chart configuration
  chartConfig: {
    chartType: 'bar' | 'line' | 'pie' | 'doughnut';
    stacked: boolean;
    showLegend: boolean;
    showDataLabels: boolean;
    colorScheme: 'default' | 'monochrome' | 'colorful';
  };
}

// Initial state
const initialState: ReportState = {
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
  setCurrentReport: (state: ReportState, currentReport: string) => ({
    ...state,
    currentReport
  }),
  setReportData: (state: ReportState, reportData: ReportData | null) => ({
    ...state,
    reportData
  }),

  // Loading actions
  setLoading: (state: ReportState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: ReportState, error: string | null) => ({
    ...state,
    error
  }),

  // Filter actions
  setFilters: (state: ReportState, filters: ReportFilters) => ({
    ...state,
    filters: { ...state.filters, ...filters }
  }),
  updateFilter: (state: ReportState, { name, value }: { name: string; value: any }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  resetFilters: (state: ReportState) => ({
    ...state,
    filters: initialState.filters
  }),

  // Chart config actions
  setChartConfig: (state: ReportState, chartConfig: typeof initialState.chartConfig) => ({
    ...state,
    chartConfig
  }),
  updateChartConfig: (state: ReportState, { name, value }: { name: string; value: any }) => ({
    ...state,
    chartConfig: { ...state.chartConfig, [name]: value }
  }),
  resetChartConfig: (state: ReportState) => ({
    ...state,
    chartConfig: initialState.chartConfig
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const reportStore = createStore({
  name: 'report',
  initialState,
  actions
});

export const useReportStore = reportStore.useStore;

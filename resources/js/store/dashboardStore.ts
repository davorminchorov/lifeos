import { createStore } from '../utils/xstate-store-adapter';

// Define TypeScript interfaces for dashboard state
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

export interface DashboardState {
  // Summary data
  summary: DashboardSummary;
  layout: DashboardLayout;

  // Loading states
  loading: boolean;
  summaryLoading: boolean;
  error: string | null;

  // Financial dashboard state
  financialData: {
    investmentPerformance: any[];
    expensesByCategory: any[];
    cashFlow: any[];
  };

  // Customization state
  customizing: boolean;
  availableWidgets: DashboardWidget[];

  // Settings
  settings: {
    refreshInterval: number;
    defaultView: string;
    theme: string;
  };
}

// Initial state
const initialState: DashboardState = {
  summary: {
    totalSubscriptions: 0,
    activeSubscriptions: 0,
    upcomingPayments: 0,
    monthlyCost: 0,
    pendingBills: 0,
    upcomingReminders: 0,
    totalInvestments: 0,
    totalInvestmentValue: 0,
    totalJobApplications: 0,
    activeJobApplications: 0,
    totalExpenses: 0,
    totalExpensesAmount: 0
  },
  layout: {
    columns: 3,
    widgets: [
      {
        id: 'subscriptions-summary',
        type: 'subscription',
        title: 'Subscriptions Summary',
        position: 0,
        size: 'medium',
        visible: true
      },
      {
        id: 'utility-bills-summary',
        type: 'utility-bill',
        title: 'Utility Bills Summary',
        position: 1,
        size: 'medium',
        visible: true
      },
      {
        id: 'expenses-summary',
        type: 'expense',
        title: 'Expenses Summary',
        position: 2,
        size: 'medium',
        visible: true
      },
      {
        id: 'investments-summary',
        type: 'investment',
        title: 'Investments Summary',
        position: 3,
        size: 'medium',
        visible: true
      },
      {
        id: 'job-applications-summary',
        type: 'job-application',
        title: 'Job Applications Summary',
        position: 4,
        size: 'medium',
        visible: true
      }
    ]
  },
  loading: false,
  summaryLoading: false,
  error: null,
  financialData: {
    investmentPerformance: [],
    expensesByCategory: [],
    cashFlow: []
  },
  customizing: false,
  availableWidgets: [],
  settings: {
    refreshInterval: 30, // minutes
    defaultView: 'overview',
    theme: 'system'
  }
};

// Type-safe actions
const actions = {
  // Data fetching actions
  setSummary: (state: DashboardState, summary: DashboardSummary) => ({
    ...state,
    summary
  }),
  setLoading: (state: DashboardState, loading: boolean) => ({
    ...state,
    loading
  }),
  setSummaryLoading: (state: DashboardState, summaryLoading: boolean) => ({
    ...state,
    summaryLoading
  }),
  setError: (state: DashboardState, error: string | null) => ({
    ...state,
    error
  }),

  // Layout actions
  updateLayout: (state: DashboardState, layout: DashboardLayout) => ({
    ...state,
    layout
  }),
  updateWidget: (state: DashboardState, widget: DashboardWidget) => ({
    ...state,
    layout: {
      ...state.layout,
      widgets: state.layout.widgets.map(w =>
        w.id === widget.id ? widget : w
      )
    }
  }),
  addWidget: (state: DashboardState, widget: DashboardWidget) => ({
    ...state,
    layout: {
      ...state.layout,
      widgets: [...state.layout.widgets, widget]
    }
  }),
  removeWidget: (state: DashboardState, widgetId: string) => ({
    ...state,
    layout: {
      ...state.layout,
      widgets: state.layout.widgets.filter(w => w.id !== widgetId)
    }
  }),
  reorderWidgets: (state: DashboardState, widgetIds: string[]) => ({
    ...state,
    layout: {
      ...state.layout,
      widgets: state.layout.widgets
        .map((widget, idx) => ({
          ...widget,
          position: widgetIds.indexOf(widget.id)
        }))
        .sort((a, b) => a.position - b.position)
    }
  }),

  // Financial dashboard actions
  setFinancialData: (state: DashboardState, financialData: typeof initialState.financialData) => ({
    ...state,
    financialData
  }),

  // Customization actions
  setCustomizing: (state: DashboardState, customizing: boolean) => ({
    ...state,
    customizing
  }),
  setAvailableWidgets: (state: DashboardState, availableWidgets: DashboardWidget[]) => ({
    ...state,
    availableWidgets
  }),

  // Settings actions
  updateSettings: (state: DashboardState, settings: typeof initialState.settings) => ({
    ...state,
    settings
  }),
  updateSetting: (state: DashboardState, { key, value }: { key: string; value: any }) => ({
    ...state,
    settings: {
      ...state.settings,
      [key]: value
    }
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const dashboardStore = createStore({
  name: 'dashboard',
  initialState,
  actions
});

export const useDashboardStore = dashboardStore.useStore;

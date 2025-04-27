import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
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
    setSummary: (state, summary) => (Object.assign(Object.assign({}, state), { summary })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setSummaryLoading: (state, summaryLoading) => (Object.assign(Object.assign({}, state), { summaryLoading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    // Layout actions
    updateLayout: (state, layout) => (Object.assign(Object.assign({}, state), { layout })),
    updateWidget: (state, widget) => (Object.assign(Object.assign({}, state), { layout: Object.assign(Object.assign({}, state.layout), { widgets: state.layout.widgets.map(w => w.id === widget.id ? widget : w) }) })),
    addWidget: (state, widget) => (Object.assign(Object.assign({}, state), { layout: Object.assign(Object.assign({}, state.layout), { widgets: [...state.layout.widgets, widget] }) })),
    removeWidget: (state, widgetId) => (Object.assign(Object.assign({}, state), { layout: Object.assign(Object.assign({}, state.layout), { widgets: state.layout.widgets.filter(w => w.id !== widgetId) }) })),
    reorderWidgets: (state, widgetIds) => (Object.assign(Object.assign({}, state), { layout: Object.assign(Object.assign({}, state.layout), { widgets: state.layout.widgets
                .map((widget, idx) => (Object.assign(Object.assign({}, widget), { position: widgetIds.indexOf(widget.id) })))
                .sort((a, b) => a.position - b.position) }) })),
    // Financial dashboard actions
    setFinancialData: (state, financialData) => (Object.assign(Object.assign({}, state), { financialData })),
    // Customization actions
    setCustomizing: (state, customizing) => (Object.assign(Object.assign({}, state), { customizing })),
    setAvailableWidgets: (state, availableWidgets) => (Object.assign(Object.assign({}, state), { availableWidgets })),
    // Settings actions
    updateSettings: (state, settings) => (Object.assign(Object.assign({}, state), { settings })),
    updateSetting: (state, { key, value }) => (Object.assign(Object.assign({}, state), { settings: Object.assign(Object.assign({}, state.settings), { [key]: value }) }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const dashboardStore = createStore({
    name: 'dashboard',
    initialState,
    actions
});
export const useDashboardStore = dashboardStore.useStore;

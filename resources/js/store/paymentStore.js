import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
    // Payment history state
    payments: [],
    summary: {
        total_spent: 0,
        payments_count: 0,
        average_payment: 0,
        this_month: 0,
        previous_month: 0
    },
    subscriptions: [],
    filters: {
        subscription_id: 'all',
        from_date: '',
        to_date: ''
    },
    loading: false,
    error: null,
    exportStatus: 'idle',
    // Record payment state
    selectedSubscription: null,
    formData: {
        amount: 0,
        payment_date: new Date().toISOString().split('T')[0],
        notes: '',
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null,
};
// Type-safe actions
const actions = {
    // Payment history actions
    setPayments: (state, payments) => (Object.assign(Object.assign({}, state), { payments })),
    setSummary: (state, summary) => (Object.assign(Object.assign({}, state), { summary })),
    setSubscriptions: (state, subscriptions) => (Object.assign(Object.assign({}, state), { subscriptions })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setExportStatus: (state, exportStatus) => (Object.assign(Object.assign({}, state), { exportStatus })),
    // Record payment actions
    setSelectedSubscription: (state, selectedSubscription) => (Object.assign(Object.assign({}, state), { selectedSubscription })),
    setFormData: (state, formData) => (Object.assign(Object.assign({}, state), { formData })),
    updateFormField: (state, { name, value }) => (Object.assign(Object.assign({}, state), { formData: Object.assign(Object.assign({}, state.formData), { [name]: value }) })),
    setFormErrors: (state, formErrors) => (Object.assign(Object.assign({}, state), { formErrors })),
    clearFormError: (state, fieldName) => {
        const newErrors = Object.assign({}, state.formErrors);
        delete newErrors[fieldName];
        return Object.assign(Object.assign({}, state), { formErrors: newErrors });
    },
    setIsSubmitting: (state, isSubmitting) => (Object.assign(Object.assign({}, state), { isSubmitting })),
    setSubmitError: (state, submitError) => (Object.assign(Object.assign({}, state), { submitError })),
    resetForm: (state) => {
        var _a;
        return (Object.assign(Object.assign({}, state), { formData: {
                amount: ((_a = state.selectedSubscription) === null || _a === void 0 ? void 0 : _a.amount) || 0,
                payment_date: new Date().toISOString().split('T')[0],
                notes: '',
            }, formErrors: {}, submitError: null }));
    }
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const paymentStore = createStore({
    name: 'payment',
    initialState,
    actions
});
export const usePaymentStore = paymentStore.useStore;

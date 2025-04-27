import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
    // List state
    subscriptions: [],
    loading: false,
    error: null,
    filters: {
        status: 'all',
        category: 'all',
        sort_by: 'name'
    },
    // Detail state
    selectedSubscription: null,
    subscriptionPayments: [],
    // Form state
    formData: {
        name: '',
        description: '',
        amount: 0,
        currency: 'USD',
        billing_cycle: 'monthly',
        start_date: new Date().toISOString().split('T')[0],
        website: '',
        category: ''
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null
};
// Type-safe actions
const actions = {
    // List actions
    setSubscriptions: (state, subscriptions) => (Object.assign(Object.assign({}, state), { subscriptions })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    // Detail actions
    setSelectedSubscription: (state, subscription) => (Object.assign(Object.assign({}, state), { selectedSubscription: subscription })),
    setSubscriptionPayments: (state, payments) => (Object.assign(Object.assign({}, state), { subscriptionPayments: payments })),
    // Form actions
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
    resetForm: (state) => (Object.assign(Object.assign({}, state), { formData: initialState.formData, formErrors: {}, submitError: null })),
    initFormForEdit: (state, subscription) => (Object.assign(Object.assign({}, state), { formData: {
            name: subscription.name,
            description: subscription.description,
            amount: subscription.amount,
            currency: subscription.currency,
            billing_cycle: subscription.billing_cycle,
            start_date: subscription.start_date,
            website: subscription.website || '',
            category: subscription.category || ''
        }, formErrors: {}, submitError: null }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const subscriptionStore = createStore({
    name: 'subscription',
    initialState,
    actions
});
export const useSubscriptionStore = subscriptionStore.useStore;

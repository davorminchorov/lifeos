import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
    // List state
    budgets: [],
    loading: false,
    error: null,
    filters: {
        search: '',
        category_id: '',
        date_from: '',
        date_to: '',
        sort_by: 'created_at',
        sort_order: 'desc',
    },
    meta: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    },
    // Detail state
    selectedBudget: null,
    // Form state
    formData: {
        name: '',
        amount: 0,
        start_date: new Date().toISOString().split('T')[0],
        end_date: new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString().split('T')[0],
        category_id: '',
        description: ''
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null
};
// Type-safe actions
const actions = {
    // List actions
    setBudgets: (state, budgets) => (Object.assign(Object.assign({}, state), { budgets })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setMeta: (state, meta) => (Object.assign(Object.assign({}, state), { meta })),
    // Detail actions
    setSelectedBudget: (state, budget) => (Object.assign(Object.assign({}, state), { selectedBudget: budget })),
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
    initFormForEdit: (state, budget) => (Object.assign(Object.assign({}, state), { formData: {
            name: budget.name,
            amount: budget.amount,
            start_date: budget.start_date,
            end_date: budget.end_date,
            category_id: budget.category_id,
            description: budget.description
        }, formErrors: {}, submitError: null }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const budgetStore = createStore({
    name: 'budget',
    initialState,
    actions
});
export const useBudgetStore = budgetStore.useStore;

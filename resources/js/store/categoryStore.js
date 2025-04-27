import { createStore } from 'xstate-store';
// Initial state
const initialState = {
    // List state
    categories: [],
    loading: false,
    error: null,
    filters: {
        search: '',
        parent_id: '',
        sort_by: 'name',
        sort_order: 'asc',
    },
    meta: {
        current_page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
    },
    // Detail state
    selectedCategory: null,
    // Form state
    formData: {
        name: '',
        description: '',
        color: '#5046e5', // default color (primary)
        icon: 'tag',
        parent_id: ''
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null
};
// Type-safe actions
const actions = {
    // List actions
    setCategories: (state, categories) => (Object.assign(Object.assign({}, state), { categories })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setMeta: (state, meta) => (Object.assign(Object.assign({}, state), { meta })),
    // Detail actions
    setSelectedCategory: (state, category) => (Object.assign(Object.assign({}, state), { selectedCategory: category })),
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
    initFormForEdit: (state, category) => (Object.assign(Object.assign({}, state), { formData: {
            name: category.name,
            description: category.description || '',
            color: category.color || '#5046e5',
            icon: category.icon || 'tag',
            parent_id: category.parent_id || ''
        }, formErrors: {}, submitError: null }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const categoryStore = createStore({
    name: 'category',
    initialState,
    actions
});
export const useCategoryStore = categoryStore.useStore;

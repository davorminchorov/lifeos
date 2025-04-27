import { createStore } from 'xstate-store';
// Initial state
const initialState = {
    // List state
    expenses: [],
    categories: [],
    loading: false,
    error: null,
    filters: {
        category_id: '',
        date_from: '',
        date_to: '',
        search: '',
        sort_by: 'date',
        sort_order: 'desc',
    },
    meta: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    },
    // Detail state
    selectedExpense: null,
    // Form state
    formData: {
        title: '',
        description: '',
        amount: 0,
        currency: 'USD',
        date: new Date().toISOString().split('T')[0],
        category_id: '',
        payment_method: '',
        notes: null,
        receipt_url: null
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null,
    // Upload state
    uploadedReceipt: null,
    isUploading: false,
    uploadError: null
};
// Type-safe actions
const actions = {
    // List actions
    setExpenses: (state, expenses) => (Object.assign(Object.assign({}, state), { expenses })),
    setCategories: (state, categories) => (Object.assign(Object.assign({}, state), { categories })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setMeta: (state, meta) => (Object.assign(Object.assign({}, state), { meta })),
    // Detail actions
    setSelectedExpense: (state, expense) => (Object.assign(Object.assign({}, state), { selectedExpense: expense })),
    // Categorization action
    categorizeExpense: (state, { expenseId, categoryId, categoryName }) => (Object.assign(Object.assign({}, state), { expenses: state.expenses.map(expense => expense.id === expenseId
            ? Object.assign(Object.assign({}, expense), { category_id: categoryId, category_name: categoryName }) : expense) })),
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
    resetForm: (state) => (Object.assign(Object.assign({}, state), { formData: initialState.formData, formErrors: {}, submitError: null, uploadedReceipt: null, uploadError: null })),
    initFormForEdit: (state, expense) => (Object.assign(Object.assign({}, state), { formData: {
            title: expense.title,
            description: expense.description,
            amount: expense.amount,
            currency: expense.currency,
            date: expense.date,
            category_id: expense.category_id || '',
            payment_method: expense.payment_method,
            notes: expense.notes,
            receipt_url: expense.receipt_url
        }, formErrors: {}, submitError: null })),
    // Receipt upload actions
    setUploadedReceipt: (state, file) => (Object.assign(Object.assign({}, state), { uploadedReceipt: file })),
    setIsUploading: (state, isUploading) => (Object.assign(Object.assign({}, state), { isUploading })),
    setUploadError: (state, uploadError) => (Object.assign(Object.assign({}, state), { uploadError }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const expenseStore = createStore({
    name: 'expense',
    initialState,
    actions
});
export const useExpenseStore = expenseStore.useStore;

import { createStore } from 'xstate-store';
// Initial state
const initialState = {
    // List state
    investments: [],
    loading: false,
    error: null,
    filters: {
        type: '',
        search: '',
        date_from: '',
        date_to: '',
        sort_by: 'start_date',
        sort_order: 'desc',
    },
    meta: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    },
    // Detail state
    selectedInvestment: null,
    transactions: [],
    valuations: [],
    // Form state
    formData: {
        name: '',
        type: 'stock',
        institution: '',
        account_number: '',
        initial_investment: 0,
        start_date: new Date().toISOString().split('T')[0],
        end_date: '',
        description: '',
        notes: ''
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null,
    // Transaction form state
    transactionFormData: {
        type: 'deposit',
        amount: 0,
        date: new Date().toISOString().split('T')[0],
        notes: ''
    },
    transactionFormErrors: {},
    isTransactionSubmitting: false,
    transactionSubmitError: null,
    // Valuation form state
    valuationFormData: {
        value: 0,
        date: new Date().toISOString().split('T')[0],
        notes: ''
    },
    valuationFormErrors: {},
    isValuationSubmitting: false,
    valuationSubmitError: null
};
// Type-safe actions
const actions = {
    // List actions
    setInvestments: (state, investments) => (Object.assign(Object.assign({}, state), { investments })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setMeta: (state, meta) => (Object.assign(Object.assign({}, state), { meta })),
    // Detail actions
    setSelectedInvestment: (state, investment) => (Object.assign(Object.assign({}, state), { selectedInvestment: investment })),
    setTransactions: (state, transactions) => (Object.assign(Object.assign({}, state), { transactions })),
    setValuations: (state, valuations) => (Object.assign(Object.assign({}, state), { valuations })),
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
    initFormForEdit: (state, investment) => (Object.assign(Object.assign({}, state), { formData: {
            name: investment.name,
            type: investment.type,
            institution: investment.institution,
            account_number: investment.account_number,
            initial_investment: investment.initial_investment,
            start_date: investment.start_date,
            end_date: investment.end_date,
            description: investment.description,
            notes: investment.notes
        }, formErrors: {}, submitError: null })),
    // Transaction form actions
    setTransactionFormData: (state, transactionFormData) => (Object.assign(Object.assign({}, state), { transactionFormData })),
    updateTransactionFormField: (state, { name, value }) => (Object.assign(Object.assign({}, state), { transactionFormData: Object.assign(Object.assign({}, state.transactionFormData), { [name]: value }) })),
    setTransactionFormErrors: (state, transactionFormErrors) => (Object.assign(Object.assign({}, state), { transactionFormErrors })),
    clearTransactionFormError: (state, fieldName) => {
        const newErrors = Object.assign({}, state.transactionFormErrors);
        delete newErrors[fieldName];
        return Object.assign(Object.assign({}, state), { transactionFormErrors: newErrors });
    },
    setIsTransactionSubmitting: (state, isTransactionSubmitting) => (Object.assign(Object.assign({}, state), { isTransactionSubmitting })),
    setTransactionSubmitError: (state, transactionSubmitError) => (Object.assign(Object.assign({}, state), { transactionSubmitError })),
    resetTransactionForm: (state) => (Object.assign(Object.assign({}, state), { transactionFormData: initialState.transactionFormData, transactionFormErrors: {}, transactionSubmitError: null })),
    // Valuation form actions
    setValuationFormData: (state, valuationFormData) => (Object.assign(Object.assign({}, state), { valuationFormData })),
    updateValuationFormField: (state, { name, value }) => (Object.assign(Object.assign({}, state), { valuationFormData: Object.assign(Object.assign({}, state.valuationFormData), { [name]: value }) })),
    setValuationFormErrors: (state, valuationFormErrors) => (Object.assign(Object.assign({}, state), { valuationFormErrors })),
    clearValuationFormError: (state, fieldName) => {
        const newErrors = Object.assign({}, state.valuationFormErrors);
        delete newErrors[fieldName];
        return Object.assign(Object.assign({}, state), { valuationFormErrors: newErrors });
    },
    setIsValuationSubmitting: (state, isValuationSubmitting) => (Object.assign(Object.assign({}, state), { isValuationSubmitting })),
    setValuationSubmitError: (state, valuationSubmitError) => (Object.assign(Object.assign({}, state), { valuationSubmitError })),
    resetValuationForm: (state) => (Object.assign(Object.assign({}, state), { valuationFormData: initialState.valuationFormData, valuationFormErrors: {}, valuationSubmitError: null })),
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const investmentStore = createStore({
    name: 'investment',
    initialState,
    actions
});
export const useInvestmentStore = investmentStore.useStore;

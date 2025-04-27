import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
    // List state
    bills: [],
    pendingBills: [],
    loading: false,
    error: null,
    filters: {
        status: '',
        category: '',
        search: ''
    },
    // Detail state
    selectedBill: null,
    billPayments: [],
    billReminders: [],
    // Form state
    formData: {
        name: '',
        description: '',
        amount: null,
        currency: 'USD',
        due_date: new Date().toISOString().split('T')[0],
        reminder_days: 7,
        provider: '',
        account_number: '',
        payment_method: '',
        category: 'electricity',
        is_recurring: false,
        recurrence_period: null
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null,
    // Payment form state
    paymentFormData: {
        payment_date: new Date().toISOString().split('T')[0],
        payment_amount: 0,
        payment_method: '',
        notes: null
    },
    paymentFormErrors: {},
    isPaymentSubmitting: false,
    paymentSubmitError: null,
    // Reminder form state
    reminderFormData: {
        reminder_date: new Date().toISOString().split('T')[0],
        reminder_message: "Don't forget to pay your bill!"
    },
    reminderFormErrors: {},
    isReminderSubmitting: false,
    reminderSubmitError: null
};
// Type-safe actions
const actions = {
    // List actions
    setBills: (state, bills) => (Object.assign(Object.assign({}, state), { bills })),
    setPendingBills: (state, pendingBills) => (Object.assign(Object.assign({}, state), { pendingBills })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    // Detail actions
    setSelectedBill: (state, bill) => (Object.assign(Object.assign({}, state), { selectedBill: bill })),
    setBillPayments: (state, payments) => (Object.assign(Object.assign({}, state), { billPayments: payments })),
    setBillReminders: (state, reminders) => (Object.assign(Object.assign({}, state), { billReminders: reminders })),
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
    initFormForEdit: (state, bill) => (Object.assign(Object.assign({}, state), { formData: {
            name: bill.name,
            description: bill.description,
            amount: bill.amount,
            currency: bill.currency,
            due_date: bill.due_date,
            reminder_days: bill.reminder_days,
            provider: bill.provider,
            account_number: bill.account_number || '',
            payment_method: bill.payment_method || '',
            category: bill.category,
            is_recurring: bill.is_recurring,
            recurrence_period: bill.recurrence_period
        }, formErrors: {}, submitError: null })),
    // Payment form actions
    setPaymentFormData: (state, paymentFormData) => (Object.assign(Object.assign({}, state), { paymentFormData })),
    updatePaymentFormField: (state, { name, value }) => (Object.assign(Object.assign({}, state), { paymentFormData: Object.assign(Object.assign({}, state.paymentFormData), { [name]: value }) })),
    setPaymentFormErrors: (state, paymentFormErrors) => (Object.assign(Object.assign({}, state), { paymentFormErrors })),
    clearPaymentFormError: (state, fieldName) => {
        const newErrors = Object.assign({}, state.paymentFormErrors);
        delete newErrors[fieldName];
        return Object.assign(Object.assign({}, state), { paymentFormErrors: newErrors });
    },
    setIsPaymentSubmitting: (state, isPaymentSubmitting) => (Object.assign(Object.assign({}, state), { isPaymentSubmitting })),
    setPaymentSubmitError: (state, paymentSubmitError) => (Object.assign(Object.assign({}, state), { paymentSubmitError })),
    resetPaymentForm: (state) => (Object.assign(Object.assign({}, state), { paymentFormData: initialState.paymentFormData, paymentFormErrors: {}, paymentSubmitError: null })),
    // Reminder form actions
    setReminderFormData: (state, reminderFormData) => (Object.assign(Object.assign({}, state), { reminderFormData })),
    updateReminderFormField: (state, { name, value }) => (Object.assign(Object.assign({}, state), { reminderFormData: Object.assign(Object.assign({}, state.reminderFormData), { [name]: value }) })),
    setReminderFormErrors: (state, reminderFormErrors) => (Object.assign(Object.assign({}, state), { reminderFormErrors })),
    clearReminderFormError: (state, fieldName) => {
        const newErrors = Object.assign({}, state.reminderFormErrors);
        delete newErrors[fieldName];
        return Object.assign(Object.assign({}, state), { reminderFormErrors: newErrors });
    },
    setIsReminderSubmitting: (state, isReminderSubmitting) => (Object.assign(Object.assign({}, state), { isReminderSubmitting })),
    setReminderSubmitError: (state, reminderSubmitError) => (Object.assign(Object.assign({}, state), { reminderSubmitError })),
    resetReminderForm: (state) => (Object.assign(Object.assign({}, state), { reminderFormData: initialState.reminderFormData, reminderFormErrors: {}, reminderSubmitError: null }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const utilityBillStore = createStore({
    name: 'utilityBill',
    initialState,
    actions
});
export const useUtilityBillStore = utilityBillStore.useStore;

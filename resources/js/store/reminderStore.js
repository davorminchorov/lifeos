import { createStore } from 'xstate-store';
// Initial state
const initialState = {
    // List state
    reminders: [],
    loading: false,
    error: null,
    filters: {
        search: '',
        status: '',
        priority: '',
        date_from: '',
        date_to: '',
        related_type: '',
        related_id: '',
        sort_by: 'due_date',
        sort_order: 'asc',
    },
    meta: {
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
    },
    // Detail state
    selectedReminder: null,
    // Form state
    formData: {
        title: '',
        description: '',
        due_date: new Date().toISOString().split('T')[0],
        status: 'pending',
        priority: 'medium',
        related_type: '',
        related_id: ''
    },
    formErrors: {},
    isSubmitting: false,
    submitError: null
};
// Type-safe actions
const actions = {
    // List actions
    setReminders: (state, reminders) => (Object.assign(Object.assign({}, state), { reminders })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setMeta: (state, meta) => (Object.assign(Object.assign({}, state), { meta })),
    // Detail actions
    setSelectedReminder: (state, reminder) => (Object.assign(Object.assign({}, state), { selectedReminder: reminder })),
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
    initFormForEdit: (state, reminder) => (Object.assign(Object.assign({}, state), { formData: {
            title: reminder.title,
            description: reminder.description || '',
            due_date: reminder.due_date,
            status: reminder.status,
            priority: reminder.priority,
            related_type: reminder.related_type || '',
            related_id: reminder.related_id || ''
        }, formErrors: {}, submitError: null }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const reminderStore = createStore({
    name: 'reminder',
    initialState,
    actions
});
export const useReminderStore = reminderStore.useStore;

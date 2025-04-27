import { createStore } from '../utils/xstate-store-adapter';
import { Reminder, ReminderFormData } from '../queries/reminderQueries';

export interface ReminderState {
  // List state
  reminders: Reminder[];
  loading: boolean;
  error: string | null;
  filters: {
    search: string;
    status: string;
    priority: string;
    date_from: string;
    date_to: string;
    related_type: string;
    related_id: string;
    sort_by: string;
    sort_order: string;
  };
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };

  // Detail state
  selectedReminder: Reminder | null;

  // Form state
  formData: ReminderFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;
}

// Initial state
const initialState: ReminderState = {
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
  setReminders: (state: ReminderState, reminders: Reminder[]) => ({
    ...state,
    reminders
  }),
  setLoading: (state: ReminderState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: ReminderState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: ReminderState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: ReminderState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setMeta: (state: ReminderState, meta: typeof initialState.meta) => ({
    ...state,
    meta
  }),

  // Detail actions
  setSelectedReminder: (state: ReminderState, reminder: Reminder | null) => ({
    ...state,
    selectedReminder: reminder
  }),

  // Form actions
  setFormData: (state: ReminderState, formData: ReminderFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: ReminderState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: ReminderState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: ReminderState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: ReminderState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: ReminderState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: ReminderState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: ReminderState, reminder: Reminder) => ({
    ...state,
    formData: {
      title: reminder.title,
      description: reminder.description || '',
      due_date: reminder.due_date,
      status: reminder.status,
      priority: reminder.priority,
      related_type: reminder.related_type || '',
      related_id: reminder.related_id || ''
    },
    formErrors: {},
    submitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const reminderStore = createStore({
  name: 'reminder',
  initialState,
  actions
});

export const useReminderStore = reminderStore.useStore;

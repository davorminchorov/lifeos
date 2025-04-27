import { createStore } from 'xstate-store';

// Define TypeScript interfaces for our state
export interface Payment {
  id: string;
  amount: number;
  currency: string;
  payment_date: string;
  payment_method: string;
  subscription_name: string;
  category: string;
  notes?: string;
}

export interface PaymentSummary {
  total_spent: number;
  payments_count: number;
  average_payment: number;
  this_month: number;
  previous_month: number;
}

export interface Subscription {
  id: string;
  name: string;
  amount: number;
  currency: string;
}

export interface FormData {
  amount: number;
  payment_date: string;
  notes: string;
}

// Use a slightly different approach for type safety
type ExportStatus = 'idle' | 'loading' | 'success' | 'error';

export interface PaymentState {
  // Payment history state
  payments: Payment[];
  summary: PaymentSummary;
  subscriptions: Subscription[];
  filters: {
    subscription_id: string;
    from_date: string;
    to_date: string;
  };
  loading: boolean;
  error: string | null;
  exportStatus: ExportStatus;

  // Record payment state
  selectedSubscription: Subscription | null;
  formData: FormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;
}

// Initial state
const initialState: PaymentState = {
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
  setPayments: (state: PaymentState, payments: Payment[]) => ({
    ...state,
    payments
  }),
  setSummary: (state: PaymentState, summary: PaymentSummary) => ({
    ...state,
    summary
  }),
  setSubscriptions: (state: PaymentState, subscriptions: Subscription[]) => ({
    ...state,
    subscriptions
  }),
  setFilters: (state: PaymentState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: PaymentState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setLoading: (state: PaymentState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: PaymentState, error: string | null) => ({
    ...state,
    error
  }),
  setExportStatus: (state: PaymentState, exportStatus: ExportStatus) => ({
    ...state,
    exportStatus
  }),

  // Record payment actions
  setSelectedSubscription: (state: PaymentState, selectedSubscription: Subscription | null) => ({
    ...state,
    selectedSubscription
  }),
  setFormData: (state: PaymentState, formData: FormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: PaymentState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: PaymentState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: PaymentState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: PaymentState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: PaymentState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: PaymentState) => ({
    ...state,
    formData: {
      amount: state.selectedSubscription?.amount || 0,
      payment_date: new Date().toISOString().split('T')[0],
      notes: '',
    },
    formErrors: {},
    submitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const paymentStore = createStore({
  name: 'payment',
  initialState,
  actions
});

export const usePaymentStore = paymentStore.useStore;

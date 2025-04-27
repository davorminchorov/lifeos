import { createStore } from 'xstate-store';

// Define TypeScript interfaces for subscription state
export interface Subscription {
  id: string;
  name: string;
  description: string;
  amount: number;
  currency: string;
  billing_cycle: string;
  start_date: string;
  end_date?: string | null;
  status: 'active' | 'cancelled';
  website?: string | null;
  category?: string | null;
  next_payment_date?: string | null;
}

export interface Payment {
  id: string;
  amount: number;
  payment_date: string;
  payment_method: string;
  notes?: string;
}

export interface SubscriptionFormData {
  name: string;
  description: string;
  amount: number;
  currency: string;
  billing_cycle: string;
  start_date: string;
  website?: string;
  category?: string;
}

export interface SubscriptionState {
  // List state
  subscriptions: Subscription[];
  loading: boolean;
  error: string | null;
  filters: {
    status: 'all' | 'active' | 'cancelled';
    category: string;
    sort_by: string;
  };

  // Detail state
  selectedSubscription: Subscription | null;
  subscriptionPayments: Payment[];

  // Form state
  formData: SubscriptionFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;
}

// Initial state
const initialState: SubscriptionState = {
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
  setSubscriptions: (state: SubscriptionState, subscriptions: Subscription[]) => ({
    ...state,
    subscriptions
  }),
  setLoading: (state: SubscriptionState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: SubscriptionState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: SubscriptionState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: SubscriptionState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),

  // Detail actions
  setSelectedSubscription: (state: SubscriptionState, subscription: Subscription | null) => ({
    ...state,
    selectedSubscription: subscription
  }),
  setSubscriptionPayments: (state: SubscriptionState, payments: Payment[]) => ({
    ...state,
    subscriptionPayments: payments
  }),

  // Form actions
  setFormData: (state: SubscriptionState, formData: SubscriptionFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: SubscriptionState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: SubscriptionState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: SubscriptionState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: SubscriptionState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: SubscriptionState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: SubscriptionState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: SubscriptionState, subscription: Subscription) => ({
    ...state,
    formData: {
      name: subscription.name,
      description: subscription.description,
      amount: subscription.amount,
      currency: subscription.currency,
      billing_cycle: subscription.billing_cycle,
      start_date: subscription.start_date,
      website: subscription.website || '',
      category: subscription.category || ''
    },
    formErrors: {},
    submitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const subscriptionStore = createStore({
  name: 'subscription',
  initialState,
  actions
});

export const useSubscriptionStore = subscriptionStore.useStore;

import { createStore } from '../utils/xstate-store-adapter';

// Define TypeScript interfaces for utility bill state
export interface UtilityBill {
  id: string;
  name: string;
  provider: string;
  description: string;
  amount: number | null;
  currency: string;
  due_date: string;
  next_due_date: string;
  category: string;
  account_number: string | null;
  payment_method: string | null;
  status: string;
  reminder_days: number;
  reminder_date: string | null;
  is_recurring: boolean;
  recurrence_period: string | null;
}

export interface Payment {
  id: string;
  payment_date: string;
  payment_amount: number;
  payment_method: string;
  notes: string | null;
}

export interface Reminder {
  id: string;
  reminder_date: string;
  reminder_message: string;
  status: string;
  sent_at: string | null;
}

export interface UtilityBillFormData {
  name: string;
  description: string;
  amount: number | null;
  currency: string;
  due_date: string;
  reminder_days: number;
  provider: string;
  account_number: string;
  payment_method: string;
  category: string;
  is_recurring: boolean;
  recurrence_period: string | null;
}

export interface PaymentFormData {
  payment_date: string;
  payment_amount: number;
  payment_method: string;
  notes: string | null;
}

export interface ReminderFormData {
  reminder_date: string;
  reminder_message: string;
}

export interface UtilityBillState {
  // List state
  bills: UtilityBill[];
  pendingBills: UtilityBill[];
  loading: boolean;
  error: string | null;
  filters: {
    status: string;
    category: string;
    search: string;
  };

  // Detail state
  selectedBill: UtilityBill | null;
  billPayments: Payment[];
  billReminders: Reminder[];

  // Form state
  formData: UtilityBillFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;

  // Payment form state
  paymentFormData: PaymentFormData;
  paymentFormErrors: Record<string, string>;
  isPaymentSubmitting: boolean;
  paymentSubmitError: string | null;

  // Reminder form state
  reminderFormData: ReminderFormData;
  reminderFormErrors: Record<string, string>;
  isReminderSubmitting: boolean;
  reminderSubmitError: string | null;
}

// Initial state
const initialState: UtilityBillState = {
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
  setBills: (state: UtilityBillState, bills: UtilityBill[]) => ({
    ...state,
    bills
  }),
  setPendingBills: (state: UtilityBillState, pendingBills: UtilityBill[]) => ({
    ...state,
    pendingBills
  }),
  setLoading: (state: UtilityBillState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: UtilityBillState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: UtilityBillState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: UtilityBillState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),

  // Detail actions
  setSelectedBill: (state: UtilityBillState, bill: UtilityBill | null) => ({
    ...state,
    selectedBill: bill
  }),
  setBillPayments: (state: UtilityBillState, payments: Payment[]) => ({
    ...state,
    billPayments: payments
  }),
  setBillReminders: (state: UtilityBillState, reminders: Reminder[]) => ({
    ...state,
    billReminders: reminders
  }),

  // Form actions
  setFormData: (state: UtilityBillState, formData: UtilityBillFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: UtilityBillState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: UtilityBillState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: UtilityBillState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: UtilityBillState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: UtilityBillState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: UtilityBillState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: UtilityBillState, bill: UtilityBill) => ({
    ...state,
    formData: {
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
    },
    formErrors: {},
    submitError: null
  }),

  // Payment form actions
  setPaymentFormData: (state: UtilityBillState, paymentFormData: PaymentFormData) => ({
    ...state,
    paymentFormData
  }),
  updatePaymentFormField: (state: UtilityBillState, { name, value }: { name: string; value: any }) => ({
    ...state,
    paymentFormData: { ...state.paymentFormData, [name]: value }
  }),
  setPaymentFormErrors: (state: UtilityBillState, paymentFormErrors: Record<string, string>) => ({
    ...state,
    paymentFormErrors
  }),
  clearPaymentFormError: (state: UtilityBillState, fieldName: string) => {
    const newErrors = { ...state.paymentFormErrors };
    delete newErrors[fieldName];
    return { ...state, paymentFormErrors: newErrors };
  },
  setIsPaymentSubmitting: (state: UtilityBillState, isPaymentSubmitting: boolean) => ({
    ...state,
    isPaymentSubmitting
  }),
  setPaymentSubmitError: (state: UtilityBillState, paymentSubmitError: string | null) => ({
    ...state,
    paymentSubmitError
  }),
  resetPaymentForm: (state: UtilityBillState) => ({
    ...state,
    paymentFormData: initialState.paymentFormData,
    paymentFormErrors: {},
    paymentSubmitError: null
  }),

  // Reminder form actions
  setReminderFormData: (state: UtilityBillState, reminderFormData: ReminderFormData) => ({
    ...state,
    reminderFormData
  }),
  updateReminderFormField: (state: UtilityBillState, { name, value }: { name: string; value: any }) => ({
    ...state,
    reminderFormData: { ...state.reminderFormData, [name]: value }
  }),
  setReminderFormErrors: (state: UtilityBillState, reminderFormErrors: Record<string, string>) => ({
    ...state,
    reminderFormErrors
  }),
  clearReminderFormError: (state: UtilityBillState, fieldName: string) => {
    const newErrors = { ...state.reminderFormErrors };
    delete newErrors[fieldName];
    return { ...state, reminderFormErrors: newErrors };
  },
  setIsReminderSubmitting: (state: UtilityBillState, isReminderSubmitting: boolean) => ({
    ...state,
    isReminderSubmitting
  }),
  setReminderSubmitError: (state: UtilityBillState, reminderSubmitError: string | null) => ({
    ...state,
    reminderSubmitError
  }),
  resetReminderForm: (state: UtilityBillState) => ({
    ...state,
    reminderFormData: initialState.reminderFormData,
    reminderFormErrors: {},
    reminderSubmitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const utilityBillStore = createStore({
  name: 'utilityBill',
  initialState,
  actions
});

export const useUtilityBillStore = utilityBillStore.useStore;

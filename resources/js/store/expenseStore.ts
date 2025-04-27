import { createStore } from 'xstate-store';

// Define TypeScript interfaces for expense state
export interface Expense {
  id: string;
  title: string;
  description: string;
  amount: number;
  currency: string;
  date: string;
  category_id: string | null;
  category_name?: string;
  category_color?: string;
  category?: {
    id: string;
    name: string;
    color: string;
  } | null;
  payment_method: string;
  receipt_url: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: string;
  name: string;
  color: string;
}

export interface ExpenseFormData {
  title: string;
  description: string;
  amount: number;
  currency: string;
  date: string;
  category_id: string;
  payment_method: string;
  notes: string | null;
  receipt_url?: string | null;
}

export interface ExpenseState {
  // List state
  expenses: Expense[];
  categories: Category[];
  loading: boolean;
  error: string | null;
  filters: {
    category_id: string;
    date_from: string;
    date_to: string;
    search: string;
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
  selectedExpense: Expense | null;

  // Form state
  formData: ExpenseFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;

  // Upload state
  uploadedReceipt: File | null;
  isUploading: boolean;
  uploadError: string | null;
}

// Initial state
const initialState: ExpenseState = {
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
  setExpenses: (state: ExpenseState, expenses: Expense[]) => ({
    ...state,
    expenses
  }),
  setCategories: (state: ExpenseState, categories: Category[]) => ({
    ...state,
    categories
  }),
  setLoading: (state: ExpenseState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: ExpenseState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: ExpenseState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: ExpenseState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setMeta: (state: ExpenseState, meta: typeof initialState.meta) => ({
    ...state,
    meta
  }),

  // Detail actions
  setSelectedExpense: (state: ExpenseState, expense: Expense | null) => ({
    ...state,
    selectedExpense: expense
  }),

  // Categorization action
  categorizeExpense: (state: ExpenseState, { expenseId, categoryId, categoryName }: { expenseId: string; categoryId: string; categoryName: string }) => ({
    ...state,
    expenses: state.expenses.map(expense =>
      expense.id === expenseId
        ? { ...expense, category_id: categoryId, category_name: categoryName }
        : expense
    )
  }),

  // Form actions
  setFormData: (state: ExpenseState, formData: ExpenseFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: ExpenseState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: ExpenseState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: ExpenseState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: ExpenseState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: ExpenseState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: ExpenseState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null,
    uploadedReceipt: null,
    uploadError: null
  }),
  initFormForEdit: (state: ExpenseState, expense: Expense) => ({
    ...state,
    formData: {
      title: expense.title,
      description: expense.description,
      amount: expense.amount,
      currency: expense.currency,
      date: expense.date,
      category_id: expense.category_id || '',
      payment_method: expense.payment_method,
      notes: expense.notes,
      receipt_url: expense.receipt_url
    },
    formErrors: {},
    submitError: null
  }),

  // Receipt upload actions
  setUploadedReceipt: (state: ExpenseState, file: File | null) => ({
    ...state,
    uploadedReceipt: file
  }),
  setIsUploading: (state: ExpenseState, isUploading: boolean) => ({
    ...state,
    isUploading
  }),
  setUploadError: (state: ExpenseState, uploadError: string | null) => ({
    ...state,
    uploadError
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const expenseStore = createStore({
  name: 'expense',
  initialState,
  actions
});

export const useExpenseStore = expenseStore.useStore;

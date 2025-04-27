import { createStore } from '../utils/xstate-store-adapter';
import { Budget, BudgetFormData } from '../queries/budgetQueries';

export interface BudgetState {
  // List state
  budgets: Budget[];
  loading: boolean;
  error: string | null;
  filters: {
    search: string;
    category_id: string;
    date_from: string;
    date_to: string;
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
  selectedBudget: Budget | null;

  // Form state
  formData: BudgetFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;
}

// Initial state
const initialState: BudgetState = {
  // List state
  budgets: [],
  loading: false,
  error: null,
  filters: {
    search: '',
    category_id: '',
    date_from: '',
    date_to: '',
    sort_by: 'created_at',
    sort_order: 'desc',
  },
  meta: {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  },

  // Detail state
  selectedBudget: null,

  // Form state
  formData: {
    name: '',
    amount: 0,
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString().split('T')[0],
    category_id: '',
    description: ''
  },
  formErrors: {},
  isSubmitting: false,
  submitError: null
};

// Type-safe actions
const actions = {
  // List actions
  setBudgets: (state: BudgetState, budgets: Budget[]) => ({
    ...state,
    budgets
  }),
  setLoading: (state: BudgetState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: BudgetState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: BudgetState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: BudgetState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setMeta: (state: BudgetState, meta: typeof initialState.meta) => ({
    ...state,
    meta
  }),

  // Detail actions
  setSelectedBudget: (state: BudgetState, budget: Budget | null) => ({
    ...state,
    selectedBudget: budget
  }),

  // Form actions
  setFormData: (state: BudgetState, formData: BudgetFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: BudgetState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: BudgetState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: BudgetState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: BudgetState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: BudgetState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: BudgetState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: BudgetState, budget: Budget) => ({
    ...state,
    formData: {
      name: budget.name,
      amount: budget.amount,
      start_date: budget.start_date,
      end_date: budget.end_date,
      category_id: budget.category_id,
      description: budget.description
    },
    formErrors: {},
    submitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const budgetStore = createStore({
  name: 'budget',
  initialState,
  actions
});

export const useBudgetStore = budgetStore.useStore;

import { createStore } from 'xstate-store';

// Define TypeScript interfaces for investment state
export interface Investment {
  id: string;
  name: string;
  type: string;
  institution: string;
  account_number?: string;
  initial_investment: number;
  current_value: number;
  roi: number;
  start_date: string;
  end_date?: string;
  description?: string;
  notes?: string;
  last_valuation_date: string;
  created_at: string;
  updated_at: string;
  transactions: Transaction[];
  valuations: Valuation[];
}

export interface Transaction {
  id: string;
  investment_id: string;
  type: 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee';
  amount: number;
  date: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface Valuation {
  id: string;
  investment_id: string;
  value: number;
  date: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface InvestmentFormData {
  name: string;
  type: string;
  institution: string;
  account_number?: string;
  initial_investment: number;
  start_date: string;
  end_date?: string;
  description?: string;
  notes?: string;
}

export interface TransactionFormData {
  type: 'deposit' | 'withdrawal' | 'dividend' | 'interest' | 'fee';
  amount: number;
  date: string;
  notes?: string;
}

export interface ValuationFormData {
  value: number;
  date: string;
  notes?: string;
}

export interface InvestmentState {
  // List state
  investments: Investment[];
  loading: boolean;
  error: string | null;
  filters: {
    type: string;
    search: string;
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
  selectedInvestment: Investment | null;
  transactions: Transaction[];
  valuations: Valuation[];

  // Form state
  formData: InvestmentFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;

  // Transaction form state
  transactionFormData: TransactionFormData;
  transactionFormErrors: Record<string, string>;
  isTransactionSubmitting: boolean;
  transactionSubmitError: string | null;

  // Valuation form state
  valuationFormData: ValuationFormData;
  valuationFormErrors: Record<string, string>;
  isValuationSubmitting: boolean;
  valuationSubmitError: string | null;
}

// Initial state
const initialState: InvestmentState = {
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
  setInvestments: (state: InvestmentState, investments: Investment[]) => ({
    ...state,
    investments
  }),
  setLoading: (state: InvestmentState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: InvestmentState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: InvestmentState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: InvestmentState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setMeta: (state: InvestmentState, meta: typeof initialState.meta) => ({
    ...state,
    meta
  }),

  // Detail actions
  setSelectedInvestment: (state: InvestmentState, investment: Investment | null) => ({
    ...state,
    selectedInvestment: investment
  }),
  setTransactions: (state: InvestmentState, transactions: Transaction[]) => ({
    ...state,
    transactions
  }),
  setValuations: (state: InvestmentState, valuations: Valuation[]) => ({
    ...state,
    valuations
  }),

  // Form actions
  setFormData: (state: InvestmentState, formData: InvestmentFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: InvestmentState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: InvestmentState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: InvestmentState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: InvestmentState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: InvestmentState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: InvestmentState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: InvestmentState, investment: Investment) => ({
    ...state,
    formData: {
      name: investment.name,
      type: investment.type,
      institution: investment.institution,
      account_number: investment.account_number,
      initial_investment: investment.initial_investment,
      start_date: investment.start_date,
      end_date: investment.end_date,
      description: investment.description,
      notes: investment.notes
    },
    formErrors: {},
    submitError: null
  }),

  // Transaction form actions
  setTransactionFormData: (state: InvestmentState, transactionFormData: TransactionFormData) => ({
    ...state,
    transactionFormData
  }),
  updateTransactionFormField: (state: InvestmentState, { name, value }: { name: string; value: any }) => ({
    ...state,
    transactionFormData: { ...state.transactionFormData, [name]: value }
  }),
  setTransactionFormErrors: (state: InvestmentState, transactionFormErrors: Record<string, string>) => ({
    ...state,
    transactionFormErrors
  }),
  clearTransactionFormError: (state: InvestmentState, fieldName: string) => {
    const newErrors = { ...state.transactionFormErrors };
    delete newErrors[fieldName];
    return { ...state, transactionFormErrors: newErrors };
  },
  setIsTransactionSubmitting: (state: InvestmentState, isTransactionSubmitting: boolean) => ({
    ...state,
    isTransactionSubmitting
  }),
  setTransactionSubmitError: (state: InvestmentState, transactionSubmitError: string | null) => ({
    ...state,
    transactionSubmitError
  }),
  resetTransactionForm: (state: InvestmentState) => ({
    ...state,
    transactionFormData: initialState.transactionFormData,
    transactionFormErrors: {},
    transactionSubmitError: null
  }),

  // Valuation form actions
  setValuationFormData: (state: InvestmentState, valuationFormData: ValuationFormData) => ({
    ...state,
    valuationFormData
  }),
  updateValuationFormField: (state: InvestmentState, { name, value }: { name: string; value: any }) => ({
    ...state,
    valuationFormData: { ...state.valuationFormData, [name]: value }
  }),
  setValuationFormErrors: (state: InvestmentState, valuationFormErrors: Record<string, string>) => ({
    ...state,
    valuationFormErrors
  }),
  clearValuationFormError: (state: InvestmentState, fieldName: string) => {
    const newErrors = { ...state.valuationFormErrors };
    delete newErrors[fieldName];
    return { ...state, valuationFormErrors: newErrors };
  },
  setIsValuationSubmitting: (state: InvestmentState, isValuationSubmitting: boolean) => ({
    ...state,
    isValuationSubmitting
  }),
  setValuationSubmitError: (state: InvestmentState, valuationSubmitError: string | null) => ({
    ...state,
    valuationSubmitError
  }),
  resetValuationForm: (state: InvestmentState) => ({
    ...state,
    valuationFormData: initialState.valuationFormData,
    valuationFormErrors: {},
    valuationSubmitError: null
  }),
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const investmentStore = createStore({
  name: 'investment',
  initialState,
  actions
});

export const useInvestmentStore = investmentStore.useStore;

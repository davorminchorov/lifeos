import { createStore } from '../utils/xstate-store-adapter';
import { Category, CategoryFormData } from '../queries/categoryQueries';

export interface CategoryState {
  // List state
  categories: Category[];
  loading: boolean;
  error: string | null;
  filters: {
    search: string;
    parent_id: string;
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
  selectedCategory: Category | null;

  // Form state
  formData: CategoryFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;
}

// Initial state
const initialState: CategoryState = {
  // List state
  categories: [],
  loading: false,
  error: null,
  filters: {
    search: '',
    parent_id: '',
    sort_by: 'name',
    sort_order: 'asc',
  },
  meta: {
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
  },

  // Detail state
  selectedCategory: null,

  // Form state
  formData: {
    name: '',
    description: '',
    color: '#5046e5', // default color (primary)
    icon: 'tag',
    parent_id: ''
  },
  formErrors: {},
  isSubmitting: false,
  submitError: null
};

// Type-safe actions
const actions = {
  // List actions
  setCategories: (state: CategoryState, categories: Category[]) => ({
    ...state,
    categories
  }),
  setLoading: (state: CategoryState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: CategoryState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: CategoryState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: CategoryState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setMeta: (state: CategoryState, meta: typeof initialState.meta) => ({
    ...state,
    meta
  }),

  // Detail actions
  setSelectedCategory: (state: CategoryState, category: Category | null) => ({
    ...state,
    selectedCategory: category
  }),

  // Form actions
  setFormData: (state: CategoryState, formData: CategoryFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: CategoryState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: CategoryState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: CategoryState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: CategoryState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: CategoryState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: CategoryState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: CategoryState, category: Category) => ({
    ...state,
    formData: {
      name: category.name,
      description: category.description || '',
      color: category.color || '#5046e5',
      icon: category.icon || 'tag',
      parent_id: category.parent_id || ''
    },
    formErrors: {},
    submitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const categoryStore = createStore({
  name: 'category',
  initialState,
  actions
});

export const useCategoryStore = categoryStore.useStore;

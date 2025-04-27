import { createStore } from '../utils/xstate-store-adapter';

// Define TypeScript interfaces for job application state
export interface JobApplication {
  id: string;
  company: string;
  company_name: string; // Alternative naming
  position: string;
  status: string;
  application_date: string;
  job_description: string | null;
  notes: string | null;
  salary_expectation: number | null;
  salary_offered: number | null;
  salary_range: string | null;
  currency: string;
  location: string | null;
  remote_status: string | null;
  contact_name: string | null;
  contact_person: string | null; // Alternative naming
  contact_email: string | null;
  contact_phone: string | null;
  source: string | null;
  application_url: string | null;
  outcome: any | null;
  interviews: Interview[];
  created_at: string;
  updated_at: string;
}

export interface Interview {
  id: string;
  job_application_id: string;
  date: string;
  time: string;
  interview_type: string;
  location: string | null;
  notes: string | null;
  with_person: string | null;
  status: string;
}

export interface JobApplicationFormData {
  company: string;
  position: string;
  status: string;
  application_date: string;
  job_description: string | null;
  notes: string | null;
  salary_expectation: number | null;
  salary_offered: number | null;
  currency: string;
  location: string | null;
  remote_status: string | null;
  contact_name: string | null;
  contact_email: string | null;
  contact_phone: string | null;
  source: string | null;
}

export interface InterviewFormData {
  date: string;
  time: string;
  interview_type: string;
  location: string | null;
  notes: string | null;
  with_person: string | null;
  status: string;
}

export interface JobApplicationState {
  // List state
  jobApplications: JobApplication[];
  loading: boolean;
  error: string | null;
  filters: {
    status: string;
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
  selectedJobApplication: JobApplication | null;
  interviews: Interview[];

  // Form state
  formData: JobApplicationFormData;
  formErrors: Record<string, string>;
  isSubmitting: boolean;
  submitError: string | null;

  // Interview form state
  interviewFormData: InterviewFormData;
  interviewFormErrors: Record<string, string>;
  isInterviewSubmitting: boolean;
  interviewSubmitError: string | null;
}

// Initial state
const initialState: JobApplicationState = {
  // List state
  jobApplications: [],
  loading: false,
  error: null,
  filters: {
    status: '',
    search: '',
    date_from: '',
    date_to: '',
    sort_by: 'application_date',
    sort_order: 'desc',
  },
  meta: {
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  },

  // Detail state
  selectedJobApplication: null,
  interviews: [],

  // Form state
  formData: {
    company: '',
    position: '',
    status: 'applied',
    application_date: new Date().toISOString().split('T')[0],
    job_description: null,
    notes: null,
    salary_expectation: null,
    salary_offered: null,
    currency: 'USD',
    location: null,
    remote_status: null,
    contact_name: null,
    contact_email: null,
    contact_phone: null,
    source: null
  },
  formErrors: {},
  isSubmitting: false,
  submitError: null,

  // Interview form state
  interviewFormData: {
    date: new Date().toISOString().split('T')[0],
    time: '09:00',
    interview_type: 'phone',
    location: null,
    notes: null,
    with_person: null,
    status: 'scheduled'
  },
  interviewFormErrors: {},
  isInterviewSubmitting: false,
  interviewSubmitError: null
};

// Type-safe actions
const actions = {
  // List actions
  setJobApplications: (state: JobApplicationState, jobApplications: JobApplication[]) => ({
    ...state,
    jobApplications
  }),
  setLoading: (state: JobApplicationState, loading: boolean) => ({
    ...state,
    loading
  }),
  setError: (state: JobApplicationState, error: string | null) => ({
    ...state,
    error
  }),
  setFilters: (state: JobApplicationState, filters: typeof initialState.filters) => ({
    ...state,
    filters
  }),
  updateFilter: (state: JobApplicationState, { name, value }: { name: string; value: string }) => ({
    ...state,
    filters: { ...state.filters, [name]: value }
  }),
  setMeta: (state: JobApplicationState, meta: typeof initialState.meta) => ({
    ...state,
    meta
  }),

  // Detail actions
  setSelectedJobApplication: (state: JobApplicationState, jobApplication: JobApplication | null) => ({
    ...state,
    selectedJobApplication: jobApplication
  }),
  setInterviews: (state: JobApplicationState, interviews: Interview[]) => ({
    ...state,
    interviews
  }),

  // Status update action
  updateJobApplicationStatus: (state: JobApplicationState, { id, status }: { id: string; status: string }) => ({
    ...state,
    jobApplications: state.jobApplications.map(app =>
      app.id === id ? { ...app, status } : app
    ),
    selectedJobApplication: state.selectedJobApplication?.id === id
      ? { ...state.selectedJobApplication, status }
      : state.selectedJobApplication
  }),

  // Form actions
  setFormData: (state: JobApplicationState, formData: JobApplicationFormData) => ({
    ...state,
    formData
  }),
  updateFormField: (state: JobApplicationState, { name, value }: { name: string; value: any }) => ({
    ...state,
    formData: { ...state.formData, [name]: value }
  }),
  setFormErrors: (state: JobApplicationState, formErrors: Record<string, string>) => ({
    ...state,
    formErrors
  }),
  clearFormError: (state: JobApplicationState, fieldName: string) => {
    const newErrors = { ...state.formErrors };
    delete newErrors[fieldName];
    return { ...state, formErrors: newErrors };
  },
  setIsSubmitting: (state: JobApplicationState, isSubmitting: boolean) => ({
    ...state,
    isSubmitting
  }),
  setSubmitError: (state: JobApplicationState, submitError: string | null) => ({
    ...state,
    submitError
  }),
  resetForm: (state: JobApplicationState) => ({
    ...state,
    formData: initialState.formData,
    formErrors: {},
    submitError: null
  }),
  initFormForEdit: (state: JobApplicationState, jobApplication: JobApplication) => ({
    ...state,
    formData: {
      company: jobApplication.company,
      position: jobApplication.position,
      status: jobApplication.status,
      application_date: jobApplication.application_date,
      job_description: jobApplication.job_description,
      notes: jobApplication.notes,
      salary_expectation: jobApplication.salary_expectation,
      salary_offered: jobApplication.salary_offered,
      currency: jobApplication.currency,
      location: jobApplication.location,
      remote_status: jobApplication.remote_status,
      contact_name: jobApplication.contact_name,
      contact_email: jobApplication.contact_email,
      contact_phone: jobApplication.contact_phone,
      source: jobApplication.source
    },
    formErrors: {},
    submitError: null
  }),

  // Interview form actions
  setInterviewFormData: (state: JobApplicationState, interviewFormData: InterviewFormData) => ({
    ...state,
    interviewFormData
  }),
  updateInterviewFormField: (state: JobApplicationState, { name, value }: { name: string; value: any }) => ({
    ...state,
    interviewFormData: { ...state.interviewFormData, [name]: value }
  }),
  setInterviewFormErrors: (state: JobApplicationState, interviewFormErrors: Record<string, string>) => ({
    ...state,
    interviewFormErrors
  }),
  clearInterviewFormError: (state: JobApplicationState, fieldName: string) => {
    const newErrors = { ...state.interviewFormErrors };
    delete newErrors[fieldName];
    return { ...state, interviewFormErrors: newErrors };
  },
  setIsInterviewSubmitting: (state: JobApplicationState, isInterviewSubmitting: boolean) => ({
    ...state,
    isInterviewSubmitting
  }),
  setInterviewSubmitError: (state: JobApplicationState, interviewSubmitError: string | null) => ({
    ...state,
    interviewSubmitError
  }),
  resetInterviewForm: (state: JobApplicationState) => ({
    ...state,
    interviewFormData: initialState.interviewFormData,
    interviewFormErrors: {},
    interviewSubmitError: null
  }),
  initInterviewFormForEdit: (state: JobApplicationState, interview: Interview) => ({
    ...state,
    interviewFormData: {
      date: interview.date,
      time: interview.time,
      interview_type: interview.interview_type,
      location: interview.location,
      notes: interview.notes,
      with_person: interview.with_person,
      status: interview.status
    },
    interviewFormErrors: {},
    interviewSubmitError: null
  })
};

// Create the store
// @ts-ignore - Ignore the type issues with the library
export const jobApplicationStore = createStore({
  name: 'jobApplication',
  initialState,
  actions
});

export const useJobApplicationStore = jobApplicationStore.useStore;

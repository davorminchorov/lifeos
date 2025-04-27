import { createStore } from '../utils/xstate-store-adapter';
// Initial state
const initialState = {
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
    setJobApplications: (state, jobApplications) => (Object.assign(Object.assign({}, state), { jobApplications })),
    setLoading: (state, loading) => (Object.assign(Object.assign({}, state), { loading })),
    setError: (state, error) => (Object.assign(Object.assign({}, state), { error })),
    setFilters: (state, filters) => (Object.assign(Object.assign({}, state), { filters })),
    updateFilter: (state, { name, value }) => (Object.assign(Object.assign({}, state), { filters: Object.assign(Object.assign({}, state.filters), { [name]: value }) })),
    setMeta: (state, meta) => (Object.assign(Object.assign({}, state), { meta })),
    // Detail actions
    setSelectedJobApplication: (state, jobApplication) => (Object.assign(Object.assign({}, state), { selectedJobApplication: jobApplication })),
    setInterviews: (state, interviews) => (Object.assign(Object.assign({}, state), { interviews })),
    // Status update action
    updateJobApplicationStatus: (state, { id, status }) => {
        var _a;
        return (Object.assign(Object.assign({}, state), { jobApplications: state.jobApplications.map(app => app.id === id ? Object.assign(Object.assign({}, app), { status }) : app), selectedJobApplication: ((_a = state.selectedJobApplication) === null || _a === void 0 ? void 0 : _a.id) === id
                ? Object.assign(Object.assign({}, state.selectedJobApplication), { status }) : state.selectedJobApplication }));
    },
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
    initFormForEdit: (state, jobApplication) => (Object.assign(Object.assign({}, state), { formData: {
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
        }, formErrors: {}, submitError: null })),
    // Interview form actions
    setInterviewFormData: (state, interviewFormData) => (Object.assign(Object.assign({}, state), { interviewFormData })),
    updateInterviewFormField: (state, { name, value }) => (Object.assign(Object.assign({}, state), { interviewFormData: Object.assign(Object.assign({}, state.interviewFormData), { [name]: value }) })),
    setInterviewFormErrors: (state, interviewFormErrors) => (Object.assign(Object.assign({}, state), { interviewFormErrors })),
    clearInterviewFormError: (state, fieldName) => {
        const newErrors = Object.assign({}, state.interviewFormErrors);
        delete newErrors[fieldName];
        return Object.assign(Object.assign({}, state), { interviewFormErrors: newErrors });
    },
    setIsInterviewSubmitting: (state, isInterviewSubmitting) => (Object.assign(Object.assign({}, state), { isInterviewSubmitting })),
    setInterviewSubmitError: (state, interviewSubmitError) => (Object.assign(Object.assign({}, state), { interviewSubmitError })),
    resetInterviewForm: (state) => (Object.assign(Object.assign({}, state), { interviewFormData: initialState.interviewFormData, interviewFormErrors: {}, interviewSubmitError: null })),
    initInterviewFormForEdit: (state, interview) => (Object.assign(Object.assign({}, state), { interviewFormData: {
            date: interview.date,
            time: interview.time,
            interview_type: interview.interview_type,
            location: interview.location,
            notes: interview.notes,
            with_person: interview.with_person,
            status: interview.status
        }, interviewFormErrors: {}, interviewSubmitError: null }))
};
// Create the store
// @ts-ignore - Ignore the type issues with the library
export const jobApplicationStore = createStore({
    name: 'jobApplication',
    initialState,
    actions
});
export const useJobApplicationStore = jobApplicationStore.useStore;

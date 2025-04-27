export interface JobApplication {
  id: string;
  user_id: number;
  company_name: string;
  position: string;
  application_date: string;
  job_description?: string;
  application_url?: string;
  salary_range?: string;
  contact_person?: string;
  contact_email?: string;
  status: 'applied' | 'interviewing' | 'offered' | 'rejected' | 'withdrawn';
  notes?: string;
  interviews: Interview[];
  outcome: Outcome | null;
  created_at: string;
  updated_at: string;
}

export interface Interview {
  id: string;
  application_id: string;
  interview_date: string;
  interview_time: string;
  interview_type: 'phone' | 'video' | 'in-person';
  with_person: string;
  location?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface Outcome {
  application_id: string;
  outcome: 'offered' | 'rejected' | 'withdrawn';
  outcome_date: string;
  salary_offered?: string;
  feedback?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface JobApplicationFormData {
  company_name: string;
  position: string;
  application_date: string;
  job_description?: string;
  application_url?: string;
  salary_range?: string;
  contact_person?: string;
  contact_email?: string;
  status: 'applied' | 'interviewing' | 'offered' | 'rejected' | 'withdrawn';
  notes?: string;
}

export interface InterviewFormData {
  interview_date: string;
  interview_time: string;
  interview_type: 'phone' | 'video' | 'in-person';
  with_person: string;
  location?: string;
  notes?: string;
}

export interface OutcomeFormData {
  outcome: 'offered' | 'rejected' | 'withdrawn';
  outcome_date: string;
  salary_offered?: string;
  feedback?: string;
  notes?: string;
}

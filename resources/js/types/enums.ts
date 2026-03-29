// PHP Enum mirrors — auto-generated from app/Enums/*.php

export const MealType = {
    BREAKFAST: 'breakfast',
    LUNCH: 'lunch',
    DINNER: 'dinner',
    SNACK: 'snack',
    OTHER: 'other',
} as const
export type MealType = (typeof MealType)[keyof typeof MealType]

export const InterviewOutcome = {
    PENDING: 'pending',
    POSITIVE: 'positive',
    NEUTRAL: 'neutral',
    NEGATIVE: 'negative',
} as const
export type InterviewOutcome = (typeof InterviewOutcome)[keyof typeof InterviewOutcome]

export const TaxBehavior = {
    INCLUSIVE: 'inclusive',
    EXCLUSIVE: 'exclusive',
} as const
export type TaxBehavior = (typeof TaxBehavior)[keyof typeof TaxBehavior]

export const CreditNoteStatus = {
    DRAFT: 'draft',
    ISSUED: 'issued',
    APPLIED: 'applied',
    VOID: 'void',
} as const
export type CreditNoteStatus = (typeof CreditNoteStatus)[keyof typeof CreditNoteStatus]

export const InterviewType = {
    PHONE: 'phone',
    VIDEO: 'video',
    ONSITE: 'onsite',
    PANEL: 'panel',
    TECHNICAL: 'technical',
    BEHAVIORAL: 'behavioral',
    FINAL: 'final',
} as const
export type InterviewType = (typeof InterviewType)[keyof typeof InterviewType]

export const RecurringStatus = {
    ACTIVE: 'active',
    PAUSED: 'paused',
    CANCELLED: 'cancelled',
    COMPLETED: 'completed',
} as const
export type RecurringStatus = (typeof RecurringStatus)[keyof typeof RecurringStatus]

export const DiscountType = {
    PERCENT: 'percent',
    FIXED: 'fixed',
} as const
export type DiscountType = (typeof DiscountType)[keyof typeof DiscountType]

export const ApplicationSource = {
    LINKEDIN: 'linkedin',
    COMPANY_WEBSITE: 'company_website',
    JOB_BOARD: 'job_board',
    REFERRAL: 'referral',
    RECRUITER: 'recruiter',
    NETWORKING: 'networking',
    OTHER: 'other',
} as const
export type ApplicationSource = (typeof ApplicationSource)[keyof typeof ApplicationSource]

export const BillingInterval = {
    DAILY: 'daily',
    WEEKLY: 'weekly',
    MONTHLY: 'monthly',
    QUARTERLY: 'quarterly',
    YEARLY: 'yearly',
} as const
export type BillingInterval = (typeof BillingInterval)[keyof typeof BillingInterval]

export const InvoiceStatus = {
    DRAFT: 'draft',
    ISSUED: 'issued',
    PAID: 'paid',
    PARTIALLY_PAID: 'partially_paid',
    PAST_DUE: 'past_due',
    VOID: 'void',
    WRITTEN_OFF: 'written_off',
    ARCHIVED: 'archived',
} as const
export type InvoiceStatus = (typeof InvoiceStatus)[keyof typeof InvoiceStatus]

export const ApplicationStatus = {
    WISHLIST: 'wishlist',
    APPLIED: 'applied',
    SCREENING: 'screening',
    INTERVIEW: 'interview',
    ASSESSMENT: 'assessment',
    OFFER: 'offer',
    ACCEPTED: 'accepted',
    REJECTED: 'rejected',
    WITHDRAWN: 'withdrawn',
    ARCHIVED: 'archived',
} as const
export type ApplicationStatus = (typeof ApplicationStatus)[keyof typeof ApplicationStatus]

export const OfferStatus = {
    PENDING: 'pending',
    NEGOTIATING: 'negotiating',
    ACCEPTED: 'accepted',
    DECLINED: 'declined',
    EXPIRED: 'expired',
} as const
export type OfferStatus = (typeof OfferStatus)[keyof typeof OfferStatus]

export const PaymentStatus = {
    PENDING: 'pending',
    SUCCEEDED: 'succeeded',
    FAILED: 'failed',
    REFUNDED: 'refunded',
    PARTIALLY_REFUNDED: 'partially_refunded',
} as const
export type PaymentStatus = (typeof PaymentStatus)[keyof typeof PaymentStatus]

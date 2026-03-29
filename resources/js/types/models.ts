import type {
    ApplicationSource,
    ApplicationStatus,
    BillingInterval,
    CreditNoteStatus,
    DiscountType,
    InterviewOutcome,
    InterviewType,
    InvoiceStatus,
    MealType,
    OfferStatus,
    PaymentStatus,
    RecurringStatus,
    TaxBehavior,
} from './enums'

export interface User {
    id: number
    name: string
    email: string
    email_verified_at: string | null
    current_tenant_id: number | null
    created_at: string
    updated_at: string
    // Relationships
    current_tenant?: Tenant
    tenants?: Tenant[]
    subscriptions?: Subscription[]
    contracts?: Contract[]
    expenses?: Expense[]
    notification_preferences?: UserNotificationPreference[]
    project_investments?: ProjectInvestment[]
}

export interface Tenant {
    id: number
    name: string
    slug: string
    default_currency: string | null
    default_country: string | null
    owner_id: number
    created_at: string
    updated_at: string
    // Relationships
    owner?: User
    members?: User[]
}

export interface TenantMember {
    id: number
    tenant_id: number
    user_id: number
    role: string
    created_at: string
    updated_at: string
    // Relationships
    tenant?: Tenant
    user?: User
}

export interface Subscription {
    id: number
    tenant_id: number
    user_id: number
    service_name: string
    description: string | null
    category: string | null
    cost: number
    billing_cycle: string
    billing_cycle_days: number | null
    currency: string | null
    start_date: string
    next_billing_date: string | null
    cancellation_date: string | null
    payment_method: string | null
    merchant_info: string | null
    auto_renewal: boolean
    cancellation_difficulty: number | null
    price_history: unknown[] | null
    notes: string | null
    tags: string[] | null
    status: string
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface Budget {
    id: number
    tenant_id: number
    user_id: number
    category: string
    budget_period: string
    amount: number
    currency: string | null
    start_date: string
    end_date: string
    is_active: boolean
    rollover_unused: boolean
    alert_threshold: number
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface Expense {
    id: number
    tenant_id: number
    user_id: number
    amount: number
    currency: string | null
    category: string
    subcategory: string | null
    expense_date: string
    description: string | null
    merchant: string | null
    payment_method: string | null
    receipt_attachments: unknown[] | null
    tags: string[] | null
    location: string | null
    is_tax_deductible: boolean
    expense_type: string | null
    is_recurring: boolean
    recurring_schedule: string | null
    budget_allocated: number | null
    notes: string | null
    status: string | null
    unique_key: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface Investment {
    id: number
    tenant_id: number
    user_id: number
    investment_type: string
    symbol_identifier: string | null
    name: string
    quantity: number
    purchase_date: string
    purchase_price: number
    currency: string | null
    current_value: number | null
    total_dividends_received: number
    total_fees_paid: number
    investment_goals: unknown[] | null
    risk_tolerance: string | null
    account_broker: string | null
    account_number: string | null
    transaction_history: unknown[] | null
    tax_lots: unknown[] | null
    target_allocation_percentage: number | null
    last_price_update: string | null
    notes: string | null
    status: string
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    dividends?: InvestmentDividend[]
    transactions?: InvestmentTransaction[]
}

export interface InvestmentDividend {
    id: number
    tenant_id: number
    investment_id: number
    amount: number
    record_date: string | null
    payment_date: string | null
    ex_dividend_date: string | null
    dividend_type: string | null
    frequency: string | null
    dividend_per_share: number | null
    shares_held: number | null
    tax_withheld: number | null
    currency: string | null
    reinvested: boolean
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    investment?: Investment
}

export interface InvestmentTransaction {
    id: number
    tenant_id: number
    investment_id: number
    transaction_type: string
    quantity: number
    price_per_share: number
    total_amount: number
    fees: number
    taxes: number
    transaction_date: string
    settlement_date: string | null
    order_id: string | null
    confirmation_number: string | null
    account_number: string | null
    broker: string | null
    currency: string | null
    exchange_rate: number | null
    order_type: string | null
    limit_price: number | null
    stop_price: number | null
    notes: string | null
    tax_lot_info: unknown[] | null
    created_at: string
    updated_at: string
    // Relationships
    investment?: Investment
}

export interface InvestmentGoal {
    id: number
    tenant_id: number
    user_id: number
    title: string
    description: string | null
    target_amount: number
    current_progress: number
    target_date: string | null
    status: string
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface JobApplication {
    id: number
    tenant_id: number
    user_id: number
    company_name: string
    company_website: string | null
    job_title: string
    job_description: string | null
    job_url: string | null
    location: string | null
    remote: boolean
    salary_min: number | null
    salary_max: number | null
    currency: string | null
    status: ApplicationStatus
    source: ApplicationSource | null
    applied_at: string | null
    next_action_at: string | null
    priority: number | null
    contact_name: string | null
    contact_email: string | null
    contact_phone: string | null
    notes: string | null
    tags: string[] | null
    file_attachments: unknown[] | null
    archived_at: string | null
    deleted_at: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    status_histories?: JobApplicationStatusHistory[]
    interviews?: JobApplicationInterview[]
    offer?: JobApplicationOffer
}

export interface JobApplicationInterview {
    id: number
    tenant_id: number
    user_id: number
    job_application_id: number
    type: InterviewType
    scheduled_at: string
    duration_minutes: number | null
    location: string | null
    video_link: string | null
    interviewer_name: string | null
    notes: string | null
    feedback: string | null
    outcome: InterviewOutcome | null
    completed: boolean
    deleted_at: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    job_application?: JobApplication
}

export interface JobApplicationOffer {
    id: number
    tenant_id: number
    user_id: number
    job_application_id: number
    base_salary: number
    bonus: number | null
    equity: string | null
    currency: string | null
    benefits: string | null
    start_date: string | null
    decision_deadline: string | null
    status: OfferStatus
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    job_application?: JobApplication
}

export interface JobApplicationStatusHistory {
    id: number
    tenant_id: number
    user_id: number
    job_application_id: number
    from_status: ApplicationStatus | null
    to_status: ApplicationStatus
    changed_at: string
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    job_application?: JobApplication
}

export interface Invoice {
    id: number
    tenant_id: number
    user_id: number
    customer_id: number
    number: string | null
    sequence_year: number | null
    sequence_no: number | null
    hash: string | null
    status: InvoiceStatus
    currency: string
    subtotal: number
    discount_total: number
    tax_total: number
    total: number
    amount_due: number
    amount_paid: number
    tax_behavior: TaxBehavior
    issued_at: string | null
    due_at: string | null
    paid_at: string | null
    voided_at: string | null
    last_sent_at: string | null
    net_terms_days: number
    notes: string | null
    internal_notes: string | null
    pdf_path: string | null
    subscription_id: number | null
    metadata: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    customer?: Customer
    items?: InvoiceItem[]
    payments?: Payment[]
    credit_notes?: CreditNote[]
    recurring_invoice?: RecurringInvoice
    reminders?: InvoiceReminder[]
}

export interface InvoiceItem {
    id: number
    tenant_id: number
    invoice_id: number
    name: string
    description: string | null
    quantity: number
    unit_amount: number
    currency: string
    tax_rate_id: number | null
    tax_amount: number
    discount_id: number | null
    discount_amount: number
    amount: number
    total_amount: number
    metadata: Record<string, unknown> | null
    sort_order: number
    created_at: string
    updated_at: string
    // Relationships
    invoice?: Invoice
    tax_rate?: TaxRate
    discount?: Discount
}

export interface InvoiceReminder {
    id: number
    tenant_id: number
    user_id: number
    invoice_id: number
    days_after_due: number
    reminder_type: string | null
    message: string | null
    sent_at: string | null
    email_sent: boolean
    email_error: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    invoice?: Invoice
}

export interface Customer {
    id: number
    tenant_id: number
    user_id: number
    name: string
    email: string | null
    phone: string | null
    company_name: string | null
    billing_address: Record<string, unknown> | null
    tax_id: string | null
    tax_country: string | null
    currency: string
    default_payment_method_id: number | null
    metadata: Record<string, unknown> | null
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    invoices?: Invoice[]
    credit_notes?: CreditNote[]
}

export interface TaxRate {
    id: number
    tenant_id: number
    user_id: number
    name: string
    code: string | null
    percentage_basis_points: number
    country: string | null
    region: string | null
    inclusive: boolean
    active: boolean
    valid_from: string | null
    valid_to: string | null
    description: string | null
    metadata: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    invoice_items?: InvoiceItem[]
}

export interface Discount {
    id: number
    tenant_id: number
    user_id: number
    code: string
    name: string
    description: string | null
    type: DiscountType
    value: number
    currency: string | null
    starts_at: string | null
    ends_at: string | null
    active: boolean
    max_redemptions: number | null
    current_redemptions: number
    max_redemptions_per_customer: number | null
    minimum_amount: number | null
    metadata: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    invoice_items?: InvoiceItem[]
}

export interface Contract {
    id: number
    tenant_id: number
    user_id: number
    contract_type: string | null
    title: string
    counterparty: string | null
    start_date: string | null
    end_date: string | null
    notice_period_days: number | null
    auto_renewal: boolean
    contract_value: number | null
    payment_terms: string | null
    key_obligations: string | null
    penalties: string | null
    termination_clauses: string | null
    document_attachments: unknown[] | null
    performance_rating: number | null
    renewal_history: unknown[] | null
    amendments: unknown[] | null
    notes: string | null
    status: string
    deleted_at: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface Warranty {
    id: number
    tenant_id: number
    user_id: number
    product_name: string
    brand: string | null
    model: string | null
    serial_number: string | null
    purchase_date: string | null
    purchase_price: number | null
    retailer: string | null
    warranty_duration_months: number | null
    warranty_type: string | null
    warranty_terms: string | null
    warranty_expiration_date: string | null
    claim_history: unknown[] | null
    receipt_attachments: unknown[] | null
    proof_of_purchase_attachments: unknown[] | null
    current_status: string
    transfer_history: unknown[] | null
    maintenance_reminders: unknown[] | null
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface Iou {
    id: number
    tenant_id: number
    user_id: number
    type: string
    person_name: string
    amount: number
    currency: string | null
    transaction_date: string | null
    due_date: string | null
    description: string | null
    notes: string | null
    status: string
    amount_paid: number | null
    payment_method: string | null
    category: string | null
    attachments: unknown[] | null
    is_recurring: boolean
    recurring_schedule: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface CycleMenu {
    id: number
    tenant_id: number
    user_id: number
    name: string
    starts_on: string
    cycle_length_days: number
    is_active: boolean
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    days?: CycleMenuDay[]
}

export interface CycleMenuDay {
    id: number
    tenant_id: number
    cycle_menu_id: number
    day_index: number
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    menu?: CycleMenu
    items?: CycleMenuItem[]
}

export interface CycleMenuItem {
    id: number
    tenant_id: number
    cycle_menu_day_id: number
    title: string
    meal_type: MealType
    time_of_day: string | null
    quantity: string | null
    recipe_id: number | null
    position: number
    created_at: string
    updated_at: string
    // Relationships
    day?: CycleMenuDay
}

export interface ProjectInvestment {
    id: number
    tenant_id: number
    user_id: number
    name: string
    project_type: string | null
    stage: string | null
    business_model: string | null
    website_url: string | null
    repository_url: string | null
    equity_percentage: number | null
    current_value: number | null
    start_date: string | null
    end_date: string | null
    status: string
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    transactions?: ProjectInvestmentTransaction[]
}

export interface ProjectInvestmentTransaction {
    id: number
    project_investment_id: number
    user_id: number
    amount: number
    currency: string | null
    transaction_date: string
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    project_investment?: ProjectInvestment
    user?: User
}

export interface UtilityBill {
    id: number
    tenant_id: number
    user_id: number
    utility_type: string
    service_provider: string
    account_number: string | null
    service_address: string | null
    bill_amount: number
    currency: string | null
    usage_amount: number | null
    usage_unit: string | null
    rate_per_unit: number | null
    bill_period_start: string
    bill_period_end: string
    due_date: string
    payment_status: string
    payment_date: string | null
    meter_readings: unknown[] | null
    bill_attachments: unknown[] | null
    service_plan: string | null
    contract_terms: string | null
    auto_pay_enabled: boolean
    usage_history: unknown[] | null
    budget_alert_threshold: number | null
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface CreditNote {
    id: number
    tenant_id: number
    user_id: number
    customer_id: number
    invoice_id: number | null
    number: string
    status: CreditNoteStatus
    currency: string
    subtotal: number
    tax_total: number
    total: number
    amount_remaining: number
    reason: string | null
    reason_notes: string | null
    issued_at: string | null
    pdf_path: string | null
    metadata: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    customer?: Customer
    invoice?: Invoice
    applications?: CreditNoteApplication[]
}

export interface CreditNoteApplication {
    id: number
    tenant_id: number
    credit_note_id: number
    invoice_id: number
    amount_applied: number
    applied_at: string | null
    created_at: string
    updated_at: string
    // Relationships
    credit_note?: CreditNote
    invoice?: Invoice
}

export interface Payment {
    id: number
    tenant_id: number
    user_id: number
    invoice_id: number
    provider: string
    provider_payment_id: string | null
    amount: number
    currency: string
    status: PaymentStatus
    attempted_at: string | null
    succeeded_at: string | null
    failed_at: string | null
    failure_code: string | null
    failure_message: string | null
    payment_method: string | null
    payment_method_details: Record<string, unknown> | null
    payment_date: string | null
    reference: string | null
    metadata: Record<string, unknown> | null
    notes: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    invoice?: Invoice
    refunds?: Refund[]
}

export interface Refund {
    id: number
    payment_id: number
    amount: number
    currency: string
    provider: string | null
    provider_refund_id: string | null
    status: string
    reason: string | null
    reason_notes: string | null
    processed_at: string | null
    metadata: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    payment?: Payment
}

export interface RecurringInvoice {
    id: number
    tenant_id: number
    user_id: number
    customer_id: number
    name: string
    description: string | null
    billing_interval: BillingInterval
    interval_count: number
    status: RecurringStatus
    currency: string
    tax_behavior: TaxBehavior
    net_terms_days: number
    start_date: string
    end_date: string | null
    next_billing_date: string | null
    billing_day_of_month: number | null
    occurrences_limit: number | null
    occurrences_count: number
    auto_send_email: boolean
    days_before_due: number | null
    notes: string | null
    metadata: Record<string, unknown> | null
    last_generated_at: string | null
    paused_at: string | null
    cancelled_at: string | null
    completed_at: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    customer?: Customer
    items?: RecurringInvoiceItem[]
    generated_invoices?: Invoice[]
}

export interface RecurringInvoiceItem {
    id: number
    tenant_id: number
    user_id: number
    recurring_invoice_id: number
    description: string
    quantity: number
    unit_amount: number
    tax_rate_id: number | null
    discount_id: number | null
    sort_order: number
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    recurring_invoice?: RecurringInvoice
    tax_rate?: TaxRate
    discount?: Discount
}

export interface Holiday {
    id: number
    tenant_id: number
    country: string
    date: string
    name: string
    description: string | null
    created_at: string
    updated_at: string
}

export interface Sequence {
    id: number
    tenant_id: number
    user_id: number
    scope: string
    year: number
    current_value: number
    prefix: string | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface GmailConnection {
    id: number
    tenant_id: number
    user_id: number
    email_address: string
    token_expires_at: string | null
    last_synced_at: string | null
    sync_enabled: boolean
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

export interface ProcessedEmail {
    id: number
    tenant_id: number
    user_id: number
    gmail_message_id: string
    expense_id: number | null
    processed_at: string | null
    processing_status: string
    failure_reason: string | null
    email_data: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
    expense?: Expense
}

export interface UserNotificationPreference {
    id: number
    user_id: number
    notification_type: string
    email_enabled: boolean
    database_enabled: boolean
    push_enabled: boolean
    settings: Record<string, unknown> | null
    created_at: string
    updated_at: string
    // Relationships
    user?: User
}

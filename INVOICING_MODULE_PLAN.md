# Invoicing Module Plan

Last updated: 2025-10-09

## 1) Goals and Non-Goals
- Goals
  - Provide a robust, extensible invoicing system to bill customers for one‑time and recurring items.
  - Support multi-currency, basic tax rules (VAT/GST/sales tax), discounts, credit notes, and refunds.
  - Generate compliant invoice numbers, PDFs, and email delivery with automated reminders and dunning.
  - Expose a clean API (internal first) and an admin/operator UI. Ensure auditability and permissions.
- Non-Goals (initial phase)
  - Complex tax engines (OSS/IOSS, place-of-supply), multi-entity consolidation, or revenue recognition.
  - Advanced proration across mid-cycle changes (only basic proration for now).

## 2) Core Concepts & Domain Model
- Customer (or Account)
  - Holds billing profile: legal name, address, tax ID (VAT/GST), currency preference, payment methods.
- Invoice
  - Immutable financial document once finalized; supports draft → issued → paid/partially_paid/void → refunded.
  - Contains line items, totals, taxes, discounts, adjustments; links to payments and credit notes.
- InvoiceItem
  - Describes quantity, unit price, currency, tax category, discount allocation, and metadata.
- Payment
  - Records captured funds against an invoice via gateway (e.g., Stripe) or manual methods (bank/wire).
- CreditNote
  - Negative invoice linked to a prior invoice to reverse charges (partial or full).
- Refund
  - Refunds a payment (full or partial), may create or reference a credit note depending on workflow.
- TaxRate
  - Defines percentage or fixed tax per jurisdiction; effective periods; inclusive/exclusive behavior.
- Discount
  - Coupon or promotion; fixed amount or percentage; per-item or per-invoice; time-bounded.
- Subscription (future integration)
  - Creates recurring invoice schedules for plans/add‑ons; handles basic proration on upgrade/downgrade.

## 3) Lifecycle & State Machine
- Invoice statuses
  - draft → issued → (paid | partially_paid | past_due) → (void | written_off) → archived
- Payment statuses
  - pending → succeeded → failed → refunded/partially_refunded
- Credit note statuses
  - draft → issued → applied
- Transitions
  - Draft can be edited; issuing locks monetary fields (except limited corrections via void + reissue).
  - Past due triggers reminder sequence and dunning logic; write-off allowed by role with reason.

## 4) Numbering & Identity
- Format: PREFIX-YYYY-SEQ (e.g., INV-2025-000123). PREFIX configurable per environment/company.
- Gap‑tolerant: reserve number on issuing; do not reuse numbers. Maintain a separate sequence per entity.
- Store a short hash to prevent spoofing (display but not considered legal id).

## 5) Currency, Rounding, and Precision
- Store all money in integer minor units (cents); display via Money/Currency helpers.
- Currency per invoice (not per line mixed). Conversion snapshot at invoice time if needed.
- Rounding: bank/half‑even for unit price and taxes; totals computed as sum of rounded lines.

## 6) Taxes
- Support tax exclusive (price + tax) and inclusive (tax baked in) per item/tax rate.
- Per-line tax breakdown with category codes; total tax summary on invoice.
- Validate customer tax ID format when present; allow reverse charge flag (zero-rated with note).

## 7) Discounts, Credits, and Adjustments
- Discounts
  - Percentage or fixed; line-level or invoice-level. Stored explicitly and referenced by source (coupon).
- Credits
  - Apply available credit notes to reduce amount_due. Track remaining credit balance.
- Adjustments
  - Manual positive/negative adjustments with reason codes; audit logged.

## 8) Payments & Dunning
- Gateways: abstracted PaymentProvider (Stripe first, extensible interface), plus manual payments.
- Dunning & reminders (configurable):
  - T+0 (issue): invoice email
  - T+7: reminder 1
  - T+14: reminder 2
  - T+21: final notice, optional automatic suspension webhook/event
- Partial payments allowed; amount_due updated; status moves to partially_paid until fully settled.

## 9) PDFs & Email Delivery
- PDF rendering service with branded template, legal address, tax breakdown, payment instructions, QR code.
- Email templates: issue, reminder, paid receipt, credit note issued, refund processed.
- Store rendered PDF (binary) and a signed JSON snapshot of totals/items for traceability.

## 10) Permissions, Roles, and Audit
- Roles: Admin, Finance, Support, Viewer.
- Gate/Policy examples:
  - View/Issue/Void/WriteOff/Refund/ApplyCredit per company/tenant.
- Audit log entries for lifecycle events, monetary changes, numbering, and delivery attempts.

## 11) Data Model (initial draft)
- customers
  - id, name, email, billing_address_json, tax_id, tax_country, currency, default_payment_method_id, meta
- invoices
  - id, customer_id, number, sequence_year, sequence_no, status, currency, subtotal, discount_total, tax_total,
    total, amount_due, amount_paid, issued_at, due_at, paid_at, notes, tax_behavior (inclusive|exclusive), meta
- invoice_items
  - id, invoice_id, name, description, quantity, unit_amount, currency, tax_rate_id (nullable), discount_id (nullable),
    amount, tax_amount, total_amount, meta
- payments
  - id, invoice_id, provider, provider_payment_id, amount, currency, status, attempted_at, succeeded_at, failed_at,
    failure_code, failure_message, meta
- credit_notes
  - id, invoice_id (original), number, status, currency, subtotal, tax_total, total, reason, issued_at, meta
- credit_note_applications
  - id, credit_note_id, invoice_id, amount_applied, applied_at
- refunds
  - id, payment_id, amount, currency, provider_refund_id, status, created_at, processed_at, reason, meta
- tax_rates
  - id, name, percentage_basis_points, country, region, inclusive, valid_from, valid_to, meta
- discounts
  - id, code, type (percent|fixed), value, starts_at, ends_at, max_redemptions, meta
- sequences
  - id, scope ("invoice"|"credit_note"), year, current_value

Notes:
- All monetary fields are integers in minor units.
- Use JSON columns for flexible addresses/metadata where suitable.

## 12) Application Architecture (Laravel 12)
- Models: Customer, Invoice, InvoiceItem, Payment, CreditNote, CreditNoteApplication, Refund, TaxRate, Discount, Sequence
- Enums: InvoiceStatus, PaymentStatus, TaxBehavior, DiscountType, CreditNoteStatus
- Services
  - InvoicingService: create draft, add items, compute totals, issue, void, write-off
  - NumberingService: reserve numbers (atomic, per year)
  - TaxService: compute line and total taxes
  - DiscountService: apply coupons and pro‑rate
  - PaymentProvider interface + StripeProvider implementation
  - PdfRenderer (using Dompdf/Snappy or existing PDF stack)
  - EmailDispatcher for notifications
- Jobs/Listeners
  - SendInvoiceIssuedEmail, SendReminderEmail, GeneratePdf, SyncPaymentStatus, ApplyDunningActions
- Policies & Gates for authorization

## 13) APIs and Routes
- Admin API (v1)
  - GET /api/v1/invoices
  - POST /api/v1/invoices (create draft)
  - POST /api/v1/invoices/{id}/items
  - POST /api/v1/invoices/{id}/issue
  - POST /api/v1/invoices/{id}/void
  - POST /api/v1/invoices/{id}/write-off
  - POST /api/v1/invoices/{id}/apply-credit
  - POST /api/v1/invoices/{id}/pay (manual capture)
  - POST /api/v1/payments/{id}/refund
  - GET /api/v1/invoices/{id}/pdf
- Eloquent API Resources for Invoice, Payment, CreditNote with includes for items and customer.
- Webhooks
  - /webhooks/payments/stripe (events: payment_intent.succeeded, payment_intent.payment_failed, charge.refunded)

## 14) UI (Operator/Admin)
- Invoices index: filters (status, customer, past due), quick metrics.
- Invoice detail: header (status, number, amounts), timeline (audit), items, payments, credits, PDF preview/download.
- Issue/Void/Write-off actions with confirmations; Refund on payments.
- Customer profile: billing info, tax ID, default currency, open balance, history.

## 15) Validation & Business Rules
- Cannot issue with zero total unless marked pro‑forma or $0 policy enabled.
- Due date default: issue date + configured net terms (e.g., Net 14); override allowed.
- Prevent currency changes after issue.
- Prevent item edits after issue; require credit note for corrections.
- Enforce idempotency keys for external provider calls.

## 16) Events
- InvoiceIssued, InvoicePaid, InvoicePartiallyPaid, InvoicePastDue, InvoiceVoided, InvoiceWrittenOff,
  CreditNoteIssued, PaymentSucceeded, PaymentFailed, PaymentRefunded

## 17) Configuration
- Config file: invoicing.php
  - prefix: INV
  - credit_note_prefix: CN
  - net_terms_days: 14
  - reminder_days: [0, 7, 14, 21]
  - default_currency: USD
  - provider: stripe (or null for manual)

## 18) Observability
- Structured logs around invoice lifecycle and payment interactions.
- Metrics: issued count, DSO, past_due rate, collection rate, refund rate, reminder effectiveness.

## 19) Security & Compliance
- PII protection for billing data; encrypt sensitive fields (tax IDs) at rest.
- Role-based access; audit trails.
- Legal notes section on PDFs: tax and reverse charge statements where applicable.

## 20) Testing Strategy
- Feature tests for lifecycle (draft → issue → pay → refund), reminders, and dunning transitions.
- Unit tests for tax, discount, numbering, and money calculations (edge cases, rounding).
- Integration tests for payment provider webhooks.
- Snapshot tests for PDF rendering output (hash/size checks rather than pixel-perfect).

## 21) Rollout Plan
- Phase 1: Manual invoices + manual payments, PDFs, email delivery, basic taxes/discounts.
- Phase 2: Stripe payments + webhooks, partial payments, refunds, credit notes.
- Phase 3: Subscription integration (auto invoice schedules) and advanced reporting.

## 22) Open Questions
- Do we need multi‑tenant separation now or later? If now, add tenant_id to all billing tables.
- Which PDF engine aligns with deployment constraints?
- Required tax notes for specific jurisdictions (US, EU) and whether to integrate a tax service.

## 23) Next Steps
- Confirm scope and open questions with stakeholders.
- Create migrations and models skeletons (artisan make:model -m) in a feature branch when ready.
- Implement NumberingService and basic lifecycle first; follow with PDF and email.

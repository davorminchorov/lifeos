# Invoicing Module - Detailed Implementation Plan

**Created:** 2026-01-13
**Based on:** INVOICING_MODULE_PLAN.md
**Architecture:** Laravel 12 + Alpine.js + Tailwind CSS

---

## Table of Contents
1. [Overview](#overview)
2. [Architecture Alignment](#architecture-alignment)
3. [Implementation Phases](#implementation-phases)
4. [Database Schema](#database-schema)
5. [Models & Enums](#models--enums)
6. [Controllers & Routes](#controllers--routes)
7. [Services Layer](#services-layer)
8. [Form Requests & Validation](#form-requests--validation)
9. [Blade Views & Components](#blade-views--components)
10. [Jobs & Events](#jobs--events)
11. [Testing Strategy](#testing-strategy)
12. [Configuration](#configuration)
13. [Security & Permissions](#security--permissions)

---

## Overview

The invoicing module will enable LifeOS to:
- Create and manage invoices for one-time and recurring billing
- Support multiple currencies with conversion
- Handle taxes (VAT/GST/sales tax), discounts, and credit notes
- Generate compliant invoice numbers and PDFs
- Automate email delivery and payment reminders
- Process manual payments and track payment history
- Track refunds and credit note applications

**Key Design Principles:**
- Follow existing LifeOS patterns (RESTful controllers, Form Requests, Blade components)
- Leverage existing CurrencyService for multi-currency support
- Use decimal(10,2) for monetary fields (store in cents where needed)
- Maintain user isolation via `user_id` foreign keys
- Support dark mode with CSS variables

---

## Architecture Alignment

### Existing LifeOS Patterns to Follow

| Pattern | Example Reference | Application to Invoicing |
|---------|-------------------|-------------------------|
| **RESTful Controllers** | `SubscriptionController.php` | `InvoiceController`, `CustomerController`, `PaymentController` |
| **Form Requests** | `StoreSubscriptionRequest.php` | `StoreInvoiceRequest`, `UpdateCustomerRequest` |
| **Blade Components** | `<x-form.input>`, `<x-button>` | Reuse for all invoice forms |
| **Service Layer** | `CurrencyService.php` | `InvoicingService`, `NumberingService`, `TaxService` |
| **Enums** | `ApplicationStatus.php` | `InvoiceStatus`, `PaymentStatus`, `TaxBehavior` |
| **Scopes** | `Subscription::scopeActive()` | `Invoice::scopePastDue()`, `scopeUnpaid()` |
| **Monetary Fields** | `decimal('cost', 10, 2)` | Store in cents as `integer` for precision |
| **Currency Support** | Subscriptions use `CurrencyService` | Integrate for conversions and display |
| **Status Badges** | Subscription status display | Invoice status with colors from enum |

### Directory Structure

```
app/
├── Enums/
│   ├── InvoiceStatus.php
│   ├── PaymentStatus.php
│   ├── TaxBehavior.php
│   ├── DiscountType.php
│   └── CreditNoteStatus.php
├── Events/
│   ├── InvoiceIssued.php
│   ├── InvoicePaid.php
│   ├── PaymentSucceeded.php
│   └── InvoicePastDue.php
├── Http/
│   ├── Controllers/
│   │   ├── CustomerController.php
│   │   ├── InvoiceController.php
│   │   ├── PaymentController.php
│   │   ├── CreditNoteController.php
│   │   └── TaxRateController.php
│   └── Requests/
│       ├── StoreCustomerRequest.php
│       ├── UpdateCustomerRequest.php
│       ├── StoreInvoiceRequest.php
│       ├── UpdateInvoiceRequest.php
│       ├── IssueInvoiceRequest.php
│       ├── StorePaymentRequest.php
│       └── RefundPaymentRequest.php
├── Jobs/
│   ├── GenerateInvoicePdf.php
│   ├── SendInvoiceIssuedEmail.php
│   └── SendPaymentReminderEmail.php
├── Listeners/
│   ├── SendInvoiceNotification.php
│   └── UpdateInvoiceStatus.php
├── Models/
│   ├── Customer.php
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   ├── Payment.php
│   ├── CreditNote.php
│   ├── CreditNoteApplication.php
│   ├── Refund.php
│   ├── TaxRate.php
│   ├── Discount.php
│   └── Sequence.php
└── Services/
    ├── InvoicingService.php
    ├── NumberingService.php
    ├── TaxService.php
    ├── DiscountService.php
    └── PdfRendererService.php

database/
├── factories/
│   ├── CustomerFactory.php
│   ├── InvoiceFactory.php
│   └── PaymentFactory.php
├── migrations/
│   ├── 2026_01_14_000001_create_customers_table.php
│   ├── 2026_01_14_000002_create_invoices_table.php
│   ├── 2026_01_14_000003_create_invoice_items_table.php
│   ├── 2026_01_14_000004_create_payments_table.php
│   ├── 2026_01_14_000005_create_credit_notes_table.php
│   ├── 2026_01_14_000006_create_credit_note_applications_table.php
│   ├── 2026_01_14_000007_create_refunds_table.php
│   ├── 2026_01_14_000008_create_tax_rates_table.php
│   ├── 2026_01_14_000009_create_discounts_table.php
│   └── 2026_01_14_000010_create_sequences_table.php
└── seeders/
    ├── TaxRateSeeder.php
    └── InvoicingSeeder.php

resources/views/
├── customers/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── invoices/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php (draft only)
│   ├── show.blade.php
│   └── pdf/
│       └── invoice-template.blade.php
├── payments/
│   ├── index.blade.php
│   └── show.blade.php
└── credit-notes/
    ├── index.blade.php
    ├── create.blade.php
    └── show.blade.php

config/
└── invoicing.php
```

---

## Implementation Phases

### Phase 1: Foundation (Week 1-2)
**Goal:** Core models, migrations, and basic CRUD

**Tasks:**
1. Create all Enums (`InvoiceStatus`, `PaymentStatus`, etc.)
2. Create database migrations for all tables
3. Create Eloquent models with relationships and casts
4. Implement `NumberingService` for invoice numbering
5. Create Form Request classes for validation
6. Build Customer management (CRUD)
7. Build basic Invoice CRUD (draft state only)
8. Add navigation menu items

**Deliverables:**
- ✅ All database tables created
- ✅ Customer management complete
- ✅ Draft invoices can be created and edited
- ✅ Invoice numbering works (INV-2026-000001 format)

---

### Phase 2: Invoice Lifecycle (Week 3-4)
**Goal:** Issue invoices, calculate totals, apply taxes/discounts

**Tasks:**
1. Implement `TaxService` for tax calculations
2. Implement `DiscountService` for discount logic
3. Implement `InvoicingService` for business logic
4. Add invoice item management (add/edit/remove line items)
5. Build invoice totals calculation (subtotal, tax, discount, total)
6. Implement "Issue Invoice" action (draft → issued)
7. Implement "Void Invoice" action
8. Add invoice status workflow
9. Create Tax Rate management interface
10. Create Discount management interface

**Deliverables:**
- ✅ Invoices can be issued with proper totals
- ✅ Tax calculations work (inclusive/exclusive)
- ✅ Discounts can be applied
- ✅ Invoice state machine functional

---

### Phase 3: PDF Generation & Email (Week 5)
**Goal:** Generate PDFs and send emails

**Tasks:**
1. Install and configure PDF library (Dompdf or Snappy)
2. Create `PdfRendererService`
3. Design invoice PDF template (Blade)
4. Implement PDF generation job (`GenerateInvoicePdf`)
5. Create email templates (invoice issued, payment reminder, receipt)
6. Implement email jobs (`SendInvoiceIssuedEmail`, etc.)
7. Add "Download PDF" and "Send Email" actions to UI
8. Store PDF files in storage with signed URLs

**Deliverables:**
- ✅ PDF generation works with branded template
- ✅ Invoices can be emailed to customers
- ✅ PDFs accessible from invoice detail page

---

### Phase 4: Manual Payments (Week 6)
**Goal:** Record manual payments and track invoice status

**Tasks:**
1. Create Payment model and migration
2. Build payment recording interface
3. Implement payment allocation logic
4. Update invoice status on payment (paid/partially_paid)
5. Create payment history view
6. Add payment notifications (receipt emails)
7. Implement partial payment tracking

**Deliverables:**
- ✅ Manual payments can be recorded
- ✅ Invoice status updates automatically
- ✅ Payment receipts sent via email
- ✅ Payment history visible

---

### Phase 5: Refunds & Credit Notes (Week 7)
**Goal:** Handle refunds and credit notes

**Tasks:**
1. Create Refund model and migration
2. Create CreditNote and CreditNoteApplication models
3. Implement refund logic (full/partial)
4. Implement credit note creation (link to original invoice)
5. Implement credit note application to invoices
6. Build UI for refund processing
7. Build UI for credit note management
8. Add credit note PDF generation
9. Send credit note emails

**Deliverables:**
- ✅ Refunds can be processed
- ✅ Credit notes can be created and applied
- ✅ Credit balance tracked per customer

---

### Phase 6: Dunning & Reminders (Week 8)
**Goal:** Automate payment reminders

**Tasks:**
1. Create dunning configuration (T+0, +7, +14, +21)
2. Implement `SendPaymentReminderEmail` job
3. Create scheduler command for daily dunning check
4. Build reminder email templates
5. Add reminder tracking (don't duplicate sends)
6. Implement "past_due" status transition
7. Add manual "Send Reminder" action
8. Create dunning analytics dashboard

**Deliverables:**
- ✅ Automated reminders sent on schedule
- ✅ Manual reminders can be triggered
- ✅ Dunning metrics tracked

---

### Phase 7: Analytics & Reporting (Week 9)
**Goal:** Financial insights and reporting

**Tasks:**
1. Create analytics dashboard (total revenue, outstanding, past due)
2. Build invoice aging report (0-30, 31-60, 61-90, 90+ days)
3. Add customer balance summary
4. Create revenue charts (monthly/quarterly)
5. Implement collection rate metrics
6. Add export to CSV/Excel
7. Create tax liability report
8. Build discount usage analytics

**Deliverables:**
- ✅ Analytics dashboard shows key metrics
- ✅ Reports can be exported
- ✅ Tax reporting available

---

### Phase 8: Subscription Integration (Week 10)
**Goal:** Auto-generate invoices from subscriptions

**Tasks:**
1. Add subscription linking to invoices
2. Create recurring invoice scheduler
3. Implement basic proration logic
4. Auto-issue invoices for active subscriptions
5. Handle failed payments (retry logic)
6. Add subscription upgrade/downgrade proration
7. Link invoices to subscription history

**Deliverables:**
- ✅ Subscriptions auto-generate invoices
- ✅ Proration works for mid-cycle changes
- ✅ Failed payments trigger dunning

---

### Phase 9: Polish & Testing (Week 11-12)
**Goal:** Production readiness

**Tasks:**
1. Write comprehensive feature tests
2. Write unit tests for services (tax, discount, numbering)
3. Implement audit logging for all invoice actions
4. Add permission checks (Admin, Finance, Support, Viewer)
5. Optimize database queries (N+1 prevention)
6. Add data validation and sanitization
7. Security review (PII encryption, CSRF, XSS)
8. Documentation (API docs, user guide)
9. Performance testing

**Deliverables:**
- ✅ Test coverage > 80%
- ✅ Security audit passed
- ✅ Performance benchmarks met
- ✅ Documentation complete

---

## Database Schema

### 1. Customers Table

```php
// database/migrations/2026_01_14_000001_create_customers_table.php

Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Basic Info
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('company_name')->nullable();

    // Billing Address
    $table->json('billing_address'); // {street, city, state, postal_code, country}

    // Tax Info
    $table->string('tax_id')->nullable(); // VAT/GST number
    $table->string('tax_country', 2)->nullable(); // ISO 3166-1 alpha-2

    // Payment Defaults
    $table->string('currency', 3)->default('MKD'); // ISO 4217
    $table->string('default_payment_method_id')->nullable();

    // Metadata
    $table->json('metadata')->nullable();
    $table->text('notes')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['user_id', 'email']);
    $table->index(['user_id', 'created_at']);
});
```

---

### 2. Invoices Table

```php
// database/migrations/2026_01_14_000002_create_invoices_table.php

Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

    // Invoice Number
    $table->string('number')->unique(); // INV-2026-000123
    $table->integer('sequence_year'); // 2026
    $table->integer('sequence_no'); // 123
    $table->string('hash', 8)->nullable(); // Short hash for verification

    // Status
    $table->enum('status', [
        'draft',
        'issued',
        'paid',
        'partially_paid',
        'past_due',
        'void',
        'written_off',
        'archived'
    ])->default('draft');

    // Currency
    $table->string('currency', 3); // Invoice currency (single currency per invoice)

    // Amounts (stored in cents as integers for precision)
    $table->bigInteger('subtotal'); // Before tax/discount (cents)
    $table->bigInteger('discount_total')->default(0); // Total discount (cents)
    $table->bigInteger('tax_total')->default(0); // Total tax (cents)
    $table->bigInteger('total'); // Final total (cents)
    $table->bigInteger('amount_due'); // Remaining to pay (cents)
    $table->bigInteger('amount_paid')->default(0); // Already paid (cents)

    // Tax Behavior
    $table->enum('tax_behavior', ['inclusive', 'exclusive'])->default('exclusive');

    // Dates
    $table->timestamp('issued_at')->nullable();
    $table->timestamp('due_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('voided_at')->nullable();

    // Terms & Notes
    $table->integer('net_terms_days')->default(14); // Payment terms (Net 14)
    $table->text('notes')->nullable(); // Customer-facing notes
    $table->text('internal_notes')->nullable(); // Internal only

    // PDF Storage
    $table->string('pdf_path')->nullable(); // Storage path to PDF

    // Subscription Link (optional)
    $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();

    // Metadata
    $table->json('metadata')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index(['user_id', 'customer_id']);
    $table->index(['status', 'due_at']); // For dunning queries
    $table->index(['issued_at']);
    $table->unique(['sequence_year', 'sequence_no']);
});
```

---

### 3. Invoice Items Table

```php
// database/migrations/2026_01_14_000003_create_invoice_items_table.php

Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

    // Line Item Details
    $table->string('name'); // Product/service name
    $table->text('description')->nullable();
    $table->decimal('quantity', 10, 2)->default(1);
    $table->bigInteger('unit_amount'); // Price per unit (cents)
    $table->string('currency', 3); // Should match invoice currency

    // Tax
    $table->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete();
    $table->bigInteger('tax_amount')->default(0); // Computed tax (cents)

    // Discount
    $table->foreignId('discount_id')->nullable()->constrained()->nullOnDelete();
    $table->bigInteger('discount_amount')->default(0); // Applied discount (cents)

    // Totals
    $table->bigInteger('amount'); // quantity * unit_amount (cents)
    $table->bigInteger('total_amount'); // amount - discount + tax (cents)

    // Metadata
    $table->json('metadata')->nullable();
    $table->integer('sort_order')->default(0); // Display order

    $table->timestamps();

    // Indexes
    $table->index(['invoice_id', 'sort_order']);
});
```

---

### 4. Payments Table

```php
// database/migrations/2026_01_14_000004_create_payments_table.php

Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

    // Payment Provider
    $table->string('provider')->default('manual'); // 'manual', 'bank_transfer', etc.
    $table->string('provider_payment_id')->nullable(); // External payment reference ID

    // Amount
    $table->bigInteger('amount'); // Payment amount (cents)
    $table->string('currency', 3);

    // Status
    $table->enum('status', [
        'pending',
        'succeeded',
        'failed',
        'refunded',
        'partially_refunded'
    ])->default('pending');

    // Timestamps
    $table->timestamp('attempted_at')->nullable();
    $table->timestamp('succeeded_at')->nullable();
    $table->timestamp('failed_at')->nullable();

    // Failure Info
    $table->string('failure_code')->nullable();
    $table->text('failure_message')->nullable();

    // Payment Method
    $table->string('payment_method')->nullable(); // 'card', 'bank_transfer', 'cash', etc.
    $table->json('payment_method_details')->nullable(); // Last 4 digits, brand, etc.

    // Metadata
    $table->json('metadata')->nullable();
    $table->text('notes')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['invoice_id', 'status']);
    $table->index(['provider', 'provider_payment_id']);
    $table->index(['succeeded_at']);
});
```

---

### 5. Credit Notes Table

```php
// database/migrations/2026_01_14_000005_create_credit_notes_table.php

Schema::create('credit_notes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete(); // Original invoice

    // Credit Note Number
    $table->string('number')->unique(); // CN-2026-000123

    // Status
    $table->enum('status', ['draft', 'issued', 'applied', 'void'])->default('draft');

    // Currency
    $table->string('currency', 3);

    // Amounts (stored in cents)
    $table->bigInteger('subtotal');
    $table->bigInteger('tax_total')->default(0);
    $table->bigInteger('total');
    $table->bigInteger('amount_remaining'); // Available credit

    // Reason
    $table->enum('reason', [
        'duplicate',
        'fraudulent',
        'requested_by_customer',
        'product_unsatisfactory',
        'order_canceled',
        'other'
    ])->nullable();
    $table->text('reason_notes')->nullable();

    // Dates
    $table->timestamp('issued_at')->nullable();

    // PDF Storage
    $table->string('pdf_path')->nullable();

    // Metadata
    $table->json('metadata')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['user_id', 'status']);
    $table->index(['customer_id', 'issued_at']);
});
```

---

### 6. Credit Note Applications Table

```php
// database/migrations/2026_01_14_000006_create_credit_note_applications_table.php

Schema::create('credit_note_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('credit_note_id')->constrained()->cascadeOnDelete();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete(); // Invoice receiving credit

    // Amount Applied (cents)
    $table->bigInteger('amount_applied');

    // Timestamp
    $table->timestamp('applied_at');

    $table->timestamps();

    // Indexes
    $table->index(['credit_note_id']);
    $table->index(['invoice_id']);
});
```

---

### 7. Refunds Table

```php
// database/migrations/2026_01_14_000007_create_refunds_table.php

Schema::create('refunds', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_id')->constrained()->cascadeOnDelete();

    // Amount
    $table->bigInteger('amount'); // Refund amount (cents)
    $table->string('currency', 3);

    // Provider
    $table->string('provider')->default('manual');
    $table->string('provider_refund_id')->nullable(); // External refund reference ID

    // Status
    $table->enum('status', ['pending', 'succeeded', 'failed', 'canceled'])->default('pending');

    // Reason
    $table->enum('reason', [
        'duplicate',
        'fraudulent',
        'requested_by_customer',
        'product_unsatisfactory',
        'other'
    ])->nullable();
    $table->text('reason_notes')->nullable();

    // Timestamps
    $table->timestamp('processed_at')->nullable();

    // Metadata
    $table->json('metadata')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['payment_id', 'status']);
    $table->index(['provider', 'provider_refund_id']);
});
```

---

### 8. Tax Rates Table

```php
// database/migrations/2026_01_14_000008_create_tax_rates_table.php

Schema::create('tax_rates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Tax Details
    $table->string('name'); // "VAT (20%)", "GST (10%)"
    $table->string('code')->nullable(); // "VAT", "GST", "SALES_TAX"
    $table->integer('percentage_basis_points'); // 2000 = 20.00%

    // Jurisdiction
    $table->string('country', 2)->nullable(); // ISO 3166-1 alpha-2
    $table->string('region')->nullable(); // State/province code

    // Behavior
    $table->boolean('inclusive')->default(false); // Tax included in price
    $table->boolean('active')->default(true);

    // Validity Period
    $table->date('valid_from')->nullable();
    $table->date('valid_to')->nullable();

    // Description
    $table->text('description')->nullable();

    // Metadata
    $table->json('metadata')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['user_id', 'active']);
    $table->index(['country', 'active']);
});
```

---

### 9. Discounts Table

```php
// database/migrations/2026_01_14_000009_create_discounts_table.php

Schema::create('discounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Discount Details
    $table->string('code')->unique(); // Coupon code
    $table->string('name'); // Display name
    $table->text('description')->nullable();

    // Type & Value
    $table->enum('type', ['percent', 'fixed'])->default('percent');
    $table->integer('value'); // Percentage (e.g., 20 = 20%) or fixed amount in cents
    $table->string('currency', 3)->nullable(); // Required for fixed type

    // Validity
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('ends_at')->nullable();
    $table->boolean('active')->default(true);

    // Usage Limits
    $table->integer('max_redemptions')->nullable(); // Total limit
    $table->integer('current_redemptions')->default(0);
    $table->integer('max_redemptions_per_customer')->nullable();

    // Minimum Requirements
    $table->bigInteger('minimum_amount')->nullable(); // Minimum order (cents)

    // Metadata
    $table->json('metadata')->nullable();

    $table->timestamps();

    // Indexes
    $table->index(['user_id', 'active']);
    $table->index(['code']);
    $table->index(['ends_at', 'active']);
});
```

---

### 10. Sequences Table

```php
// database/migrations/2026_01_14_000010_create_sequences_table.php

Schema::create('sequences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    // Sequence Scope
    $table->enum('scope', ['invoice', 'credit_note']);
    $table->integer('year');

    // Current Value
    $table->integer('current_value')->default(0);

    // Prefix (optional override)
    $table->string('prefix')->nullable(); // Custom prefix per user

    $table->timestamps();

    // Unique constraint: one sequence per user, scope, year
    $table->unique(['user_id', 'scope', 'year']);
});
```

---

## Models & Enums

### Enums

#### 1. InvoiceStatus Enum

```php
// app/Enums/InvoiceStatus.php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID = 'paid';
    case PARTIALLY_PAID = 'partially_paid';
    case PAST_DUE = 'past_due';
    case VOID = 'void';
    case WRITTEN_OFF = 'written_off';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ISSUED => 'Issued',
            self::PAID => 'Paid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAST_DUE => 'Past Due',
            self::VOID => 'Void',
            self::WRITTEN_OFF => 'Written Off',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ISSUED => 'blue',
            self::PAID => 'green',
            self::PARTIALLY_PAID => 'yellow',
            self::PAST_DUE => 'red',
            self::VOID => 'gray',
            self::WRITTEN_OFF => 'red',
            self::ARCHIVED => 'gray',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        return "inline-flex px-2 py-1 text-xs rounded-full bg-[color:var(--color-{$color}-50)] text-[color:var(--color-{$color}-600)]";
    }
}
```

#### 2. PaymentStatus Enum

```php
// app/Enums/PaymentStatus.php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::SUCCEEDED => 'green',
            self::FAILED => 'red',
            self::REFUNDED => 'gray',
            self::PARTIALLY_REFUNDED => 'yellow',
        };
    }
}
```

#### 3. TaxBehavior Enum

```php
// app/Enums/TaxBehavior.php

namespace App\Enums;

enum TaxBehavior: string
{
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';

    public function label(): string
    {
        return match ($this) {
            self::INCLUSIVE => 'Tax Inclusive',
            self::EXCLUSIVE => 'Tax Exclusive',
        };
    }
}
```

#### 4. DiscountType Enum

```php
// app/Enums/DiscountType.php

namespace App\Enums;

enum DiscountType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::PERCENT => 'Percentage',
            self::FIXED => 'Fixed Amount',
        };
    }
}
```

#### 5. CreditNoteStatus Enum

```php
// app/Enums/CreditNoteStatus.php

namespace App\Enums;

enum CreditNoteStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case APPLIED = 'applied';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ISSUED => 'Issued',
            self::APPLIED => 'Applied',
            self::VOID => 'Void',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ISSUED => 'blue',
            self::APPLIED => 'green',
            self::VOID => 'gray',
        };
    }
}
```

---

### Key Models

#### 1. Customer Model

```php
// app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company_name',
        'billing_address',
        'tax_id',
        'tax_country',
        'currency',
        'default_payment_method_id',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_address' => 'array',
            'metadata' => 'array',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    // Scopes

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    // Computed Attributes

    public function getOutstandingBalanceAttribute(): int
    {
        return $this->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'past_due'])
            ->sum('amount_due');
    }

    public function getCreditBalanceAttribute(): int
    {
        return $this->creditNotes()
            ->where('status', 'issued')
            ->sum('amount_remaining');
    }
}
```

#### 2. Invoice Model

```php
// app/Models/Invoice.php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'number',
        'sequence_year',
        'sequence_no',
        'hash',
        'status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'amount_due',
        'amount_paid',
        'tax_behavior',
        'issued_at',
        'due_at',
        'paid_at',
        'voided_at',
        'net_terms_days',
        'notes',
        'internal_notes',
        'pdf_path',
        'subscription_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'amount_due' => 'integer',
            'amount_paid' => 'integer',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', InvoiceStatus::DRAFT);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', InvoiceStatus::ISSUED);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            InvoiceStatus::ISSUED,
            InvoiceStatus::PARTIALLY_PAID,
            InvoiceStatus::PAST_DUE,
        ]);
    }

    public function scopePastDue($query)
    {
        return $query->where('status', InvoiceStatus::PAST_DUE)
            ->orWhere(function ($q) {
                $q->where('status', InvoiceStatus::ISSUED)
                  ->where('due_at', '<', now());
            });
    }

    // Computed Attributes

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total);
    }

    public function getFormattedAmountDueAttribute(): string
    {
        return $this->formatMoney($this->amount_due);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at && $this->due_at->isPast() && !$this->is_paid;
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }
        return now()->diffInDays($this->due_at);
    }

    // Helper Methods

    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
```

#### 3. InvoiceItem Model

```php
// app/Models/InvoiceItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'quantity',
        'unit_amount',
        'currency',
        'tax_rate_id',
        'tax_amount',
        'discount_id',
        'discount_amount',
        'amount',
        'total_amount',
        'metadata',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_amount' => 'integer',
            'tax_amount' => 'integer',
            'discount_amount' => 'integer',
            'amount' => 'integer',
            'total_amount' => 'integer',
            'metadata' => 'array',
        ];
    }

    // Relationships

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    // Computed Attributes

    public function getFormattedUnitAmountAttribute(): string
    {
        return $this->formatMoney($this->unit_amount);
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total_amount);
    }

    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
```

#### 4. Payment Model

```php
// app/Models/Payment.php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'provider',
        'provider_payment_id',
        'amount',
        'currency',
        'status',
        'attempted_at',
        'succeeded_at',
        'failed_at',
        'failure_code',
        'failure_message',
        'payment_method',
        'payment_method_details',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'attempted_at' => 'datetime',
            'succeeded_at' => 'datetime',
            'failed_at' => 'datetime',
            'payment_method_details' => 'array',
            'metadata' => 'array',
        ];
    }

    // Relationships

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // Scopes

    public function scopeSucceeded($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    // Computed Attributes

    public function getFormattedAmountAttribute(): string
    {
        $amount = $this->amount / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }

    public function getIsRefundableAttribute(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED && $this->amount > 0;
    }
}
```

---

## Controllers & Routes

### Routes Configuration

```php
// routes/web.php

Route::middleware(['auth'])->group(function () {

    // Invoicing navigation group
    Route::prefix('invoicing')->name('invoicing.')->group(function () {

        // Customers
        Route::resource('customers', CustomerController::class);
        Route::get('customers/{customer}/invoices', [CustomerController::class, 'invoices'])
            ->name('customers.invoices');

        // Invoices - Analytics routes BEFORE resource
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('analytics/dashboard', [InvoiceController::class, 'dashboard'])
                ->name('analytics.dashboard');
            Route::get('analytics/aging', [InvoiceController::class, 'agingReport'])
                ->name('analytics.aging');
        });

        // Invoices - Resource routes
        Route::resource('invoices', InvoiceController::class);

        // Invoice Actions
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])
            ->name('invoices.issue');
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])
            ->name('invoices.void');
        Route::post('invoices/{invoice}/write-off', [InvoiceController::class, 'writeOff'])
            ->name('invoices.write-off');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
            ->name('invoices.pdf');
        Route::post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])
            ->name('invoices.send-email');
        Route::post('invoices/{invoice}/send-reminder', [InvoiceController::class, 'sendReminder'])
            ->name('invoices.send-reminder');

        // Invoice Items
        Route::post('invoices/{invoice}/items', [InvoiceItemController::class, 'store'])
            ->name('invoices.items.store');
        Route::patch('invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'update'])
            ->name('invoices.items.update');
        Route::delete('invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'destroy'])
            ->name('invoices.items.destroy');

        // Payments
        Route::resource('payments', PaymentController::class)->only(['index', 'show']);
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])
            ->name('invoices.payments.store');
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])
            ->name('payments.refund');

        // Credit Notes
        Route::resource('credit-notes', CreditNoteController::class);
        Route::post('credit-notes/{creditNote}/issue', [CreditNoteController::class, 'issue'])
            ->name('credit-notes.issue');
        Route::post('credit-notes/{creditNote}/apply', [CreditNoteController::class, 'apply'])
            ->name('credit-notes.apply');
        Route::get('credit-notes/{creditNote}/pdf', [CreditNoteController::class, 'downloadPdf'])
            ->name('credit-notes.pdf');

        // Tax Rates
        Route::resource('tax-rates', TaxRateController::class);

        // Discounts
        Route::resource('discounts', DiscountController::class);
        Route::post('discounts/{discount}/validate', [DiscountController::class, 'validate'])
            ->name('discounts.validate');
    });
});
```

---

### Sample Controller Structure

#### InvoiceController

```php
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\IssueInvoiceRequest;
use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendInvoiceIssuedEmail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoicingService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoicingService $invoicingService
    ) {}

    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->where('user_id', auth()->id())
            ->with(['customer'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->when($request->past_due, fn($q) => $q->pastDue())
            ->latest('created_at')
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('invoices.create', compact('customers'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoicingService->createDraft(
            auth()->user(),
            $request->validated()
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice draft created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['customer', 'items.taxRate', 'items.discount', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== InvoiceStatus::DRAFT) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $customers = Customer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== InvoiceStatus::DRAFT) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->update($request->validated());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        if ($invoice->status !== InvoiceStatus::DRAFT) {
            return redirect()
                ->route('invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    // Custom Actions

    public function issue(IssueInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('issue', $invoice);

        $invoice = $this->invoicingService->issue($invoice);

        // Queue PDF generation and email
        GenerateInvoicePdf::dispatch($invoice);
        SendInvoiceIssuedEmail::dispatch($invoice);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice issued successfully.');
    }

    public function void(Request $request, Invoice $invoice)
    {
        $this->authorize('void', $invoice);

        $invoice = $this->invoicingService->void($invoice, $request->reason);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice voided successfully.');
    }

    public function writeOff(Request $request, Invoice $invoice)
    {
        $this->authorize('writeOff', $invoice);

        $invoice = $this->invoicingService->writeOff($invoice, $request->reason);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice written off successfully.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            return redirect()
                ->back()
                ->with('error', 'PDF not available.');
        }

        return Storage::download($invoice->pdf_path, $invoice->number . '.pdf');
    }

    public function sendEmail(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        SendInvoiceIssuedEmail::dispatch($invoice);

        return redirect()
            ->back()
            ->with('success', 'Invoice email queued for delivery.');
    }

    public function sendReminder(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        SendPaymentReminderEmail::dispatch($invoice);

        return redirect()
            ->back()
            ->with('success', 'Reminder email queued for delivery.');
    }

    // Analytics

    public function dashboard()
    {
        $metrics = $this->invoicingService->getDashboardMetrics(auth()->user());

        return view('invoices.dashboard', compact('metrics'));
    }

    public function agingReport()
    {
        $report = $this->invoicingService->getAgingReport(auth()->user());

        return view('invoices.aging-report', compact('report'));
    }
}
```

---

## Services Layer

### InvoicingService

```php
// app/Services/InvoicingService.php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceIssued;
use App\Events\InvoicePaid;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoicingService
{
    public function __construct(
        protected NumberingService $numberingService,
        protected TaxService $taxService,
        protected DiscountService $discountService
    ) {}

    /**
     * Create a draft invoice
     */
    public function createDraft(User $user, array $data): Invoice
    {
        return DB::transaction(function () use ($user, $data) {
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'],
                'status' => InvoiceStatus::DRAFT,
                'currency' => $data['currency'] ?? 'MKD',
                'tax_behavior' => $data['tax_behavior'] ?? 'exclusive',
                'net_terms_days' => $data['net_terms_days'] ?? config('invoicing.net_terms_days', 14),
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'subtotal' => 0,
                'tax_total' => 0,
                'discount_total' => 0,
                'total' => 0,
                'amount_due' => 0,
            ]);

            return $invoice;
        });
    }

    /**
     * Add an item to an invoice
     */
    public function addItem(Invoice $invoice, array $itemData): void
    {
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            throw new \Exception('Can only add items to draft invoices');
        }

        $invoice->items()->create($itemData);

        $this->recalculateTotals($invoice);
    }

    /**
     * Recalculate invoice totals
     */
    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $subtotal = 0;
        $taxTotal = 0;
        $discountTotal = 0;

        foreach ($invoice->items as $item) {
            // Calculate line amount
            $lineAmount = $item->quantity * $item->unit_amount;

            // Apply discount
            $lineDiscount = $this->discountService->calculateLineDiscount(
                $item->discount,
                $lineAmount,
                $invoice->currency
            );

            // Apply tax
            $lineTax = $this->taxService->calculateLineTax(
                $item->taxRate,
                $lineAmount - $lineDiscount,
                $invoice->tax_behavior
            );

            // Update item
            $item->update([
                'amount' => $lineAmount,
                'discount_amount' => $lineDiscount,
                'tax_amount' => $lineTax,
                'total_amount' => $lineAmount - $lineDiscount + $lineTax,
            ]);

            // Accumulate totals
            $subtotal += $lineAmount;
            $discountTotal += $lineDiscount;
            $taxTotal += $lineTax;
        }

        $total = $subtotal - $discountTotal + $taxTotal;

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'amount_due' => $total - $invoice->amount_paid,
        ]);

        return $invoice->fresh();
    }

    /**
     * Issue an invoice (draft → issued)
     */
    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::DRAFT) {
            throw new \Exception('Only draft invoices can be issued');
        }

        if ($invoice->items()->count() === 0) {
            throw new \Exception('Cannot issue invoice with no items');
        }

        return DB::transaction(function () use ($invoice) {
            // Reserve invoice number
            $number = $this->numberingService->reserveInvoiceNumber($invoice->user_id);

            // Update invoice
            $invoice->update([
                'number' => $number['number'],
                'sequence_year' => $number['year'],
                'sequence_no' => $number['sequence'],
                'hash' => $this->generateHash(),
                'status' => InvoiceStatus::ISSUED,
                'issued_at' => now(),
                'due_at' => now()->addDays($invoice->net_terms_days),
            ]);

            // Fire event
            event(new InvoiceIssued($invoice));

            return $invoice->fresh();
        });
    }

    /**
     * Void an invoice
     */
    public function void(Invoice $invoice, ?string $reason = null): Invoice
    {
        if (!in_array($invoice->status, [InvoiceStatus::ISSUED, InvoiceStatus::PARTIALLY_PAID])) {
            throw new \Exception('Only issued or partially paid invoices can be voided');
        }

        $invoice->update([
            'status' => InvoiceStatus::VOID,
            'voided_at' => now(),
            'internal_notes' => ($invoice->internal_notes ?? '') . "\n\nVoided: " . ($reason ?? 'No reason provided'),
        ]);

        return $invoice->fresh();
    }

    /**
     * Write off an invoice
     */
    public function writeOff(Invoice $invoice, ?string $reason = null): Invoice
    {
        if ($invoice->status !== InvoiceStatus::PAST_DUE) {
            throw new \Exception('Only past due invoices can be written off');
        }

        $invoice->update([
            'status' => InvoiceStatus::WRITTEN_OFF,
            'internal_notes' => ($invoice->internal_notes ?? '') . "\n\nWritten off: " . ($reason ?? 'No reason provided'),
        ]);

        return $invoice->fresh();
    }

    /**
     * Record a payment against an invoice
     */
    public function recordPayment(Invoice $invoice, int $amount, array $paymentData = []): void
    {
        if ($amount > $invoice->amount_due) {
            throw new \Exception('Payment amount exceeds amount due');
        }

        DB::transaction(function () use ($invoice, $amount, $paymentData) {
            // Create payment record
            $invoice->payments()->create([
                'amount' => $amount,
                'currency' => $invoice->currency,
                'status' => 'succeeded',
                'succeeded_at' => now(),
                ...$paymentData,
            ]);

            // Update invoice
            $newAmountPaid = $invoice->amount_paid + $amount;
            $newAmountDue = $invoice->amount_due - $amount;

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'amount_due' => $newAmountDue,
                'status' => $newAmountDue === 0 ? InvoiceStatus::PAID : InvoiceStatus::PARTIALLY_PAID,
                'paid_at' => $newAmountDue === 0 ? now() : null,
            ]);

            // Fire event
            if ($invoice->status === InvoiceStatus::PAID) {
                event(new InvoicePaid($invoice));
            }
        });
    }

    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics(User $user): array
    {
        $invoices = Invoice::where('user_id', $user->id);

        return [
            'total_revenue' => $invoices->clone()->sum('amount_paid'),
            'outstanding' => $invoices->clone()->unpaid()->sum('amount_due'),
            'past_due' => $invoices->clone()->pastDue()->sum('amount_due'),
            'invoices_count' => $invoices->clone()->count(),
            'paid_count' => $invoices->clone()->where('status', InvoiceStatus::PAID)->count(),
            'unpaid_count' => $invoices->clone()->unpaid()->count(),
            'overdue_count' => $invoices->clone()->pastDue()->count(),
        ];
    }

    /**
     * Get aging report
     */
    public function getAgingReport(User $user): array
    {
        // Implementation for aging buckets (0-30, 31-60, 61-90, 90+)
        // ...
    }

    protected function generateHash(): string
    {
        return substr(md5(uniqid(rand(), true)), 0, 8);
    }
}
```

---

## Form Requests & Validation

### StoreInvoiceRequest

```php
// app/Http/Requests/StoreInvoiceRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'currency' => ['required', 'string', 'size:3'],
            'tax_behavior' => ['required', 'in:inclusive,exclusive'],
            'net_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-letter ISO code.',
        ];
    }
}
```

---

## Blade Views & Components

### Invoice Index View

```blade
{{-- resources/views/invoices/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Invoices')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Invoices</h1>
        <x-button href="{{ route('invoices.create') }}" variant="primary">
            Create Invoice
        </x-button>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Filters --}}
        <div class="flex gap-4">
            <form method="GET" class="flex gap-4">
                <x-form.select name="status" label="Status">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="issued">Issued</option>
                    <option value="paid">Paid</option>
                    <option value="past_due">Past Due</option>
                </x-form.select>

                <x-button type="submit" variant="secondary">Filter</x-button>
            </form>
        </div>

        {{-- Invoices Table --}}
        <div class="overflow-hidden rounded-lg border">
            <table class="min-w-full divide-y">
                <thead class="bg-[color:var(--color-primary-100)]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4">
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-medium hover:underline">
                                    {{ $invoice->number ?? 'Draft' }}
                                </a>
                            </td>
                            <td class="px-6 py-4">{{ $invoice->customer->name }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $invoice->status->badgeClass() }}">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $invoice->formatted_total }}</td>
                            <td class="px-6 py-4">
                                {{ $invoice->due_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-button href="{{ route('invoices.show', $invoice) }}" variant="secondary" size="sm">
                                    View
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        {{ $invoices->links() }}
    </div>
@endsection
```

---

## Jobs & Events

### SendInvoiceIssuedEmail Job

```php
// app/Jobs/SendInvoiceIssuedEmail.php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceIssuedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceIssuedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function handle(): void
    {
        $customer = $this->invoice->customer;

        if (!$customer->email) {
            \Log::warning("Cannot send invoice email - customer has no email", [
                'invoice_id' => $this->invoice->id,
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $customer->notify(new InvoiceIssuedNotification($this->invoice));
    }
}
```

---

## Testing Strategy

### Feature Tests

```php
// tests/Feature/InvoiceTest.php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_draft_invoice()
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
            'net_terms_days' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::DRAFT,
        ]);
    }

    public function test_draft_invoice_can_be_issued()
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->draft()->hasItems(2)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('invoices.issue', $invoice));

        $response->assertRedirect();
        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::ISSUED, $invoice->status);
        $this->assertNotNull($invoice->number);
        $this->assertNotNull($invoice->issued_at);
    }

    public function test_payment_updates_invoice_status()
    {
        // Test implementation
    }
}
```

---

## Configuration

### config/invoicing.php

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Invoice Number Prefix
    |--------------------------------------------------------------------------
    */

    'prefix' => env('INVOICE_PREFIX', 'INV'),

    /*
    |--------------------------------------------------------------------------
    | Credit Note Prefix
    |--------------------------------------------------------------------------
    */

    'credit_note_prefix' => env('CREDIT_NOTE_PREFIX', 'CN'),

    /*
    |--------------------------------------------------------------------------
    | Default Net Terms (Days)
    |--------------------------------------------------------------------------
    */

    'net_terms_days' => env('INVOICE_NET_TERMS', 14),

    /*
    |--------------------------------------------------------------------------
    | Dunning Configuration
    |--------------------------------------------------------------------------
    */

    'dunning' => [
        'enabled' => env('DUNNING_ENABLED', true),
        'reminder_days' => [0, 7, 14, 21], // Days after issue
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Configuration
    |--------------------------------------------------------------------------
    */

    'pdf' => [
        'engine' => env('PDF_ENGINE', 'dompdf'), // 'dompdf' or 'snappy'
        'storage_path' => 'invoices/pdfs',
    ],

];
```

---

## Security & Permissions

### Policy Example

```php
// app/Policies/InvoicePolicy.php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id && $invoice->status === 'draft';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id && $invoice->status === 'draft';
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id && $invoice->status === 'draft';
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id && in_array($invoice->status, ['issued', 'partially_paid']);
    }

    public function writeOff(User $user, Invoice $invoice): bool
    {
        // Require admin role for write-offs (future: role check)
        return $invoice->user_id === $user->id && $invoice->status === 'past_due';
    }
}
```

---

## Next Steps

1. **Phase 1 Implementation:**
   - Create all migrations
   - Create all models with relationships
   - Build customer CRUD
   - Build draft invoice CRUD
   - Implement numbering service

2. **Testing:**
   - Write feature tests for each phase
   - Test invoice lifecycle transitions
   - Test payment processing

3. **Documentation:**
   - API documentation
   - User guide for invoicing module
   - Developer documentation

4. **Deployment:**
   - Run migrations
   - Seed tax rates
   - Set up cron for dunning

---

**End of Implementation Plan**

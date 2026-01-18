# Invoicing Module Documentation

## Overview

The Invoicing Module is a comprehensive invoicing and billing system integrated into LifeOS. It provides complete functionality for managing customers, creating invoices, recording payments, issuing credit notes, and analyzing financial data.

## Features

### Core Functionality

- **Customer Management**: Create and manage customer profiles with billing information
- **Invoice Management**: Create, issue, and track invoices with line items
- **Payment Recording**: Record manual payments with multiple payment methods
- **Credit Notes**: Issue and apply credit notes to customer accounts
- **Tax Management**: Configure and apply tax rates (tax-inclusive or exclusive)
- **Discount Management**: Create and track discount codes
- **Analytics Dashboard**: Visualize revenue trends and customer insights
- **Data Export**: Export invoices and payments to CSV

## Navigation

Access the invoicing module from the main navigation menu:

**Invoicing** dropdown menu:
- Dashboard - Analytics and reports
- Customers - Manage customer profiles
- Invoices - Create and manage invoices
- Credit Notes - Issue and apply credits
- Tax Rates - Configure tax rates
- Discounts - Manage discount codes

## User Guide

### 1. Customer Management

#### Creating a Customer

1. Navigate to **Invoicing → Customers**
2. Click **Create Customer**
3. Fill in customer details:
   - **Basic Information**: Name, email, phone, company name
   - **Billing Address**: Street, city, state, postal code, country
   - **Tax Information**: Tax ID, tax country
   - **Currency**: Default currency for invoices (MKD, USD, EUR, etc.)
   - **Notes**: Internal notes about the customer
4. Click **Create Customer**

#### Viewing Customer Details

- Click **View** on any customer to see:
  - Outstanding balance
  - Credit balance
  - Customer details and billing address
  - Recent invoices
- From the customer view, you can:
  - Edit customer information
  - Create a new invoice for the customer
  - Delete customer (only if no invoices exist)

### 2. Invoice Management

#### Creating an Invoice

1. Navigate to **Invoicing → Invoices**
2. Click **Create Invoice**
3. Configure invoice settings:
   - **Customer**: Select the customer
   - **Currency**: Choose invoice currency
   - **Tax Behavior**:
     - Tax Exclusive: Tax added on top of prices
     - Tax Inclusive: Tax included in prices
   - **Payment Terms**: Number of days until due (default: 14)
   - **Notes**: Customer-visible and internal notes
4. Click **Create Draft**

#### Adding Line Items

After creating a draft invoice:

1. Navigate to the invoice detail page
2. Click **Add Item**
3. Enter line item details:
   - **Description**: Product or service description
   - **Quantity**: Number of units
   - **Unit Price**: Price in cents (e.g., 10000 = $100.00)
4. Click **Add Item**
5. Repeat for additional line items
6. Invoice totals calculate automatically

#### Issuing an Invoice

Once line items are added:

1. Review the invoice totals
2. Click **Issue Invoice**
3. Confirm the action
4. The invoice will:
   - Receive a unique number (e.g., INV-2026-000001)
   - Set the issued date to today
   - Calculate the due date based on payment terms
   - Change status from "Draft" to "Issued"

#### Invoice Lifecycle

Invoices progress through these statuses:
- **Draft**: Not yet issued, can be edited
- **Issued**: Sent to customer, awaiting payment
- **Partially Paid**: Some payment received
- **Paid**: Fully paid
- **Past Due**: Overdue for payment
- **Void**: Cancelled invoice
- **Written Off**: Bad debt
- **Archived**: Historical record

### 3. Payment Recording

#### Recording a Payment

For issued or partially paid invoices:

1. Open the invoice detail page
2. Click **Record Payment**
3. Enter payment details:
   - **Payment Amount**: Amount in cents
   - **Payment Date**: Date payment was received
   - **Payment Method**:
     - Bank Transfer
     - Cash
     - Check
     - Credit Card
     - Debit Card
     - Other
   - **Reference**: Transaction ID, check number, etc.
   - **Notes**: Additional payment information
4. Click **Record Payment**

The system will:
- Update the invoice `amount_paid`
- Recalculate `amount_due`
- Update invoice status (partially_paid or paid)
- Record the payment in payment history

#### Viewing Payment History

On the invoice detail page:
- See all recorded payments
- View payment dates, methods, and amounts
- Delete payments if needed (recalculates totals)

### 4. Credit Notes

#### Creating a Credit Note

1. Navigate to **Invoicing → Credit Notes**
2. Click **Create Credit Note**
3. Fill in credit note details:
   - **Customer**: Select the customer
   - **Related Invoice**: Optional link to specific invoice
   - **Currency**: Credit currency
   - **Credit Amount**: Amount in cents
   - **Reason**:
     - Product Return
     - Service Cancellation
     - Billing Error
     - Goodwill
     - Duplicate Payment
     - Other
   - **Description**: Detailed explanation
   - **Notes**: Internal notes
4. Click **Create Credit Note**

The credit note receives a unique number (e.g., CN-2026-000001)

#### Applying Credit to an Invoice

1. Open the credit note detail page
2. Click **Apply to Invoice**
3. Select the invoice to apply credit
4. Enter the amount to apply (in cents)
5. Click **Apply Credit**

The system will:
- Deduct the amount from credit note's remaining balance
- Record a payment on the invoice (method: credit_note)
- Update invoice status if fully paid
- Track the application in history
- Change credit note status to "Applied" if fully used

### 5. Tax Rate Management

#### Creating a Tax Rate

1. Navigate to **Invoicing → Tax Rates**
2. Click **Create Tax Rate**
3. Configure tax rate:
   - **Name**: e.g., "VAT", "Sales Tax", "GST"
   - **Rate**: In basis points (2000 = 20%, 100 basis points = 1%)
   - **Country**: 2-letter ISO code (optional)
   - **Active**: Enable/disable
   - **Valid Period**: Start and end dates (optional)
   - **Description**: Additional details
4. Click **Create Tax Rate**

#### Using Tax Rates

Tax rates can be applied to invoice line items:
- Only active tax rates appear in selections
- Tax rates respect validity dates
- Tax behavior (inclusive/exclusive) set at invoice level
- Existing invoices unchanged when rate is modified

### 6. Discount Management

#### Creating a Discount

1. Navigate to **Invoicing → Discounts**
2. Click **Create Discount**
3. Configure discount:
   - **Code**: Unique discount code (e.g., "SAVE20")
   - **Type**:
     - Percentage: Discount as percentage (basis points)
     - Fixed Amount: Discount in cents
   - **Value**:
     - For percentage: 2000 = 20%
     - For fixed: Amount in cents
   - **Active**: Enable/disable
   - **Valid Period**: Start and end dates (optional)
   - **Max Redemptions**: Limit usage (optional)
   - **Description**: Details about the discount
4. Click **Create Discount**

#### Using Discounts

Discounts can be applied to invoice line items:
- Only active discounts within validity period can be used
- System tracks redemption count
- Discount automatically disabled when max redemptions reached
- Percentage discounts limited to 100%

### 7. Analytics Dashboard

#### Accessing the Dashboard

Navigate to **Invoicing → Dashboard** to view:

**Summary Statistics:**
- Total Revenue (all paid invoices)
- Outstanding Amount (unpaid invoices)
- Total Invoices
- Total Customers
- Draft Invoices count
- Overdue Invoices count
- Available Credit balance

**Revenue Trend:**
- Last 6 months of payment data
- Monthly breakdown with visual bars
- Identify seasonal patterns

**Top Customers:**
- Top 5 customers by revenue
- See customer contact info
- Analyze customer value

**Recent Activity:**
- Last 10 invoices created
- Last 10 payments received
- Quick status overview

#### Exporting Data

From the dashboard:

**Export Invoices:**
1. Click **Export Invoices**
2. CSV file downloads with all invoice data:
   - Invoice number, customer, status
   - Currency, subtotal, tax, total
   - Amount paid, amount due
   - Dates (issued, due, created)

**Export Payments:**
1. Click **Export Payments**
2. CSV file downloads with all payment data:
   - Payment date, invoice number
   - Customer, amount
   - Payment method, reference, notes

## Technical Details

### Data Format

**Money Values:**
- All amounts stored as integers in cents
- Example: $100.00 = 10000 cents
- Ensures precision, no floating-point errors

**Percentages:**
- Stored as basis points (integer)
- 100 basis points = 1%
- Example: 20% = 2000 basis points

**Invoice Numbers:**
- Format: `PREFIX-YEAR-SEQUENCE`
- Example: `INV-2026-000001`
- Unique per user, year, and scope
- Sequential numbering with gap tolerance

**Credit Note Numbers:**
- Format: `PREFIX-YEAR-SEQUENCE`
- Example: `CN-2026-000001`
- Separate sequence from invoices

### Tax Calculations

**Tax Exclusive:**
```
Line Total = Quantity × Unit Price
Tax Amount = Line Total × Tax Rate
Final Total = Line Total + Tax Amount
```

**Tax Inclusive:**
```
Line Total = Quantity × Unit Price
Tax Amount = Line Total × (Tax Rate / (1 + Tax Rate))
Pre-Tax Amount = Line Total - Tax Amount
```

### Discount Calculations

**Percentage Discount:**
```
Discount Amount = Line Total × (Discount Value / 10000)
```

**Fixed Amount Discount:**
```
Discount Amount = min(Discount Value, Line Total)
```

### Invoice Total Calculation

```
Subtotal = Sum of (Quantity × Unit Price) for all items
Discount Total = Sum of discount amounts for all items
Tax Total = Sum of tax amounts for all items
Total = Subtotal - Discount Total + Tax Total
Amount Due = Total - Amount Paid
```

## Workflows

### Complete Invoice Workflow

1. **Create Customer**
   - Add customer with billing info
   - Set default currency

2. **Create Invoice Draft**
   - Select customer
   - Set tax behavior and payment terms
   - Add customer notes

3. **Add Line Items**
   - Add products/services
   - Apply taxes and discounts
   - Review calculated totals

4. **Issue Invoice**
   - Generate invoice number
   - Set issued and due dates
   - Send to customer (external process)

5. **Record Payments**
   - Enter payment details as received
   - Invoice status updates automatically
   - Track payment methods

6. **Handle Credits (if needed)**
   - Create credit note for returns/errors
   - Apply credit to outstanding invoices
   - Track credit balance

### Partial Payment Workflow

1. Customer pays $500 on $1000 invoice
2. Record payment for $500
3. Invoice status: "Partially Paid"
4. Amount Due: $500
5. Customer pays remaining $500
6. Record second payment
7. Invoice status: "Paid"
8. Amount Due: $0

### Credit Note Workflow

1. Customer returns $200 worth of products
2. Create credit note for $200
3. Credit Note status: "Available"
4. Customer has $300 outstanding invoice
5. Apply $200 credit to invoice
6. System records $200 payment (method: credit_note)
7. Invoice Amount Due: $100
8. Credit Note status: "Applied"

## Security & Authorization

All invoicing operations require authentication and authorization:

- Users can only access their own data
- All queries filtered by `user_id`
- Controllers verify ownership before actions
- Related records validated (customer, invoice, etc.)
- Cascade deletions respect relationships
- Cannot delete tax rates/discounts in use

## Best Practices

### Creating Invoices

1. ✅ Set up customers before creating invoices
2. ✅ Configure tax rates and discounts beforehand
3. ✅ Use clear, descriptive line item descriptions
4. ✅ Review totals before issuing
5. ✅ Issue invoices only when ready to send
6. ❌ Don't delete draft invoices, use them as templates

### Recording Payments

1. ✅ Enter exact amounts received
2. ✅ Include payment reference numbers
3. ✅ Use appropriate payment methods
4. ✅ Record payments on the date received
5. ❌ Don't record payments in advance

### Managing Tax Rates

1. ✅ Create tax rates before they're needed
2. ✅ Use descriptive names (e.g., "CA Sales Tax 7.5%")
3. ✅ Set validity dates for time-limited rates
4. ✅ Deactivate instead of deleting
5. ❌ Don't modify rates affecting existing invoices

### Using Discounts

1. ✅ Set redemption limits to control usage
2. ✅ Use validity dates for promotions
3. ✅ Choose descriptive codes
4. ✅ Deactivate expired discounts
5. ❌ Don't delete discounts with history

## Troubleshooting

### Invoice Won't Issue

**Problem**: "Issue Invoice" button disabled
**Solution**:
- Ensure invoice has at least one line item
- Verify invoice total is greater than 0
- Check that invoice is in "Draft" status

### Cannot Delete Customer

**Problem**: "Cannot delete customer with existing invoices"
**Solution**:
- Customers with invoices cannot be deleted
- This prevents data integrity issues
- Archive or mark customer as inactive instead

### Cannot Delete Tax Rate/Discount

**Problem**: "Cannot delete tax rate/discount in use"
**Solution**:
- Tax rates and discounts used in invoices cannot be deleted
- Mark as inactive instead
- Set end date to prevent future use

### Payment Amount Validation

**Problem**: "Payment amount cannot exceed amount due"
**Solution**:
- Check invoice's amount_due
- Enter amount in cents, not dollars
- For overpayment, create credit note for excess

## Configuration

### Environment Variables

```env
# Invoice numbering prefix (default: INV)
INVOICE_PREFIX=INV

# Credit note prefix (default: CN)
CREDIT_NOTE_PREFIX=CN

# Default payment terms in days (default: 14)
INVOICE_NET_TERMS=14

# Enable dunning (payment reminders)
DUNNING_ENABLED=true
```

### Configuration File

Location: `config/invoicing.php`

```php
return [
    'prefix' => env('INVOICE_PREFIX', 'INV'),
    'credit_note_prefix' => env('CREDIT_NOTE_PREFIX', 'CN'),
    'net_terms_days' => env('INVOICE_NET_TERMS', 14),
    'dunning' => [
        'enabled' => env('DUNNING_ENABLED', true),
        'reminder_days' => [0, 7, 14, 21],
    ],
];
```

## Support & Maintenance

### Database Maintenance

The invoicing module uses these tables:
- `customers` - Customer profiles
- `invoices` - Invoice headers
- `invoice_items` - Invoice line items
- `payments` - Payment records
- `credit_notes` - Credit note headers
- `credit_note_applications` - Credit applications
- `tax_rates` - Tax rate definitions
- `discounts` - Discount code definitions
- `sequences` - Number generation sequences
- `refunds` - Refund records

### Regular Maintenance Tasks

1. **Archive Old Invoices**: Mark paid invoices as archived after 1 year
2. **Clean Up Draft Invoices**: Review and delete abandoned drafts
3. **Deactivate Expired Discounts**: Disable discounts past validity
4. **Update Tax Rates**: Create new rates when tax laws change
5. **Export Data**: Backup invoice and payment data monthly

## Version History

- **v1.0** (2026-01-18): Initial release
  - Customer management
  - Invoice creation and lifecycle
  - Payment recording
  - Credit notes
  - Tax rates and discounts
  - Analytics dashboard
  - CSV export

## License

This module is part of LifeOS and follows the same license terms.

## Credits

Developed for LifeOS personal life management platform.

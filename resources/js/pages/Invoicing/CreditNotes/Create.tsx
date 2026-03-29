import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import type { Customer, Invoice } from '@/types/models'

interface CreditNoteCreateProps {
    customers: Customer[]
    invoices: Array<Invoice & { customer?: Customer }>
    selectedCustomerId?: number | null
    selectedInvoiceId?: number | null
}

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']
const reasons = [
    { value: 'product_return', label: 'Product Return' },
    { value: 'service_cancellation', label: 'Service Cancellation' },
    { value: 'billing_error', label: 'Billing Error' },
    { value: 'goodwill', label: 'Goodwill' },
    { value: 'duplicate_payment', label: 'Duplicate Payment' },
    { value: 'other', label: 'Other' },
]

export default function CreditNoteCreate({ customers, invoices, selectedCustomerId, selectedInvoiceId }: CreditNoteCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: selectedCustomerId ? String(selectedCustomerId) : '',
        invoice_id: selectedInvoiceId ? String(selectedInvoiceId) : '',
        currency: 'MKD',
        amount: '',
        reason: '',
        description: '',
        notes: '',
    })

    const filteredInvoices = data.customer_id
        ? invoices.filter(inv => String(inv.customer_id) === data.customer_id)
        : invoices

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/invoicing/credit-notes')
    }

    return (
        <AppLayout>
            <Head title="Create Credit Note" />

            <PageHeader title="Create Credit Note" description="Issue a new credit note">
                <Button variant="outline" asChild>
                    <Link href="/invoicing/credit-notes">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Credit Note Details" description="Specify the credit note details">
                            <FormField label="Customer" name="customer_id" error={errors.customer_id} required>
                                <Select value={data.customer_id} onValueChange={v => { setData('customer_id', v); setData('invoice_id', '') }}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {customers.map(c => (
                                            <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Related Invoice" name="invoice_id" error={errors.invoice_id}>
                                <Select value={data.invoice_id} onValueChange={v => setData('invoice_id', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select invoice (optional)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">None</SelectItem>
                                        {filteredInvoices.map(inv => (
                                            <SelectItem key={inv.id} value={String(inv.id)}>
                                                {inv.number ?? `Draft #${inv.id}`}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Currency" name="currency" error={errors.currency} required>
                                <Select value={data.currency} onValueChange={v => setData('currency', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {currencies.map(c => (
                                            <SelectItem key={c} value={c}>{c}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Amount (in cents)"
                                name="amount"
                                type="number"
                                value={data.amount}
                                onChange={e => setData('amount', e.target.value)}
                                error={errors.amount}
                                required
                                min="1"
                                placeholder="e.g. 1000 for 10.00"
                            />
                            <FormField label="Reason" name="reason" error={errors.reason} required>
                                <Select value={data.reason} onValueChange={v => setData('reason', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select reason" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {reasons.map(r => (
                                            <SelectItem key={r.value} value={r.value}>{r.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Description"
                                name="description"
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                error={errors.description}
                                multiline
                                placeholder="Describe the reason for this credit note"
                            />
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Internal notes"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/invoicing/credit-notes">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Credit Note'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

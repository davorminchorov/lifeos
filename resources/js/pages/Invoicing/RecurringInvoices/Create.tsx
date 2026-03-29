import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import type { Customer } from '@/types/models'

interface RecurringInvoiceCreateProps {
    customers: Customer[]
    selectedCustomerId?: number | null
}

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']
const billingIntervals = [
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'yearly', label: 'Yearly' },
]
const taxBehaviors = [
    { value: 'exclusive', label: 'Tax Exclusive' },
    { value: 'inclusive', label: 'Tax Inclusive' },
]

export default function RecurringInvoiceCreate({ customers, selectedCustomerId }: RecurringInvoiceCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: selectedCustomerId ? String(selectedCustomerId) : '',
        name: '',
        description: '',
        billing_interval: 'monthly',
        interval_count: '1',
        start_date: '',
        end_date: '',
        billing_day_of_month: '',
        occurrences_limit: '',
        currency: 'MKD',
        tax_behavior: 'exclusive',
        net_terms_days: '14',
        auto_send_email: true,
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/invoicing/recurring-invoices')
    }

    return (
        <AppLayout>
            <Head title="Create Recurring Invoice" />

            <PageHeader title="Create Recurring Invoice" description="Set up automatic invoice generation">
                <Button variant="outline" asChild>
                    <Link href="/invoicing/recurring-invoices">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="Recurring invoice details">
                            <FormField
                                label="Name"
                                name="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="e.g. Monthly Retainer"
                            />
                            <FormField label="Customer" name="customer_id" error={errors.customer_id} required>
                                <Select value={data.customer_id} onValueChange={v => setData('customer_id', v)}>
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
                            <FormField
                                label="Description"
                                name="description"
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                error={errors.description}
                                multiline
                                placeholder="Optional description"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Billing Schedule" description="How often to generate invoices">
                            <FormField label="Billing Interval" name="billing_interval" error={errors.billing_interval} required>
                                <Select value={data.billing_interval} onValueChange={v => setData('billing_interval', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {billingIntervals.map(b => (
                                            <SelectItem key={b.value} value={b.value}>{b.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Interval Count"
                                name="interval_count"
                                type="number"
                                value={data.interval_count}
                                onChange={e => setData('interval_count', e.target.value)}
                                error={errors.interval_count}
                                required
                                min="1"
                                max="12"
                            />
                            <FormField label="Start Date" name="start_date" error={errors.start_date} required>
                                <DatePicker value={data.start_date} onChange={v => setData('start_date', v)} />
                            </FormField>
                            <FormField label="End Date" name="end_date" error={errors.end_date}>
                                <DatePicker value={data.end_date} onChange={v => setData('end_date', v)} />
                            </FormField>
                            <FormField
                                label="Billing Day of Month"
                                name="billing_day_of_month"
                                type="number"
                                value={data.billing_day_of_month}
                                onChange={e => setData('billing_day_of_month', e.target.value)}
                                error={errors.billing_day_of_month}
                                min="1"
                                max="31"
                                placeholder="e.g. 1 for 1st of month"
                            />
                            <FormField
                                label="Max Occurrences"
                                name="occurrences_limit"
                                type="number"
                                value={data.occurrences_limit}
                                onChange={e => setData('occurrences_limit', e.target.value)}
                                error={errors.occurrences_limit}
                                min="1"
                                placeholder="Leave empty for unlimited"
                            />
                        </FormSection>

                        <FormSection title="Invoice Settings" description="Settings for generated invoices">
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
                            <FormField label="Tax Behavior" name="tax_behavior" error={errors.tax_behavior} required>
                                <Select value={data.tax_behavior} onValueChange={v => setData('tax_behavior', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {taxBehaviors.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Net Terms (Days)"
                                name="net_terms_days"
                                type="number"
                                value={data.net_terms_days}
                                onChange={e => setData('net_terms_days', e.target.value)}
                                error={errors.net_terms_days}
                                required
                                min="0"
                                max="365"
                            />
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="auto_send_email"
                                    checked={data.auto_send_email}
                                    onCheckedChange={(checked) => setData('auto_send_email', checked === true)}
                                />
                                <Label htmlFor="auto_send_email">Automatically email generated invoices</Label>
                            </div>
                        </FormSection>

                        <FormSection title="Notes">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Notes for generated invoices"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/invoicing/recurring-invoices">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Recurring Invoice'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

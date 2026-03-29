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

const utilityTypes = [
    { value: 'electricity', label: 'Electricity' },
    { value: 'gas', label: 'Gas' },
    { value: 'water', label: 'Water' },
    { value: 'internet', label: 'Internet' },
    { value: 'phone', label: 'Phone' },
    { value: 'cable_tv', label: 'Cable TV' },
    { value: 'trash', label: 'Trash Collection' },
    { value: 'sewer', label: 'Sewer' },
    { value: 'other', label: 'Other' },
]

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

const paymentStatuses = [
    { value: 'pending', label: 'Pending' },
    { value: 'paid', label: 'Paid' },
    { value: 'overdue', label: 'Overdue' },
]

const usageUnits = [
    { value: 'kWh', label: 'kWh (Kilowatt hours)' },
    { value: 'therms', label: 'Therms' },
    { value: 'gallons', label: 'Gallons' },
    { value: 'cubic_meters', label: 'Cubic Meters' },
    { value: 'GB', label: 'GB (Gigabytes)' },
    { value: 'minutes', label: 'Minutes' },
    { value: 'other', label: 'Other' },
]

export default function UtilityBillCreate() {
    const { data, setData, post, processing, errors } = useForm({
        utility_type: '',
        service_provider: '',
        account_number: '',
        service_address: '',
        bill_amount: '',
        currency: 'MKD',
        usage_amount: '',
        usage_unit: '',
        rate_per_unit: '',
        bill_period_start: '',
        bill_period_end: '',
        due_date: '',
        payment_status: 'pending',
        payment_date: '',
        service_plan: '',
        contract_terms: '',
        auto_pay_enabled: false,
        budget_alert_threshold: '',
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/utility-bills')
    }

    return (
        <AppLayout>
            <Head title="Add Utility Bill" />

            <PageHeader title="Add Utility Bill" description="Add a new utility bill to track your usage and payments">
                <Button variant="outline" asChild>
                    <Link href="/utility-bills">Back to Bills</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="Utility type and provider details">
                            <FormField label="Utility Type" name="utility_type" error={errors.utility_type} required>
                                <Select value={data.utility_type} onValueChange={v => setData('utility_type', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select utility type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {utilityTypes.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Service Provider"
                                name="service_provider"
                                value={data.service_provider}
                                onChange={e => setData('service_provider', e.target.value)}
                                error={errors.service_provider}
                                required
                                placeholder="e.g., Pacific Gas & Electric"
                            />
                            <FormField
                                label="Account Number"
                                name="account_number"
                                value={data.account_number}
                                onChange={e => setData('account_number', e.target.value)}
                                error={errors.account_number}
                                placeholder="Account number"
                            />
                            <FormField label="Payment Status" name="payment_status" error={errors.payment_status} required>
                                <Select value={data.payment_status} onValueChange={v => setData('payment_status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {paymentStatuses.map(s => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Service Address"
                                name="service_address"
                                value={data.service_address}
                                onChange={e => setData('service_address', e.target.value)}
                                error={errors.service_address}
                                multiline
                                placeholder="Address where the service is provided"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Bill Details" description="Amount, usage, and rate information">
                            <FormField
                                label="Bill Amount"
                                name="bill_amount"
                                type="number"
                                value={data.bill_amount}
                                onChange={e => setData('bill_amount', e.target.value)}
                                error={errors.bill_amount}
                                required
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                            />
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
                                label="Usage Amount"
                                name="usage_amount"
                                type="number"
                                value={data.usage_amount}
                                onChange={e => setData('usage_amount', e.target.value)}
                                error={errors.usage_amount}
                                step="0.0001"
                                placeholder="0.0000"
                            />
                            <FormField label="Usage Unit" name="usage_unit" error={errors.usage_unit}>
                                <Select value={data.usage_unit} onValueChange={v => setData('usage_unit', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select unit" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {usageUnits.map(u => (
                                            <SelectItem key={u.value} value={u.value}>{u.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Rate per Unit"
                                name="rate_per_unit"
                                type="number"
                                value={data.rate_per_unit}
                                onChange={e => setData('rate_per_unit', e.target.value)}
                                error={errors.rate_per_unit}
                                step="0.000001"
                                placeholder="0.000000"
                            />
                            <FormField
                                label="Budget Alert Threshold"
                                name="budget_alert_threshold"
                                type="number"
                                value={data.budget_alert_threshold}
                                onChange={e => setData('budget_alert_threshold', e.target.value)}
                                error={errors.budget_alert_threshold}
                                step="0.01"
                                placeholder="0.00"
                            />
                        </FormSection>

                        <FormSection title="Billing Period" description="Start, end, and due dates">
                            <FormField label="Period Start" name="bill_period_start" error={errors.bill_period_start} required>
                                <DatePicker value={data.bill_period_start} onChange={v => setData('bill_period_start', v)} />
                            </FormField>
                            <FormField label="Period End" name="bill_period_end" error={errors.bill_period_end} required>
                                <DatePicker value={data.bill_period_end} onChange={v => setData('bill_period_end', v)} />
                            </FormField>
                            <FormField label="Due Date" name="due_date" error={errors.due_date} required>
                                <DatePicker value={data.due_date} onChange={v => setData('due_date', v)} />
                            </FormField>
                            <FormField label="Payment Date" name="payment_date" error={errors.payment_date}>
                                <DatePicker value={data.payment_date} onChange={v => setData('payment_date', v)} />
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Service Plan"
                                name="service_plan"
                                value={data.service_plan}
                                onChange={e => setData('service_plan', e.target.value)}
                                error={errors.service_plan}
                                placeholder="e.g., Residential Standard"
                            />
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="auto_pay_enabled"
                                    checked={data.auto_pay_enabled}
                                    onCheckedChange={(checked) => setData('auto_pay_enabled', checked === true)}
                                />
                                <Label htmlFor="auto_pay_enabled">Auto-pay enabled</Label>
                            </div>
                            <FormField
                                label="Contract Terms"
                                name="contract_terms"
                                value={data.contract_terms}
                                onChange={e => setData('contract_terms', e.target.value)}
                                error={errors.contract_terms}
                                multiline
                                placeholder="Contract details, terms, or special conditions"
                                className="sm:col-span-2"
                            />
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Additional notes or comments"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/utility-bills">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Add Utility Bill'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

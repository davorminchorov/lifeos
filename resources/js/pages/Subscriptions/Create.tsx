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

const categories = ['Entertainment', 'Software', 'Fitness', 'Storage', 'Productivity', 'Development', 'Health', 'Communication']
const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']
const billingCycles = [
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
    { value: 'custom', label: 'Custom' },
]
const paymentMethods = ['Credit Card', 'Debit Card', 'PayPal', 'Bank Transfer', 'Apple Pay', 'Google Pay']
const difficultyLevels = [
    { value: '1', label: '1 - Very Easy' },
    { value: '2', label: '2 - Easy' },
    { value: '3', label: '3 - Moderate' },
    { value: '4', label: '4 - Hard' },
    { value: '5', label: '5 - Very Hard' },
]

export default function SubscriptionCreate() {
    const { data, setData, post, processing, errors } = useForm({
        service_name: '',
        description: '',
        category: '',
        cost: '',
        currency: 'MKD',
        billing_cycle: 'monthly',
        billing_cycle_days: '',
        start_date: '',
        next_billing_date: '',
        payment_method: '',
        merchant_info: '',
        auto_renewal: true,
        cancellation_difficulty: '',
        tags: '',
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/subscriptions')
    }

    return (
        <AppLayout>
            <Head title="Create Subscription" />

            <PageHeader title="Create Subscription" description="Add a new recurring subscription">
                <Button variant="outline" asChild>
                    <Link href="/subscriptions">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="General details about the subscription">
                            <FormField
                                label="Service Name"
                                name="service_name"
                                value={data.service_name}
                                onChange={e => setData('service_name', e.target.value)}
                                error={errors.service_name}
                                required
                                placeholder="e.g. Netflix, Spotify"
                            />
                            <FormField label="Category" name="category" error={errors.category} required>
                                <Select value={data.category} onValueChange={v => setData('category', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categories.map(c => (
                                            <SelectItem key={c} value={c}>{c}</SelectItem>
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

                        <FormSection title="Billing Information" description="Cost and billing cycle details">
                            <FormField
                                label="Cost"
                                name="cost"
                                type="number"
                                value={data.cost}
                                onChange={e => setData('cost', e.target.value)}
                                error={errors.cost}
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
                            <FormField label="Billing Cycle" name="billing_cycle" error={errors.billing_cycle} required>
                                <Select value={data.billing_cycle} onValueChange={v => setData('billing_cycle', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {billingCycles.map(c => (
                                            <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            {data.billing_cycle === 'custom' ? (
                                <FormField
                                    label="Custom Days"
                                    name="billing_cycle_days"
                                    type="number"
                                    value={data.billing_cycle_days}
                                    onChange={e => setData('billing_cycle_days', e.target.value)}
                                    error={errors.billing_cycle_days}
                                    required
                                    min="1"
                                    max="365"
                                />
                            ) : null}
                        </FormSection>

                        <FormSection title="Important Dates">
                            <FormField label="Start Date" name="start_date" error={errors.start_date} required>
                                <DatePicker value={data.start_date} onChange={v => setData('start_date', v)} />
                            </FormField>
                            <FormField label="Next Billing Date" name="next_billing_date" error={errors.next_billing_date} required>
                                <DatePicker value={data.next_billing_date} onChange={v => setData('next_billing_date', v)} />
                            </FormField>
                        </FormSection>

                        <FormSection title="Payment Information">
                            <FormField label="Payment Method" name="payment_method" error={errors.payment_method}>
                                <Select value={data.payment_method} onValueChange={v => setData('payment_method', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {paymentMethods.map(m => (
                                            <SelectItem key={m} value={m}>{m}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Merchant/Company"
                                name="merchant_info"
                                value={data.merchant_info}
                                onChange={e => setData('merchant_info', e.target.value)}
                                error={errors.merchant_info}
                                placeholder="Company name"
                            />
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="auto_renewal"
                                    checked={data.auto_renewal}
                                    onCheckedChange={(checked) => setData('auto_renewal', checked === true)}
                                />
                                <Label htmlFor="auto_renewal">Auto-renewal enabled</Label>
                            </div>
                            <FormField label="Cancellation Difficulty" name="cancellation_difficulty" error={errors.cancellation_difficulty}>
                                <Select value={data.cancellation_difficulty} onValueChange={v => setData('cancellation_difficulty', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select difficulty" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {difficultyLevels.map(d => (
                                            <SelectItem key={d.value} value={d.value}>{d.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Tags"
                                name="tags"
                                value={data.tags}
                                onChange={e => setData('tags', e.target.value)}
                                error={errors.tags}
                                placeholder="Comma-separated tags"
                            />
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Additional notes"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/subscriptions">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Subscription'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

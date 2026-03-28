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

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

const paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'PayPal', 'Venmo', 'Other']

const categories = ['Personal', 'Business', 'Family', 'Friend', 'Other']

export default function IouCreate() {
    const { data, setData, post, processing, errors } = useForm({
        type: '',
        person_name: '',
        amount: '',
        currency: 'MKD',
        transaction_date: '',
        due_date: '',
        description: '',
        notes: '',
        payment_method: '',
        category: '',
        is_recurring: false,
        recurring_schedule: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/ious')
    }

    return (
        <AppLayout>
            <Head title="Create IOU" />

            <PageHeader title="Create IOU" description="Record a new IOU">
                <Button variant="outline" asChild>
                    <Link href="/ious">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="Who and what is this IOU for?">
                            <FormField label="Type" name="type" error={errors.type} required>
                                <Select value={data.type} onValueChange={v => setData('type', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="owe">I Owe (I need to pay)</SelectItem>
                                        <SelectItem value="owed">Owed to Me (Someone owes me)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Person Name"
                                name="person_name"
                                value={data.person_name}
                                onChange={e => setData('person_name', e.target.value)}
                                error={errors.person_name}
                                required
                                placeholder="Who is involved?"
                            />
                            <FormField
                                label="Description"
                                name="description"
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                error={errors.description}
                                required
                                multiline
                                placeholder="What is this for?"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Financial Details">
                            <FormField
                                label="Amount"
                                name="amount"
                                type="number"
                                value={data.amount}
                                onChange={e => setData('amount', e.target.value)}
                                error={errors.amount}
                                required
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <FormField label="Currency" name="currency" error={errors.currency}>
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
                            <FormField label="Category" name="category" error={errors.category}>
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
                        </FormSection>

                        <FormSection title="Dates">
                            <FormField label="Transaction Date" name="transaction_date" error={errors.transaction_date} required>
                                <DatePicker value={data.transaction_date} onChange={v => setData('transaction_date', v)} />
                            </FormField>
                            <FormField label="Due Date" name="due_date" error={errors.due_date}>
                                <DatePicker value={data.due_date} onChange={v => setData('due_date', v)} />
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_recurring"
                                    checked={data.is_recurring}
                                    onCheckedChange={(checked) => setData('is_recurring', checked === true)}
                                />
                                <Label htmlFor="is_recurring">This is a recurring IOU</Label>
                            </div>
                            {data.is_recurring ? (
                                <FormField
                                    label="Recurring Schedule"
                                    name="recurring_schedule"
                                    value={data.recurring_schedule}
                                    onChange={e => setData('recurring_schedule', e.target.value)}
                                    error={errors.recurring_schedule}
                                    placeholder="e.g. Monthly, Weekly"
                                />
                            ) : null}
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Additional notes"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/ious">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create IOU'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

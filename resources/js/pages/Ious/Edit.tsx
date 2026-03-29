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
import type { Iou } from '@/types/models'

interface IouEditProps {
    iou: Iou
}

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

const paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'PayPal', 'Venmo', 'Other']

const categories = ['Personal', 'Business', 'Family', 'Friend', 'Other']

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'partially_paid', label: 'Partially Paid' },
    { value: 'paid', label: 'Paid' },
    { value: 'cancelled', label: 'Cancelled' },
]

export default function IouEdit({ iou }: IouEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        type: iou.type,
        person_name: iou.person_name,
        amount: String(iou.amount),
        currency: iou.currency ?? 'MKD',
        transaction_date: iou.transaction_date ?? '',
        due_date: iou.due_date ?? '',
        description: iou.description ?? '',
        notes: iou.notes ?? '',
        status: iou.status,
        amount_paid: iou.amount_paid != null ? String(iou.amount_paid) : '0',
        payment_method: iou.payment_method ?? '',
        category: iou.category ?? '',
        is_recurring: iou.is_recurring,
        recurring_schedule: iou.recurring_schedule ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/ious/${iou.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit IOU - ${iou.person_name}`} />

            <PageHeader title={`Edit IOU - ${iou.person_name}`} description="Update IOU details">
                <Button variant="outline" asChild>
                    <Link href={`/ious/${iou.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information">
                            <FormField label="Type" name="type" error={errors.type} required>
                                <Select value={data.type} onValueChange={v => setData('type', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
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
                            />
                            <FormField label="Status" name="status" error={errors.status}>
                                <Select value={data.status} onValueChange={v => setData('status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {statusOptions.map(s => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
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
                                required
                                multiline
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
                            <FormField
                                label="Amount Paid"
                                name="amount_paid"
                                type="number"
                                value={data.amount_paid}
                                onChange={e => setData('amount_paid', e.target.value)}
                                error={errors.amount_paid}
                                min="0"
                                step="0.01"
                            />
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
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/ious/${iou.id}`}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

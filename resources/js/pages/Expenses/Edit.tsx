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
import type { Expense } from '@/types/models'

interface ExpenseEditProps {
    expense: Expense
}

const categories = ['Food & Dining', 'Transportation', 'Shopping', 'Entertainment', 'Bills & Utilities', 'Healthcare', 'Travel', 'Other']
const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']
const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'credit_card', label: 'Credit Card' },
    { value: 'debit_card', label: 'Debit Card' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'digital_wallet', label: 'Digital Wallet' },
]
const expenseTypes = [
    { value: 'personal', label: 'Personal' },
    { value: 'business', label: 'Business' },
]
const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'reimbursed', label: 'Reimbursed' },
]

export default function ExpenseEdit({ expense }: ExpenseEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        amount: String(expense.amount),
        currency: expense.currency ?? 'MKD',
        category: expense.category,
        subcategory: expense.subcategory ?? '',
        expense_date: expense.expense_date,
        description: expense.description ?? '',
        merchant: expense.merchant ?? '',
        payment_method: expense.payment_method ?? '',
        expense_type: expense.expense_type ?? 'personal',
        location: expense.location ?? '',
        tags: Array.isArray(expense.tags) ? expense.tags.join(', ') : '',
        notes: expense.notes ?? '',
        is_tax_deductible: expense.is_tax_deductible,
        is_recurring: expense.is_recurring,
        status: expense.status ?? 'pending',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/expenses/${expense.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit Expense`} />

            <PageHeader title="Edit Expense" description={expense.description ?? 'Update expense details'}>
                <Button variant="outline" asChild>
                    <Link href={`/expenses/${expense.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Details" description="Amount, date, and category">
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
                            <FormField label="Date" name="expense_date" error={errors.expense_date} required>
                                <DatePicker value={data.expense_date} onChange={v => setData('expense_date', v)} />
                            </FormField>
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
                                label="Subcategory"
                                name="subcategory"
                                value={data.subcategory}
                                onChange={e => setData('subcategory', e.target.value)}
                                error={errors.subcategory}
                                placeholder="Optional subcategory"
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
                                placeholder="Brief description of the expense"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Payment Information" description="How and where the expense was paid">
                            <FormField label="Payment Method" name="payment_method" error={errors.payment_method}>
                                <Select value={data.payment_method} onValueChange={v => setData('payment_method', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {paymentMethods.map(m => (
                                            <SelectItem key={m.value} value={m.value}>{m.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Type" name="expense_type" error={errors.expense_type}>
                                <Select value={data.expense_type} onValueChange={v => setData('expense_type', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {expenseTypes.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Merchant"
                                name="merchant"
                                value={data.merchant}
                                onChange={e => setData('merchant', e.target.value)}
                                error={errors.merchant}
                                placeholder="Store or company name"
                            />
                            <FormField
                                label="Location"
                                name="location"
                                value={data.location}
                                onChange={e => setData('location', e.target.value)}
                                error={errors.location}
                                placeholder="City, address, or general location"
                            />
                        </FormSection>

                        <FormSection title="Additional Information">
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_tax_deductible"
                                    checked={data.is_tax_deductible}
                                    onCheckedChange={(checked) => setData('is_tax_deductible', checked === true)}
                                />
                                <Label htmlFor="is_tax_deductible">Tax Deductible</Label>
                            </div>
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_recurring"
                                    checked={data.is_recurring}
                                    onCheckedChange={(checked) => setData('is_recurring', checked === true)}
                                />
                                <Label htmlFor="is_recurring">Recurring Expense</Label>
                            </div>
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
                                placeholder="Any additional notes"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/expenses/${expense.id}`}>Cancel</Link>
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

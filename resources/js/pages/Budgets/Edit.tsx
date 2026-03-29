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
import type { Budget } from '@/types/models'

interface BudgetEditProps {
    budget: Budget
    categories: string[]
    currencies: Array<{ code: string; name: string }> | string[]
}

const periodOptions = [
    { value: 'monthly', label: 'Monthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'yearly', label: 'Yearly' },
    { value: 'custom', label: 'Custom' },
]

export default function BudgetEdit({ budget, categories, currencies }: BudgetEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        category: budget.category,
        budget_period: budget.budget_period,
        amount: String(budget.amount),
        currency: budget.currency ?? 'MKD',
        start_date: budget.start_date,
        end_date: budget.end_date,
        is_active: budget.is_active,
        rollover_unused: budget.rollover_unused,
        alert_threshold: String(budget.alert_threshold),
        notes: budget.notes ?? '',
    })

    const isCustomPeriod = data.budget_period === 'custom'

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/budgets/${budget.id}`)
    }

    const currencyOptions = currencies.map(c =>
        typeof c === 'string' ? { code: c, name: c } : c,
    )

    return (
        <AppLayout>
            <Head title={`Edit ${budget.category} Budget`} />

            <PageHeader title={`Edit ${budget.category} Budget`} description="Update budget details">
                <Button variant="outline" asChild>
                    <Link href={`/budgets/${budget.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Budget Details" description="Define what this budget covers">
                            <FormField label="Category" name="category" error={errors.category} required>
                                {categories.length > 0 ? (
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
                                ) : (
                                    <FormField
                                        label=""
                                        name="category_input"
                                        value={data.category}
                                        onChange={e => setData('category', e.target.value)}
                                        error={errors.category}
                                        placeholder="Enter category name"
                                    />
                                )}
                            </FormField>
                            <FormField label="Budget Period" name="budget_period" error={errors.budget_period} required>
                                <Select value={data.budget_period} onValueChange={v => setData('budget_period', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {periodOptions.map(p => (
                                            <SelectItem key={p.value} value={p.value}>{p.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
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
                                        {currencyOptions.map(c => (
                                            <SelectItem key={c.code} value={c.code}>{c.code} - {c.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        {isCustomPeriod ? (
                            <FormSection title="Custom Period Dates" description="Define the start and end dates for this budget">
                                <FormField label="Start Date" name="start_date" error={errors.start_date} required>
                                    <DatePicker value={data.start_date} onChange={v => setData('start_date', v)} />
                                </FormField>
                                <FormField label="End Date" name="end_date" error={errors.end_date} required>
                                    <DatePicker value={data.end_date} onChange={v => setData('end_date', v)} />
                                </FormField>
                            </FormSection>
                        ) : null}

                        <FormSection title="Settings" description="Configure budget behavior">
                            <FormField
                                label="Alert Threshold (%)"
                                name="alert_threshold"
                                type="number"
                                value={data.alert_threshold}
                                onChange={e => setData('alert_threshold', e.target.value)}
                                error={errors.alert_threshold}
                                min="1"
                                max="100"
                                placeholder="80"
                            />
                            <div className="space-y-3">
                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked === true)}
                                    />
                                    <Label htmlFor="is_active">Budget is active</Label>
                                </div>
                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="rollover_unused"
                                        checked={data.rollover_unused}
                                        onCheckedChange={(checked) => setData('rollover_unused', checked === true)}
                                    />
                                    <Label htmlFor="rollover_unused">Roll over unused budget to next period</Label>
                                </div>
                            </div>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Optional notes about this budget"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/budgets/${budget.id}`}>Cancel</Link>
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

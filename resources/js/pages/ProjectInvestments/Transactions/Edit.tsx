import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import type { ProjectInvestment, ProjectInvestmentTransaction } from '@/types/models'

interface TransactionEditProps {
    transaction: ProjectInvestmentTransaction
    projectInvestment: ProjectInvestment
}

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

export default function TransactionEdit({ transaction, projectInvestment }: TransactionEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        amount: String(transaction.amount),
        currency: transaction.currency ?? 'USD',
        transaction_date: transaction.transaction_date,
        notes: transaction.notes ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/project-investment-transactions/${transaction.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit Transaction - ${projectInvestment.name}`} />

            <PageHeader title="Edit Transaction" description={`For ${projectInvestment.name}`}>
                <Button variant="outline" asChild>
                    <Link href={`/project-investments/${projectInvestment.id}`}>Back to Investment</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Transaction Details">
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
                            <FormField label="Transaction Date" name="transaction_date" error={errors.transaction_date} required>
                                <DatePicker value={data.transaction_date} onChange={v => setData('transaction_date', v)} />
                            </FormField>
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Optional notes"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/project-investments/${projectInvestment.id}`}>Cancel</Link>
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

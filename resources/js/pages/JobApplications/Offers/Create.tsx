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
import type { JobApplication } from '@/types/models'
import type { OfferStatus } from '@/types/enums'

interface OfferCreateProps {
    application: JobApplication
    statuses: Array<{ value: OfferStatus; label: string }>
    currencies: string[]
}

export default function OfferCreate({ application, statuses, currencies }: OfferCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        base_salary: '',
        bonus: '',
        equity: '',
        currency: application.currency ?? 'MKD',
        benefits: '',
        start_date: '',
        decision_deadline: '',
        status: 'pending',
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post(`/job-applications/${application.id}/offers`)
    }

    return (
        <AppLayout>
            <Head title={`Record Offer - ${application.company_name}`} />

            <PageHeader
                title="Record Offer"
                description={`${application.company_name} - ${application.job_title}`}
            >
                <Button variant="outline" asChild>
                    <Link href={`/job-applications/${application.id}`}>Back to Application</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Compensation" description="Salary and benefits details">
                            <FormField
                                label="Base Salary"
                                name="base_salary"
                                type="number"
                                value={data.base_salary}
                                onChange={e => setData('base_salary', e.target.value)}
                                error={errors.base_salary}
                                required
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <FormField
                                label="Bonus"
                                name="bonus"
                                type="number"
                                value={data.bonus}
                                onChange={e => setData('bonus', e.target.value)}
                                error={errors.bonus}
                                min="0"
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
                                label="Equity"
                                name="equity"
                                value={data.equity}
                                onChange={e => setData('equity', e.target.value)}
                                error={errors.equity}
                                placeholder="e.g. 0.1% vesting over 4 years"
                            />
                            <FormField
                                label="Benefits"
                                name="benefits"
                                value={data.benefits}
                                onChange={e => setData('benefits', e.target.value)}
                                error={errors.benefits}
                                multiline
                                placeholder="Health insurance, PTO, etc."
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Timeline" description="Important dates">
                            <FormField label="Start Date" name="start_date" error={errors.start_date}>
                                <DatePicker value={data.start_date} onChange={v => setData('start_date', v)} />
                            </FormField>
                            <FormField label="Decision Deadline" name="decision_deadline" error={errors.decision_deadline}>
                                <DatePicker value={data.decision_deadline} onChange={v => setData('decision_deadline', v)} />
                            </FormField>
                            <FormField label="Status" name="status" error={errors.status} required>
                                <Select value={data.status} onValueChange={v => setData('status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {statuses.map(s => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Notes">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Additional notes about the offer"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/job-applications/${application.id}`}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Recording...' : 'Record Offer'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

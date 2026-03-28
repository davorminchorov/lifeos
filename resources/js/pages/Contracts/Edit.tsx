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
import type { Contract } from '@/types/models'

interface ContractEditProps {
    contract: Contract
}

const contractTypes = ['Employment', 'Freelance', 'Service', 'Lease', 'Insurance', 'Subscription', 'Other']

const performanceRatings = [
    { value: '1', label: '1 - Poor' },
    { value: '2', label: '2 - Below Average' },
    { value: '3', label: '3 - Average' },
    { value: '4', label: '4 - Good' },
    { value: '5', label: '5 - Excellent' },
]

export default function ContractEdit({ contract }: ContractEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        title: contract.title,
        contract_type: contract.contract_type ?? '',
        counterparty: contract.counterparty ?? '',
        start_date: contract.start_date ?? '',
        end_date: contract.end_date ?? '',
        notice_period_days: contract.notice_period_days != null ? String(contract.notice_period_days) : '',
        auto_renewal: contract.auto_renewal,
        contract_value: contract.contract_value != null ? String(contract.contract_value) : '',
        payment_terms: contract.payment_terms ?? '',
        key_obligations: contract.key_obligations ?? '',
        penalties: contract.penalties ?? '',
        termination_clauses: contract.termination_clauses ?? '',
        performance_rating: contract.performance_rating != null ? String(contract.performance_rating) : '',
        notes: contract.notes ?? '',
        status: contract.status,
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/contracts/${contract.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${contract.title}`} />

            <PageHeader title={`Edit ${contract.title}`} description="Update contract details">
                <Button variant="outline" asChild>
                    <Link href={`/contracts/${contract.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="General details about the contract">
                            <FormField
                                label="Title"
                                name="title"
                                value={data.title}
                                onChange={e => setData('title', e.target.value)}
                                error={errors.title}
                                required
                                placeholder="e.g. Office Lease Agreement"
                            />
                            <FormField label="Contract Type" name="contract_type" error={errors.contract_type} required>
                                <Select value={data.contract_type} onValueChange={v => setData('contract_type', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {contractTypes.map(t => (
                                            <SelectItem key={t} value={t}>{t}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Counterparty"
                                name="counterparty"
                                value={data.counterparty}
                                onChange={e => setData('counterparty', e.target.value)}
                                error={errors.counterparty}
                                required
                                placeholder="Company or person name"
                            />
                            <FormField label="Status" name="status" error={errors.status}>
                                <Select value={data.status} onValueChange={v => setData('status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="expired">Expired</SelectItem>
                                        <SelectItem value="terminated">Terminated</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Dates & Renewal">
                            <FormField label="Start Date" name="start_date" error={errors.start_date} required>
                                <DatePicker value={data.start_date} onChange={v => setData('start_date', v)} />
                            </FormField>
                            <FormField label="End Date" name="end_date" error={errors.end_date}>
                                <DatePicker value={data.end_date} onChange={v => setData('end_date', v)} />
                            </FormField>
                            <FormField
                                label="Notice Period (days)"
                                name="notice_period_days"
                                type="number"
                                value={data.notice_period_days}
                                onChange={e => setData('notice_period_days', e.target.value)}
                                error={errors.notice_period_days}
                                min="1"
                                max="365"
                                placeholder="e.g. 30"
                            />
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="auto_renewal"
                                    checked={data.auto_renewal}
                                    onCheckedChange={(checked) => setData('auto_renewal', checked === true)}
                                />
                                <Label htmlFor="auto_renewal">Auto-renewal enabled</Label>
                            </div>
                        </FormSection>

                        <FormSection title="Financial Details">
                            <FormField
                                label="Contract Value"
                                name="contract_value"
                                type="number"
                                value={data.contract_value}
                                onChange={e => setData('contract_value', e.target.value)}
                                error={errors.contract_value}
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <FormField
                                label="Payment Terms"
                                name="payment_terms"
                                value={data.payment_terms}
                                onChange={e => setData('payment_terms', e.target.value)}
                                error={errors.payment_terms}
                                placeholder="e.g. Net 30"
                            />
                        </FormSection>

                        <FormSection title="Terms & Obligations">
                            <FormField
                                label="Key Obligations"
                                name="key_obligations"
                                value={data.key_obligations}
                                onChange={e => setData('key_obligations', e.target.value)}
                                error={errors.key_obligations}
                                multiline
                                placeholder="Main obligations under this contract"
                                className="sm:col-span-2"
                            />
                            <FormField
                                label="Penalties"
                                name="penalties"
                                value={data.penalties}
                                onChange={e => setData('penalties', e.target.value)}
                                error={errors.penalties}
                                multiline
                                placeholder="Penalty clauses"
                            />
                            <FormField
                                label="Termination Clauses"
                                name="termination_clauses"
                                value={data.termination_clauses}
                                onChange={e => setData('termination_clauses', e.target.value)}
                                error={errors.termination_clauses}
                                multiline
                                placeholder="Termination conditions"
                            />
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField label="Performance Rating" name="performance_rating" error={errors.performance_rating}>
                                <Select value={data.performance_rating} onValueChange={v => setData('performance_rating', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select rating" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {performanceRatings.map(r => (
                                            <SelectItem key={r.value} value={r.value}>{r.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
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
                                <Link href={`/contracts/${contract.id}`}>Cancel</Link>
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

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

const stages = [
    { value: 'idea', label: 'Idea' },
    { value: 'prototype', label: 'Prototype' },
    { value: 'mvp', label: 'MVP' },
    { value: 'growth', label: 'Growth' },
    { value: 'mature', label: 'Mature' },
]

const businessModels = [
    { value: 'subscription', label: 'Subscription' },
    { value: 'ads', label: 'Ads' },
    { value: 'one-time', label: 'One-time' },
    { value: 'freemium', label: 'Freemium' },
]

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

const statuses = [
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'sold', label: 'Sold' },
    { value: 'abandoned', label: 'Abandoned' },
]

export default function ProjectInvestmentCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        project_type: '',
        stage: '',
        business_model: '',
        website_url: '',
        repository_url: '',
        equity_percentage: '',
        investment_amount: '',
        currency: 'USD',
        current_value: '',
        start_date: '',
        end_date: '',
        status: 'active',
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/project-investments')
    }

    return (
        <AppLayout>
            <Head title="Create Project Investment" />

            <PageHeader title="Create Project Investment" description="Add a new project investment">
                <Button variant="outline" asChild>
                    <Link href="/project-investments">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Project Details" description="Basic information about the project">
                            <FormField
                                label="Project Name"
                                name="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="e.g. My SaaS Project"
                            />
                            <FormField
                                label="Project Type"
                                name="project_type"
                                value={data.project_type}
                                onChange={e => setData('project_type', e.target.value)}
                                error={errors.project_type}
                                placeholder="e.g. SaaS, Mobile App"
                            />
                            <FormField label="Stage" name="stage" error={errors.stage}>
                                <Select value={data.stage} onValueChange={v => setData('stage', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select stage" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {stages.map(s => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Business Model" name="business_model" error={errors.business_model}>
                                <Select value={data.business_model} onValueChange={v => setData('business_model', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select model" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {businessModels.map(b => (
                                            <SelectItem key={b.value} value={b.value}>{b.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Status" name="status" error={errors.status}>
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

                        <FormSection title="URLs">
                            <FormField
                                label="Website URL"
                                name="website_url"
                                value={data.website_url}
                                onChange={e => setData('website_url', e.target.value)}
                                error={errors.website_url}
                                placeholder="https://example.com"
                            />
                            <FormField
                                label="Repository URL"
                                name="repository_url"
                                value={data.repository_url}
                                onChange={e => setData('repository_url', e.target.value)}
                                error={errors.repository_url}
                                placeholder="https://github.com/user/repo"
                            />
                        </FormSection>

                        <FormSection title="Financial Details" description="Investment amounts and equity">
                            <FormField
                                label="Investment Amount"
                                name="investment_amount"
                                type="number"
                                value={data.investment_amount}
                                onChange={e => setData('investment_amount', e.target.value)}
                                error={errors.investment_amount}
                                required
                                min="0"
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
                            <FormField
                                label="Current Value"
                                name="current_value"
                                type="number"
                                value={data.current_value}
                                onChange={e => setData('current_value', e.target.value)}
                                error={errors.current_value}
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <FormField
                                label="Equity Percentage"
                                name="equity_percentage"
                                type="number"
                                value={data.equity_percentage}
                                onChange={e => setData('equity_percentage', e.target.value)}
                                error={errors.equity_percentage}
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0.00"
                            />
                        </FormSection>

                        <FormSection title="Dates">
                            <FormField label="Start Date" name="start_date" error={errors.start_date}>
                                <DatePicker value={data.start_date} onChange={v => setData('start_date', v)} />
                            </FormField>
                            <FormField label="End Date" name="end_date" error={errors.end_date}>
                                <DatePicker value={data.end_date} onChange={v => setData('end_date', v)} />
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
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
                                <Link href="/project-investments">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Investment'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

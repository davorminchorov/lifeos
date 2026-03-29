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
import type { JobApplication } from '@/types/models'
import type { ApplicationStatus, ApplicationSource } from '@/types/enums'

type FormData = {
    company_name: string
    company_website: string
    job_title: string
    job_description: string
    job_url: string
    location: string
    remote: boolean
    salary_min: string
    salary_max: string
    currency: string
    status: string
    source: string
    applied_at: string
    next_action_at: string
    priority: string
    contact_name: string
    contact_email: string
    contact_phone: string
    notes: string
    tags: string
}

interface JobApplicationEditProps {
    application: JobApplication
    statuses: Array<{ value: ApplicationStatus; label: string }>
    sources: Array<{ value: ApplicationSource; label: string }>
    currencies: string[]
}

export default function JobApplicationEdit({ application, statuses, sources, currencies }: JobApplicationEditProps) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        company_name: application.company_name,
        company_website: application.company_website ?? '',
        job_title: application.job_title,
        job_description: application.job_description ?? '',
        job_url: application.job_url ?? '',
        location: application.location ?? '',
        remote: application.remote,
        salary_min: application.salary_min ? String(application.salary_min) : '',
        salary_max: application.salary_max ? String(application.salary_max) : '',
        currency: application.currency ?? 'MKD',
        status: application.status as string,
        source: (application.source ?? '') as string,
        applied_at: application.applied_at ?? '',
        next_action_at: application.next_action_at ?? '',
        priority: application.priority ? String(application.priority) : '2',
        contact_name: application.contact_name ?? '',
        contact_email: application.contact_email ?? '',
        contact_phone: application.contact_phone ?? '',
        notes: application.notes ?? '',
        tags: Array.isArray(application.tags) ? application.tags.join(', ') : '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/job-applications/${application.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit - ${application.company_name}`} />

            <PageHeader
                title={`Edit Application`}
                description={`${application.company_name} - ${application.job_title}`}
            >
                <Button variant="outline" asChild>
                    <Link href={`/job-applications/${application.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Company Information" description="Details about the company and position">
                            <FormField
                                label="Company Name"
                                name="company_name"
                                value={data.company_name}
                                onChange={e => setData('company_name', e.target.value)}
                                error={errors.company_name}
                                required
                                placeholder="e.g. Google, Microsoft"
                            />
                            <FormField
                                label="Company Website"
                                name="company_website"
                                type="url"
                                value={data.company_website}
                                onChange={e => setData('company_website', e.target.value)}
                                error={errors.company_website}
                                placeholder="https://company.com"
                            />
                            <FormField
                                label="Job Title"
                                name="job_title"
                                value={data.job_title}
                                onChange={e => setData('job_title', e.target.value)}
                                error={errors.job_title}
                                required
                                placeholder="e.g. Senior Software Engineer"
                            />
                            <FormField
                                label="Job URL"
                                name="job_url"
                                type="url"
                                value={data.job_url}
                                onChange={e => setData('job_url', e.target.value)}
                                error={errors.job_url}
                                placeholder="Link to job posting"
                            />
                            <FormField
                                label="Job Description"
                                name="job_description"
                                value={data.job_description}
                                onChange={e => setData('job_description', e.target.value)}
                                error={errors.job_description}
                                multiline
                                placeholder="Paste job description here"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Location" description="Where is this job based?">
                            <FormField
                                label="Location"
                                name="location"
                                value={data.location}
                                onChange={e => setData('location', e.target.value)}
                                error={errors.location}
                                placeholder="e.g. San Francisco, CA"
                            />
                            <div className="flex items-center gap-3 pt-6">
                                <Checkbox
                                    id="remote"
                                    checked={data.remote}
                                    onCheckedChange={(checked) => setData('remote', checked === true)}
                                />
                                <Label htmlFor="remote">Remote position</Label>
                            </div>
                        </FormSection>

                        <FormSection title="Compensation" description="Salary range and currency">
                            <FormField
                                label="Minimum Salary"
                                name="salary_min"
                                type="number"
                                value={data.salary_min}
                                onChange={e => setData('salary_min', e.target.value)}
                                error={errors.salary_min}
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <FormField
                                label="Maximum Salary"
                                name="salary_max"
                                type="number"
                                value={data.salary_max}
                                onChange={e => setData('salary_max', e.target.value)}
                                error={errors.salary_max}
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
                        </FormSection>

                        <FormSection title="Application Details" description="Status, source, and timeline">
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
                            <FormField label="Source" name="source" error={errors.source}>
                                <Select value={data.source} onValueChange={v => setData('source', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select source" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {sources.map(s => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Applied Date" name="applied_at" error={errors.applied_at}>
                                <DatePicker value={data.applied_at} onChange={v => setData('applied_at', v)} />
                            </FormField>
                            <FormField label="Next Action Date" name="next_action_at" error={errors.next_action_at}>
                                <DatePicker value={data.next_action_at} onChange={v => setData('next_action_at', v)} />
                            </FormField>
                            <FormField label="Priority" name="priority" error={errors.priority}>
                                <Select value={data.priority} onValueChange={v => setData('priority', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">1 - Low</SelectItem>
                                        <SelectItem value="2">2 - Medium</SelectItem>
                                        <SelectItem value="3">3 - High</SelectItem>
                                        <SelectItem value="4">4 - Critical</SelectItem>
                                        <SelectItem value="5">5 - Urgent</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Contact Information" description="Recruiter or hiring manager details">
                            <FormField
                                label="Contact Name"
                                name="contact_name"
                                value={data.contact_name}
                                onChange={e => setData('contact_name', e.target.value)}
                                error={errors.contact_name}
                                placeholder="Recruiter or hiring manager"
                            />
                            <FormField
                                label="Contact Email"
                                name="contact_email"
                                type="email"
                                value={data.contact_email}
                                onChange={e => setData('contact_email', e.target.value)}
                                error={errors.contact_email}
                                placeholder="contact@company.com"
                            />
                            <FormField
                                label="Contact Phone"
                                name="contact_phone"
                                type="tel"
                                value={data.contact_phone}
                                onChange={e => setData('contact_phone', e.target.value)}
                                error={errors.contact_phone}
                                placeholder="+1 (555) 000-0000"
                            />
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
                                placeholder="Additional notes about this application"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/job-applications/${application.id}`}>Cancel</Link>
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

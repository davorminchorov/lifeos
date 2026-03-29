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
import type { InterviewType, InterviewOutcome } from '@/types/enums'

interface InterviewCreateProps {
    application: JobApplication
    types: Array<{ value: InterviewType; label: string }>
    outcomes: Array<{ value: InterviewOutcome; label: string }>
}

export default function InterviewCreate({ application, types, outcomes }: InterviewCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        type: 'phone',
        scheduled_at: '',
        duration_minutes: '60',
        location: '',
        video_link: '',
        interviewer_name: '',
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post(`/job-applications/${application.id}/interviews`)
    }

    return (
        <AppLayout>
            <Head title={`Schedule Interview - ${application.company_name}`} />

            <PageHeader
                title="Schedule Interview"
                description={`${application.company_name} - ${application.job_title}`}
            >
                <Button variant="outline" asChild>
                    <Link href={`/job-applications/${application.id}`}>Back to Application</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Interview Details" description="Type and scheduling information">
                            <FormField label="Interview Type" name="type" error={errors.type} required>
                                <Select value={data.type} onValueChange={v => setData('type', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {types.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField label="Scheduled Date & Time" name="scheduled_at" error={errors.scheduled_at} required>
                                <DatePicker value={data.scheduled_at} onChange={v => setData('scheduled_at', v)} />
                            </FormField>
                            <FormField
                                label="Duration (minutes)"
                                name="duration_minutes"
                                type="number"
                                value={data.duration_minutes}
                                onChange={e => setData('duration_minutes', e.target.value)}
                                error={errors.duration_minutes}
                                min="15"
                                max="480"
                            />
                        </FormSection>

                        <FormSection title="Location" description="Where the interview will take place">
                            <FormField
                                label="Location"
                                name="location"
                                value={data.location}
                                onChange={e => setData('location', e.target.value)}
                                error={errors.location}
                                placeholder="Office address or meeting room"
                            />
                            <FormField
                                label="Video Link"
                                name="video_link"
                                type="url"
                                value={data.video_link}
                                onChange={e => setData('video_link', e.target.value)}
                                error={errors.video_link}
                                placeholder="https://zoom.us/j/..."
                            />
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Interviewer Name"
                                name="interviewer_name"
                                value={data.interviewer_name}
                                onChange={e => setData('interviewer_name', e.target.value)}
                                error={errors.interviewer_name}
                                placeholder="Name of the interviewer"
                            />
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Preparation notes, topics to discuss"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/job-applications/${application.id}`}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Scheduling...' : 'Schedule Interview'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

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
import type { JobApplication, JobApplicationInterview } from '@/types/models'
import type { InterviewType, InterviewOutcome } from '@/types/enums'

type FormData = {
    type: string
    scheduled_at: string
    duration_minutes: string
    location: string
    video_link: string
    interviewer_name: string
    notes: string
    feedback: string
    outcome: string
    completed: boolean
}

interface InterviewEditProps {
    application: JobApplication
    interview: JobApplicationInterview
    types: Array<{ value: InterviewType; label: string }>
    outcomes: Array<{ value: InterviewOutcome; label: string }>
}

export default function InterviewEdit({ application, interview, types, outcomes }: InterviewEditProps) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        type: interview.type as string,
        scheduled_at: interview.scheduled_at,
        duration_minutes: interview.duration_minutes ? String(interview.duration_minutes) : '60',
        location: interview.location ?? '',
        video_link: interview.video_link ?? '',
        interviewer_name: interview.interviewer_name ?? '',
        notes: interview.notes ?? '',
        feedback: interview.feedback ?? '',
        outcome: (interview.outcome ?? '') as string,
        completed: interview.completed,
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/job-applications/${application.id}/interviews/${interview.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit Interview - ${application.company_name}`} />

            <PageHeader
                title="Edit Interview"
                description={`${application.company_name} - ${application.job_title}`}
            >
                <Button variant="outline" asChild>
                    <Link href={`/job-applications/${application.id}/interviews/${interview.id}`}>Back to Interview</Link>
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

                        <FormSection title="Interview Results" description="Feedback and outcome">
                            <FormField
                                label="Interviewer Name"
                                name="interviewer_name"
                                value={data.interviewer_name}
                                onChange={e => setData('interviewer_name', e.target.value)}
                                error={errors.interviewer_name}
                                placeholder="Name of the interviewer"
                            />
                            <FormField label="Outcome" name="outcome" error={errors.outcome}>
                                <Select value={data.outcome} onValueChange={v => setData('outcome', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select outcome" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {outcomes.map(o => (
                                            <SelectItem key={o.value} value={o.value}>{o.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="completed"
                                    checked={data.completed}
                                    onCheckedChange={(checked) => setData('completed', checked === true)}
                                />
                                <Label htmlFor="completed">Interview completed</Label>
                            </div>
                            <FormField
                                label="Feedback"
                                name="feedback"
                                value={data.feedback}
                                onChange={e => setData('feedback', e.target.value)}
                                error={errors.feedback}
                                multiline
                                placeholder="How did the interview go?"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Notes">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Preparation notes, topics discussed"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/job-applications/${application.id}/interviews/${interview.id}`}>Cancel</Link>
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

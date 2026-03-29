import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Pencil, Trash2, ArrowLeft, CheckCircle2, ExternalLink } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { JobApplication, JobApplicationInterview } from '@/types/models'

interface InterviewShowProps {
    application: JobApplication
    interview: JobApplicationInterview
}

export default function InterviewShow({ application, interview }: InterviewShowProps) {
    const [confirmAction, setConfirmAction] = useState<'complete' | 'delete' | null>(null)

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'delete') {
            router.delete(`/job-applications/${application.id}/interviews/${interview.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else {
            router.patch(`/job-applications/${application.id}/interviews/${interview.id}/complete`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, application.id, interview.id])

    return (
        <AppLayout>
            <Head title={`Interview - ${application.company_name}`} />

            <PageHeader
                title={`${interview.type.charAt(0).toUpperCase() + interview.type.slice(1)} Interview`}
                description={`${application.company_name} - ${application.job_title}`}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/job-applications/${application.id}`}>
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/job-applications/${application.id}/interviews/${interview.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {!interview.completed ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('complete')}>
                        <CheckCircle2 className="mr-2 h-4 w-4" /> Mark Complete
                    </Button>
                ) : null}
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Interview Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Type</p>
                                    <p className="font-medium capitalize">{interview.type}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    {interview.completed ? (
                                        <StatusBadge status="completed" />
                                    ) : (
                                        <StatusBadge status="upcoming" variant="info" />
                                    )}
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Scheduled</p>
                                    <p className="font-medium">{formatDate(interview.scheduled_at)}</p>
                                </div>
                                {interview.duration_minutes ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Duration</p>
                                        <p className="font-medium">{interview.duration_minutes} minutes</p>
                                    </div>
                                ) : null}
                                {interview.interviewer_name ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Interviewer</p>
                                        <p className="font-medium">{interview.interviewer_name}</p>
                                    </div>
                                ) : null}
                                {interview.outcome ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Outcome</p>
                                        <StatusBadge status={interview.outcome} />
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    {interview.location || interview.video_link ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Location</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {interview.location ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Address</p>
                                        <p className="font-medium">{interview.location}</p>
                                    </div>
                                ) : null}
                                {interview.video_link ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Video Link</p>
                                        <a
                                            href={interview.video_link}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                        >
                                            Join Meeting <ExternalLink className="h-3 w-3" />
                                        </a>
                                    </div>
                                ) : null}
                            </CardContent>
                        </Card>
                    ) : null}

                    {interview.feedback ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Feedback</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap text-sm">{interview.feedback}</p>
                            </CardContent>
                        </Card>
                    ) : null}

                    {interview.notes ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap text-sm">{interview.notes}</p>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Application</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div>
                                <p className="text-sm text-muted-foreground">Company</p>
                                <Link
                                    href={`/job-applications/${application.id}`}
                                    className="font-medium text-primary hover:underline"
                                >
                                    {application.company_name}
                                </Link>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Position</p>
                                <p className="font-medium">{application.job_title}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Application Status</p>
                                <StatusBadge status={application.status} />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmAction === 'delete' ? 'Delete Interview' : 'Complete Interview'}
                description={
                    confirmAction === 'delete'
                        ? 'Are you sure you want to delete this interview? This action cannot be undone.'
                        : 'Mark this interview as completed?'
                }
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction === 'delete' ? 'Delete' : 'Mark Complete'}
                variant={confirmAction === 'delete' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}

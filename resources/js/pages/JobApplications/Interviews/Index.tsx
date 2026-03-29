import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, Plus } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { JobApplication, JobApplicationInterview } from '@/types/models'

interface InterviewIndexProps {
    application: JobApplication
    interviews: JobApplicationInterview[]
}

export default function InterviewIndex({ application, interviews }: InterviewIndexProps) {
    return (
        <AppLayout>
            <Head title={`Interviews - ${application.company_name}`} />

            <div className="space-y-6">
                <PageHeader
                    title={`Interviews for ${application.company_name}`}
                    description={application.job_title}
                >
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/job-applications/${application.id}`}>
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Back
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={`/job-applications/${application.id}/interviews/create`}>
                                <Plus className="mr-2 h-4 w-4" />
                                Schedule Interview
                            </Link>
                        </Button>
                    </div>
                </PageHeader>

                {interviews.length === 0 ? (
                    <Card>
                        <CardContent className="py-8 text-center text-muted-foreground">
                            No interviews scheduled yet.
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-4">
                        {interviews.map((interview) => (
                            <Card key={interview.id}>
                                <CardHeader>
                                    <CardTitle className="text-lg">
                                        <Link
                                            href={`/job-applications/${application.id}/interviews/${interview.id}`}
                                            className="hover:underline"
                                        >
                                            {interview.type} Interview
                                            {interview.interviewer_name && ` with ${interview.interviewer_name}`}
                                        </Link>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        {interview.scheduled_at && (
                                            <span>{formatDate(interview.scheduled_at)}</span>
                                        )}
                                        {interview.location && <span>{interview.location}</span>}
                                        {interview.completed && (
                                            <span className="text-green-600 font-medium">Completed</span>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    )
}

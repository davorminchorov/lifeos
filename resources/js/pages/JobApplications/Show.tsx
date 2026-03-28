import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Pencil,
    Trash2,
    ArrowLeft,
    Archive,
    ArchiveRestore,
    ExternalLink,
    Plus,
    CalendarDays,
    DollarSign,
    CheckCircle2,
} from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { JobApplication, JobApplicationInterview, JobApplicationOffer, JobApplicationStatusHistory } from '@/types/models'

interface JobApplicationShowProps {
    application: JobApplication & {
        status_histories?: JobApplicationStatusHistory[]
        interviews?: JobApplicationInterview[]
        offer?: JobApplicationOffer | null
    }
}

export default function JobApplicationShow({ application }: JobApplicationShowProps) {
    const [confirmAction, setConfirmAction] = useState<'archive' | 'unarchive' | 'delete' | null>(null)

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'delete') {
            router.delete(`/job-applications/${application.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'archive') {
            router.patch(`/job-applications/${application.id}/archive`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else {
            router.patch(`/job-applications/${application.id}/unarchive`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, application.id])

    const confirmTitle = confirmAction
        ? `${confirmAction.charAt(0).toUpperCase() + confirmAction.slice(1)} Application`
        : ''

    const confirmDescription = confirmAction === 'delete'
        ? 'Are you sure you want to delete this application? This action cannot be undone.'
        : confirmAction === 'archive'
            ? 'Are you sure you want to archive this application?'
            : 'Are you sure you want to unarchive this application?'

    const interviews = application.interviews ?? []
    const statusHistories = application.status_histories ?? []
    const offer = application.offer
    const tags = Array.isArray(application.tags) ? application.tags : []
    const currency = application.currency ?? 'USD'

    return (
        <AppLayout>
            <Head title={`${application.company_name} - ${application.job_title}`} />

            <PageHeader
                title={application.company_name}
                description={application.job_title}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href="/job-applications">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/job-applications/${application.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {application.archived_at ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('unarchive')}>
                        <ArchiveRestore className="mr-2 h-4 w-4" /> Unarchive
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('archive')}>
                        <Archive className="mr-2 h-4 w-4" /> Archive
                    </Button>
                )}
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    {/* Application Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Application Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={application.status} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Source</p>
                                    <p className="font-medium capitalize">
                                        {application.source ? application.source.replace(/_/g, ' ') : '\u2014'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Applied Date</p>
                                    <p className="font-medium">
                                        {application.applied_at ? formatDate(application.applied_at) : '\u2014'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Priority</p>
                                    <p className="font-medium">{application.priority ?? '\u2014'}</p>
                                </div>
                                {application.location ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Location</p>
                                        <p className="font-medium">
                                            {application.location}
                                            {application.remote ? ' (Remote)' : ''}
                                        </p>
                                    </div>
                                ) : application.remote ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Location</p>
                                        <p className="font-medium text-blue-600 dark:text-blue-400">Remote</p>
                                    </div>
                                ) : null}
                                {application.next_action_at ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Next Action</p>
                                        <p className="font-medium">{formatDate(application.next_action_at)}</p>
                                    </div>
                                ) : null}
                            </div>
                            {application.job_url ? (
                                <>
                                    <Separator />
                                    <div>
                                        <a
                                            href={application.job_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                        >
                                            View Job Posting <ExternalLink className="h-3 w-3" />
                                        </a>
                                    </div>
                                </>
                            ) : null}
                            {application.job_description ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Job Description</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{application.job_description}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {/* Contact Info */}
                    {(application.contact_name || application.contact_email || application.contact_phone) ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    {application.contact_name ? (
                                        <div>
                                            <p className="text-sm text-muted-foreground">Name</p>
                                            <p className="font-medium">{application.contact_name}</p>
                                        </div>
                                    ) : null}
                                    {application.contact_email ? (
                                        <div>
                                            <p className="text-sm text-muted-foreground">Email</p>
                                            <a href={`mailto:${application.contact_email}`} className="font-medium text-primary hover:underline">
                                                {application.contact_email}
                                            </a>
                                        </div>
                                    ) : null}
                                    {application.contact_phone ? (
                                        <div>
                                            <p className="text-sm text-muted-foreground">Phone</p>
                                            <p className="font-medium">{application.contact_phone}</p>
                                        </div>
                                    ) : null}
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {/* Interviews */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Interviews</CardTitle>
                            <Button size="sm" asChild>
                                <Link href={`/job-applications/${application.id}/interviews/create`}>
                                    <Plus className="mr-2 h-4 w-4" /> Schedule Interview
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {interviews.length > 0 ? (
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Type</TableHead>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Interviewer</TableHead>
                                                <TableHead>Outcome</TableHead>
                                                <TableHead className="w-[80px]" />
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {interviews.map((interview) => (
                                                <TableRow key={interview.id}>
                                                    <TableCell className="capitalize">{interview.type}</TableCell>
                                                    <TableCell>{formatDate(interview.scheduled_at)}</TableCell>
                                                    <TableCell>{interview.interviewer_name ?? '\u2014'}</TableCell>
                                                    <TableCell>
                                                        {interview.completed ? (
                                                            <StatusBadge status={interview.outcome ?? 'pending'} />
                                                        ) : (
                                                            <StatusBadge status="upcoming" variant="info" />
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Button variant="ghost" size="sm" asChild>
                                                            <Link href={`/job-applications/${application.id}/interviews/${interview.id}`}>
                                                                View
                                                            </Link>
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">No interviews scheduled yet.</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Offer */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Offer</CardTitle>
                            {!offer ? (
                                <Button size="sm" asChild>
                                    <Link href={`/job-applications/${application.id}/offers/create`}>
                                        <Plus className="mr-2 h-4 w-4" /> Record Offer
                                    </Link>
                                </Button>
                            ) : null}
                        </CardHeader>
                        <CardContent>
                            {offer ? (
                                <div className="space-y-4">
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <p className="text-sm text-muted-foreground">Base Salary</p>
                                            <p className="text-lg font-semibold">
                                                {formatCurrency(offer.base_salary, offer.currency ?? currency)}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">Status</p>
                                            <StatusBadge status={offer.status} />
                                        </div>
                                        {offer.bonus ? (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Bonus</p>
                                                <p className="font-medium">
                                                    {formatCurrency(offer.bonus, offer.currency ?? currency)}
                                                </p>
                                            </div>
                                        ) : null}
                                        {offer.equity ? (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Equity</p>
                                                <p className="font-medium">{offer.equity}</p>
                                            </div>
                                        ) : null}
                                        {offer.start_date ? (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Start Date</p>
                                                <p className="font-medium">{formatDate(offer.start_date)}</p>
                                            </div>
                                        ) : null}
                                        {offer.decision_deadline ? (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Decision Deadline</p>
                                                <p className="font-medium">{formatDate(offer.decision_deadline)}</p>
                                            </div>
                                        ) : null}
                                    </div>
                                    <div className="flex gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={`/job-applications/${application.id}/offers/${offer.id}`}>
                                                View Details
                                            </Link>
                                        </Button>
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={`/job-applications/${application.id}/offers/${offer.id}/edit`}>
                                                Edit Offer
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">No offer received yet.</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Status History */}
                    {statusHistories.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Status History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>From</TableHead>
                                                <TableHead>To</TableHead>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Notes</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {statusHistories.map((history) => (
                                                <TableRow key={history.id}>
                                                    <TableCell>
                                                        {history.from_status ? (
                                                            <StatusBadge status={history.from_status} />
                                                        ) : '\u2014'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <StatusBadge status={history.to_status} />
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {formatDate(history.changed_at)}
                                                    </TableCell>
                                                    <TableCell className="text-sm text-muted-foreground">
                                                        {history.notes ?? '\u2014'}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                {/* Right Column - Summary */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Compensation</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {application.salary_min || application.salary_max ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Salary Range</p>
                                    <p className="text-xl font-semibold">
                                        {application.salary_min ? formatCurrency(application.salary_min, currency) : ''}
                                        {application.salary_min && application.salary_max ? ' - ' : ''}
                                        {application.salary_max ? formatCurrency(application.salary_max, currency) : ''}
                                    </p>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">No salary information</p>
                            )}
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Currency</p>
                                <p className="font-medium">{currency}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {application.company_website ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Company</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <a
                                    href={application.company_website}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                >
                                    Visit Website <ExternalLink className="h-3 w-3" />
                                </a>
                            </CardContent>
                        </Card>
                    ) : null}

                    {tags.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Tags</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex flex-wrap gap-1">
                                    {tags.map((tag) => (
                                        <span
                                            key={tag}
                                            className="rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground"
                                        >
                                            {tag}
                                        </span>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {application.notes ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap text-sm">{application.notes}</p>
                            </CardContent>
                        </Card>
                    ) : null}

                    <Card>
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div className="flex items-center gap-2 text-sm">
                                <CalendarDays className="h-4 w-4 text-muted-foreground" />
                                <span className="text-muted-foreground">Created:</span>
                                <span>{formatDate(application.created_at)}</span>
                            </div>
                            {application.applied_at ? (
                                <div className="flex items-center gap-2 text-sm">
                                    <CalendarDays className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Applied:</span>
                                    <span>{formatDate(application.applied_at)}</span>
                                </div>
                            ) : null}
                            {application.archived_at ? (
                                <div className="flex items-center gap-2 text-sm">
                                    <Archive className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-muted-foreground">Archived:</span>
                                    <span>{formatDate(application.archived_at)}</span>
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmTitle}
                description={confirmDescription}
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction === 'delete' ? 'Delete' : 'Confirm'}
                variant={confirmAction === 'delete' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}

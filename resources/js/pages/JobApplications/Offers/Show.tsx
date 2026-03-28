import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Pencil, Trash2, ArrowLeft, CheckCircle2, XCircle } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { JobApplication, JobApplicationOffer } from '@/types/models'

interface OfferShowProps {
    application: JobApplication
    offer: JobApplicationOffer
}

export default function OfferShow({ application, offer }: OfferShowProps) {
    const [confirmAction, setConfirmAction] = useState<'accept' | 'decline' | 'delete' | null>(null)

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'delete') {
            router.delete(`/job-applications/${application.id}/offers/${offer.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'accept') {
            router.patch(`/job-applications/${application.id}/offers/${offer.id}/accept`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else {
            router.patch(`/job-applications/${application.id}/offers/${offer.id}/decline`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, application.id, offer.id])

    const currency = offer.currency ?? 'USD'
    const totalCompensation = (Number(offer.base_salary) || 0) + (Number(offer.bonus) || 0)

    return (
        <AppLayout>
            <Head title={`Offer - ${application.company_name}`} />

            <PageHeader
                title="Job Offer"
                description={`${application.company_name} - ${application.job_title}`}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/job-applications/${application.id}`}>
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/job-applications/${application.id}/offers/${offer.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {offer.status === 'pending' || offer.status === 'negotiating' ? (
                    <>
                        <Button size="sm" onClick={() => setConfirmAction('accept')}>
                            <CheckCircle2 className="mr-2 h-4 w-4" /> Accept
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setConfirmAction('decline')}>
                            <XCircle className="mr-2 h-4 w-4" /> Decline
                        </Button>
                    </>
                ) : null}
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Compensation Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Base Salary</p>
                                    <p className="text-2xl font-semibold">{formatCurrency(offer.base_salary, currency)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={offer.status} />
                                </div>
                                {offer.bonus ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Bonus</p>
                                        <p className="text-lg font-semibold">{formatCurrency(offer.bonus, currency)}</p>
                                    </div>
                                ) : null}
                                <div>
                                    <p className="text-sm text-muted-foreground">Total Compensation</p>
                                    <p className="text-lg font-semibold">{formatCurrency(totalCompensation, currency)}</p>
                                </div>
                                {offer.equity ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Equity</p>
                                        <p className="font-medium">{offer.equity}</p>
                                    </div>
                                ) : null}
                                <div>
                                    <p className="text-sm text-muted-foreground">Currency</p>
                                    <p className="font-medium">{currency}</p>
                                </div>
                            </div>
                            {offer.benefits ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Benefits</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{offer.benefits}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Important Dates</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
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
                                <div>
                                    <p className="text-sm text-muted-foreground">Received</p>
                                    <p className="font-medium">{formatDate(offer.created_at)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {offer.notes ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap text-sm">{offer.notes}</p>
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
                title={
                    confirmAction === 'delete' ? 'Delete Offer'
                        : confirmAction === 'accept' ? 'Accept Offer'
                            : 'Decline Offer'
                }
                description={
                    confirmAction === 'delete'
                        ? 'Are you sure you want to delete this offer? This action cannot be undone.'
                        : confirmAction === 'accept'
                            ? 'Are you sure you want to accept this offer? This will update the application status.'
                            : 'Are you sure you want to decline this offer?'
                }
                onConfirm={handleConfirmAction}
                confirmLabel={
                    confirmAction === 'delete' ? 'Delete'
                        : confirmAction === 'accept' ? 'Accept Offer'
                            : 'Decline Offer'
                }
                variant={confirmAction === 'delete' || confirmAction === 'decline' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}

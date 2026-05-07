import { Head, Link, router } from '@inertiajs/react'
import { useState } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Textarea } from '@/components/ui/textarea'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { ArrowLeft, Check, Undo2, X } from 'lucide-react'

interface PendingActionShowProps {
    pendingAction: {
        id: number
        tenant_id: number
        agent_slug: string | null
        session_id: string | null
        tool: string
        action: string
        status: string
        idempotency_key: string
        payload: Record<string, unknown>
        preview: { summary?: string; category?: string } | null
        applied_diff: { before?: unknown; after?: unknown } | null
        failure_reason: string | null
        created_at: string
        applied_at: string | null
        reviewed_at: string | null
        user?: { id: number; name: string; email: string }
        agent_token?: { id: number; name: string; agent_slug: string | null }
        reviewer?: { id: number; name: string }
        reverter?: { id: number; name: string }
    }
    canApprove: boolean
    canReject: boolean
    canRevert: boolean
}

export default function PendingActionShow({ pendingAction, canApprove, canReject, canRevert }: PendingActionShowProps) {
    const [rejectOpen, setRejectOpen] = useState(false)
    const [reason, setReason] = useState('')

    const approve = () => {
        router.patch(`/dashboard/pending-actions/${pendingAction.id}/approve`)
    }

    const submitReject = () => {
        router.patch(
            `/dashboard/pending-actions/${pendingAction.id}/reject`,
            { reason },
            {
                onFinish: () => {
                    setRejectOpen(false)
                    setReason('')
                },
            },
        )
    }

    const revert = () => {
        router.patch(`/dashboard/pending-actions/${pendingAction.id}/revert`)
    }

    return (
        <AppLayout>
            <Head title={`Pending action #${pendingAction.id}`} />
            <PageHeader
                title={`#${pendingAction.id} ${pendingAction.tool}`}
                description={pendingAction.preview?.summary ?? pendingAction.action}
                actions={
                    <Button asChild variant="ghost" size="sm">
                        <Link href="/dashboard/pending-actions">
                            <ArrowLeft className="mr-1 h-4 w-4" />
                            Back to list
                        </Link>
                    </Button>
                }
            />

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <CardTitle className="text-base">Payload</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <pre className="overflow-x-auto rounded-md bg-muted p-4 text-xs">
                            {JSON.stringify(pendingAction.payload, null, 2)}
                        </pre>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Status</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Status</span>
                            <Badge variant="default">{pendingAction.status}</Badge>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Action</span>
                            <span className="font-mono text-xs">{pendingAction.action}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Agent</span>
                            <span>{pendingAction.agent_slug ?? '—'}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Created</span>
                            <span>{new Date(pendingAction.created_at).toLocaleString()}</span>
                        </div>
                        {pendingAction.reviewed_at && (
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Reviewed</span>
                                <span>{new Date(pendingAction.reviewed_at).toLocaleString()}</span>
                            </div>
                        )}
                        {pendingAction.applied_at && (
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Applied</span>
                                <span>{new Date(pendingAction.applied_at).toLocaleString()}</span>
                            </div>
                        )}
                        <div className="break-all text-xs text-muted-foreground">
                            <span>idempotency: </span>
                            <span className="font-mono">{pendingAction.idempotency_key}</span>
                        </div>
                        {pendingAction.failure_reason && (
                            <div className="rounded-md border border-destructive/50 bg-destructive/10 p-2 text-xs text-destructive">
                                {pendingAction.failure_reason}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {pendingAction.applied_diff && (
                    <Card className="lg:col-span-3">
                        <CardHeader>
                            <CardTitle className="text-base">Applied diff</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 md:grid-cols-2">
                            <div>
                                <div className="mb-2 text-xs font-medium text-muted-foreground">Before</div>
                                <pre className="overflow-x-auto rounded-md bg-muted p-4 text-xs">
                                    {JSON.stringify(pendingAction.applied_diff.before ?? null, null, 2)}
                                </pre>
                            </div>
                            <div>
                                <div className="mb-2 text-xs font-medium text-muted-foreground">After</div>
                                <pre className="overflow-x-auto rounded-md bg-muted p-4 text-xs">
                                    {JSON.stringify(pendingAction.applied_diff.after ?? null, null, 2)}
                                </pre>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card className="lg:col-span-3">
                    <CardHeader>
                        <CardTitle className="text-base">Actions</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-wrap gap-3">
                        <Button onClick={approve} disabled={!canApprove}>
                            <Check className="mr-1 h-4 w-4" />
                            Approve & apply
                        </Button>
                        <Button variant="outline" onClick={() => setRejectOpen(true)} disabled={!canReject}>
                            <X className="mr-1 h-4 w-4" />
                            Reject
                        </Button>
                        <Button variant="outline" onClick={revert} disabled={!canRevert}>
                            <Undo2 className="mr-1 h-4 w-4" />
                            Revert (within 10 min)
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <AlertDialog open={rejectOpen} onOpenChange={setRejectOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Reject pending action</AlertDialogTitle>
                        <AlertDialogDescription>
                            Optional: explain why this action is being rejected. The reason is stored on the audit record.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <Textarea
                        value={reason}
                        onChange={(e) => setReason(e.target.value)}
                        placeholder="Reason (optional)…"
                        rows={3}
                    />
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction onClick={submitReject} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                            Reject
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AppLayout>
    )
}

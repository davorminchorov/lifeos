import { Head, Link, router } from '@inertiajs/react'
import { useMemo, useState } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { EmptyState } from '@/components/shared/empty-state'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Inbox, Eye } from 'lucide-react'
import type { PaginatedData } from '@/types'

interface PendingAction {
    id: number
    agent_slug: string | null
    tool: string
    action: string
    status:
        | 'pending'
        | 'approved'
        | 'rejected'
        | 'applied'
        | 'failed'
        | 'reverted'
        | 'superseded'
    preview: { summary?: string; category?: string } | null
    payload: Record<string, unknown>
    created_at: string
    applied_at: string | null
    user?: { id: number; name: string; email: string }
    agent_token?: { id: number; name: string; agent_slug: string | null }
}

interface IndexProps {
    pendingActions: PaginatedData<PendingAction>
    filters: {
        status?: string
        agent?: string
        module?: string
        from?: string
        to?: string
    }
    pendingCount: number
}

const STATUS_VARIANTS: Record<PendingAction['status'], 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'default',
    approved: 'secondary',
    applied: 'secondary',
    rejected: 'destructive',
    failed: 'destructive',
    reverted: 'outline',
    superseded: 'outline',
}

const MODULES = [
    { value: '', label: 'All modules' },
    { value: 'expenses', label: 'Expenses' },
    { value: 'subscriptions', label: 'Subscriptions' },
    { value: 'contracts', label: 'Contracts' },
    { value: 'warranties', label: 'Warranties' },
    { value: 'iou', label: 'IOU' },
    { value: 'bills', label: 'Bills' },
]

const STATUSES = [
    { value: '', label: 'Active (pending + recent)' },
    { value: 'pending', label: 'Pending' },
    { value: 'applied', label: 'Applied' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'failed', label: 'Failed' },
    { value: 'reverted', label: 'Reverted' },
]

export default function PendingActionsIndex({ pendingActions, filters, pendingCount }: IndexProps) {
    const [selected, setSelected] = useState<number[]>([])
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '')
    const [moduleFilter, setModuleFilter] = useState(filters.module ?? '')

    const allSelected = useMemo(
        () => pendingActions.data.length > 0 && selected.length === pendingActions.data.filter((a) => a.status === 'pending').length,
        [pendingActions.data, selected.length],
    )

    const toggleSelected = (id: number, checked: boolean) => {
        setSelected((prev) => (checked ? [...prev, id] : prev.filter((x) => x !== id)))
    }

    const toggleAllPending = (checked: boolean) => {
        setSelected(checked ? pendingActions.data.filter((a) => a.status === 'pending').map((a) => a.id) : [])
    }

    const submitFilters = (next: Partial<IndexProps['filters']>) => {
        router.get(
            '/dashboard/pending-actions',
            { ...filters, ...next },
            { preserveState: true, preserveScroll: true, replace: true },
        )
    }

    const bulkApprove = () => {
        if (selected.length === 0) return
        router.post(
            '/dashboard/pending-actions/bulk-approve',
            { ids: selected },
            {
                onSuccess: () => setSelected([]),
            },
        )
    }

    return (
        <AppLayout>
            <Head title="Pending Agent Actions" />
            <PageHeader
                title="Pending Agent Actions"
                description="Review writes proposed by agents before they touch live data."
            />

            <div className="mt-6 grid gap-4 md:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <div className="text-xs text-muted-foreground">Currently pending</div>
                        <div className="mt-1 text-2xl font-semibold">{pendingCount}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <div className="text-xs text-muted-foreground">In this view</div>
                        <div className="mt-1 text-2xl font-semibold">{pendingActions.total}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="flex items-center justify-between p-4">
                        <div>
                            <div className="text-xs text-muted-foreground">Selected</div>
                            <div className="mt-1 text-2xl font-semibold">{selected.length}</div>
                        </div>
                        <Button onClick={bulkApprove} disabled={selected.length === 0}>
                            Approve selected
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <Card className="mt-6">
                <CardContent className="p-4">
                    <div className="flex flex-wrap gap-3">
                        <Select
                            value={statusFilter}
                            onValueChange={(v) => {
                                setStatusFilter(v)
                                submitFilters({ status: v || undefined })
                            }}
                        >
                            <SelectTrigger className="w-[220px]">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                {STATUSES.map((s) => (
                                    <SelectItem key={s.value || 'all'} value={s.value}>
                                        {s.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select
                            value={moduleFilter}
                            onValueChange={(v) => {
                                setModuleFilter(v)
                                submitFilters({ module: v || undefined })
                            }}
                        >
                            <SelectTrigger className="w-[200px]">
                                <SelectValue placeholder="Module" />
                            </SelectTrigger>
                            <SelectContent>
                                {MODULES.map((m) => (
                                    <SelectItem key={m.value || 'all'} value={m.value}>
                                        {m.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <Card className="mt-6">
                <CardContent className="p-0">
                    {pendingActions.data.length === 0 ? (
                        <EmptyState
                            icon={Inbox}
                            title="No pending actions"
                            description="When an agent proposes a write, it will appear here for review."
                        />
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-10">
                                        <Checkbox checked={allSelected} onCheckedChange={(c) => toggleAllPending(Boolean(c))} />
                                    </TableHead>
                                    <TableHead>Tool</TableHead>
                                    <TableHead>Agent</TableHead>
                                    <TableHead>Summary</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pendingActions.data.map((action) => (
                                    <TableRow key={action.id}>
                                        <TableCell>
                                            {action.status === 'pending' && (
                                                <Checkbox
                                                    checked={selected.includes(action.id)}
                                                    onCheckedChange={(c) => toggleSelected(action.id, Boolean(c))}
                                                />
                                            )}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">{action.tool}</TableCell>
                                        <TableCell className="text-sm">{action.agent_slug ?? action.agent_token?.agent_slug ?? '—'}</TableCell>
                                        <TableCell className="max-w-md truncate text-sm">
                                            {action.preview?.summary ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={STATUS_VARIANTS[action.status]}>{action.status}</Badge>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {new Date(action.created_at).toLocaleString()}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button asChild variant="ghost" size="sm">
                                                <Link href={`/dashboard/pending-actions/${action.id}`}>
                                                    <Eye className="mr-1 h-4 w-4" />
                                                    View
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </CardContent>
            </Card>
        </AppLayout>
    )
}

import { Head, Link, router } from '@inertiajs/react'
import { useState } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { EmptyState } from '@/components/shared/empty-state'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
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
import { Bot, Eye } from 'lucide-react'
import type { PaginatedData } from '@/types'

interface AgentRun {
    id: number
    agent_slug: string
    session_id: string | null
    model: string | null
    status: 'running' | 'completed' | 'failed' | 'cancelled'
    pending_actions_created: number
    tokens_in: number
    tokens_out: number
    cost_usd: number | string
    started_at: string
    ended_at: string | null
    error: string | null
    user?: { id: number; name: string; email: string }
    agent_token?: { id: number; name: string; agent_slug: string | null }
}

interface IndexProps {
    agentRuns: PaginatedData<AgentRun>
    filters: { agent?: string; status?: string; from?: string; to?: string }
    agents: string[]
}

const STATUS_VARIANTS: Record<AgentRun['status'], 'default' | 'secondary' | 'destructive' | 'outline'> = {
    running: 'default',
    completed: 'secondary',
    failed: 'destructive',
    cancelled: 'outline',
}

const STATUSES = [
    { value: '', label: 'All statuses' },
    { value: 'running', label: 'Running' },
    { value: 'completed', label: 'Completed' },
    { value: 'failed', label: 'Failed' },
    { value: 'cancelled', label: 'Cancelled' },
]

export default function AgentRunsIndex({ agentRuns, filters, agents }: IndexProps) {
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '')
    const [agentFilter, setAgentFilter] = useState(filters.agent ?? '')

    const submitFilters = (next: Partial<IndexProps['filters']>) => {
        router.get(
            '/dashboard/agents',
            { ...filters, ...next },
            { preserveState: true, preserveScroll: true, replace: true },
        )
    }

    return (
        <AppLayout>
            <Head title="Agent runs" />
            <PageHeader
                title="Agents"
                description="Sessions launched against the Anthropic Managed Agents API. Each row is one run, regardless of how many tools it called."
            />

            <Card className="mt-6">
                <CardContent className="p-4">
                    <div className="flex flex-wrap gap-3">
                        <Select
                            value={agentFilter}
                            onValueChange={(v) => {
                                setAgentFilter(v)
                                submitFilters({ agent: v || undefined })
                            }}
                        >
                            <SelectTrigger className="w-[220px]">
                                <SelectValue placeholder="Agent" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All agents</SelectItem>
                                {agents.map((slug) => (
                                    <SelectItem key={slug} value={slug}>
                                        {slug}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select
                            value={statusFilter}
                            onValueChange={(v) => {
                                setStatusFilter(v)
                                submitFilters({ status: v || undefined })
                            }}
                        >
                            <SelectTrigger className="w-[200px]">
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
                    </div>
                </CardContent>
            </Card>

            <Card className="mt-6">
                <CardContent className="p-0">
                    {agentRuns.data.length === 0 ? (
                        <EmptyState
                            icon={Bot}
                            title="No agent runs yet"
                            description="When an agent runs, you'll see its session here with tools called and pending actions produced."
                        />
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Agent</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Pending</TableHead>
                                    <TableHead className="text-right">Tokens (in/out)</TableHead>
                                    <TableHead className="text-right">Cost (USD)</TableHead>
                                    <TableHead>Started</TableHead>
                                    <TableHead className="text-right"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {agentRuns.data.map((run) => (
                                    <TableRow key={run.id}>
                                        <TableCell className="font-mono text-sm">{run.agent_slug}</TableCell>
                                        <TableCell>
                                            <Badge variant={STATUS_VARIANTS[run.status]}>{run.status}</Badge>
                                        </TableCell>
                                        <TableCell className="text-right">{run.pending_actions_created}</TableCell>
                                        <TableCell className="text-right text-sm tabular-nums">
                                            {run.tokens_in} / {run.tokens_out}
                                        </TableCell>
                                        <TableCell className="text-right text-sm tabular-nums">
                                            {Number(run.cost_usd).toFixed(4)}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {new Date(run.started_at).toLocaleString()}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button asChild variant="ghost" size="sm">
                                                <Link href={`/dashboard/agents/${run.id}`}>
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

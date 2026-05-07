import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft } from 'lucide-react'

interface AgentRunEvent {
    id: number
    sequence: number
    type: 'tool_call' | 'tool_result' | 'text' | 'error' | 'system'
    payload: Record<string, unknown>
    occurred_at: string
}

interface AgentRunShowProps {
    agentRun: {
        id: number
        agent_slug: string
        session_id: string | null
        model: string | null
        status: string
        tools_called: Record<string, number> | null
        pending_actions_created: number
        tokens_in: number
        tokens_out: number
        cost_usd: number | string
        error: string | null
        started_at: string
        ended_at: string | null
        duration_seconds: number | null
        user?: { id: number; name: string; email: string }
        agent_token?: { id: number; name: string; agent_slug: string | null }
        events: AgentRunEvent[]
    }
}

const EVENT_BADGE: Record<AgentRunEvent['type'], 'default' | 'secondary' | 'destructive' | 'outline'> = {
    tool_call: 'default',
    tool_result: 'secondary',
    text: 'outline',
    error: 'destructive',
    system: 'outline',
}

export default function AgentRunShow({ agentRun }: AgentRunShowProps) {
    const tools = agentRun.tools_called ?? {}

    return (
        <AppLayout>
            <Head title={`Agent run #${agentRun.id}`} />
            <PageHeader
                title={`#${agentRun.id} ${agentRun.agent_slug}`}
                description={agentRun.session_id ? `session ${agentRun.session_id}` : 'no session created'}
                actions={
                    <Button asChild variant="ghost" size="sm">
                        <Link href="/dashboard/agents">
                            <ArrowLeft className="mr-1 h-4 w-4" />
                            Back
                        </Link>
                    </Button>
                }
            />

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Status</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Status</span>
                            <Badge>{agentRun.status}</Badge>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Model</span>
                            <span>{agentRun.model ?? '—'}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Started</span>
                            <span>{new Date(agentRun.started_at).toLocaleString()}</span>
                        </div>
                        {agentRun.ended_at && (
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Ended</span>
                                <span>{new Date(agentRun.ended_at).toLocaleString()}</span>
                            </div>
                        )}
                        {agentRun.duration_seconds !== null && (
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Duration</span>
                                <span>{agentRun.duration_seconds}s</span>
                            </div>
                        )}
                        {agentRun.error && (
                            <div className="rounded-md border border-destructive/50 bg-destructive/10 p-2 text-xs text-destructive">
                                {agentRun.error}
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Usage</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm">
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Pending actions</span>
                            <span className="font-mono">{agentRun.pending_actions_created}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Tokens in</span>
                            <span className="font-mono tabular-nums">{agentRun.tokens_in}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Tokens out</span>
                            <span className="font-mono tabular-nums">{agentRun.tokens_out}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">Cost (USD)</span>
                            <span className="font-mono tabular-nums">{Number(agentRun.cost_usd).toFixed(4)}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Tools called</CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm">
                        {Object.keys(tools).length === 0 ? (
                            <p className="text-muted-foreground">No tool calls recorded.</p>
                        ) : (
                            <ul className="space-y-1 font-mono">
                                {Object.entries(tools).map(([name, count]) => (
                                    <li key={name} className="flex justify-between">
                                        <span>{name}</span>
                                        <span className="tabular-nums">{count}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>

                <Card className="lg:col-span-3">
                    <CardHeader>
                        <CardTitle className="text-base">Timeline</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {agentRun.events.length === 0 ? (
                            <p className="p-4 text-sm text-muted-foreground">No events recorded for this run.</p>
                        ) : (
                            <ol className="divide-y">
                                {agentRun.events.map((event) => (
                                    <li key={event.id} className="flex items-start gap-3 px-4 py-3">
                                        <span className="w-10 shrink-0 text-xs tabular-nums text-muted-foreground">#{event.sequence}</span>
                                        <Badge variant={EVENT_BADGE[event.type]} className="shrink-0">
                                            {event.type}
                                        </Badge>
                                        <div className="min-w-0 flex-1">
                                            <pre className="overflow-x-auto whitespace-pre-wrap rounded-md bg-muted p-2 text-xs">
                                                {JSON.stringify(event.payload, null, 2)}
                                            </pre>
                                        </div>
                                        <span className="shrink-0 text-xs text-muted-foreground">
                                            {new Date(event.occurred_at).toLocaleTimeString()}
                                        </span>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}

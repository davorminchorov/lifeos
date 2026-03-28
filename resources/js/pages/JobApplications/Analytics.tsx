import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatCard } from '@/components/shared/stat-card'
import { StatusBadge } from '@/components/shared/status-badge'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Briefcase,
    TrendingUp,
    CalendarDays,
    Award,
    Download,
    BarChart3,
    Target,
    Clock,
} from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { JobApplication, JobApplicationStatusHistory } from '@/types/models'

interface FunnelEntry {
    status: string
    label: string
    color: string
    count: number
}

interface SourceStat {
    source: string
    label: string
    applications: number
    interviews: number
    offers: number
    interview_rate: number
    offer_rate: number
}

interface StageMetric {
    status: string
    label: string
    avg_days: number
}

interface RecentActivityItem {
    application: JobApplication
    history: JobApplicationStatusHistory
}

interface AnalyticsProps {
    funnel: Record<string, FunnelEntry>
    sourceStats: Record<string, SourceStat>
    stageMetrics: Record<string, StageMetric>
    stats: {
        total_applications: number
        active_applications: number
        total_interviews: number
        upcoming_interviews: number
        offers_received: number
        offers_pending: number
        offers_accepted: number
        rejected: number
        withdrawn: number
        interview_rate: number
        offer_rate: number
        acceptance_rate: number
        avg_time_to_offer: number
    }
    recentActivity: RecentActivityItem[]
    dateFrom: string
    dateTo: string
}

export default function JobApplicationAnalytics({
    funnel,
    sourceStats,
    stageMetrics,
    stats,
    recentActivity,
    dateFrom,
    dateTo,
}: AnalyticsProps) {
    const [localDateFrom, setLocalDateFrom] = useState(dateFrom)
    const [localDateTo, setLocalDateTo] = useState(dateTo)

    const applyDateRange = useCallback(() => {
        router.get('/job-applications/analytics', {
            date_from: localDateFrom,
            date_to: localDateTo,
        }, {
            preserveState: true,
            replace: true,
        })
    }, [localDateFrom, localDateTo])

    const handleExport = useCallback(() => {
        window.location.href = `/job-applications/analytics/export?date_from=${localDateFrom}&date_to=${localDateTo}`
    }, [localDateFrom, localDateTo])

    const funnelEntries = Object.values(funnel)
    const sourceStatEntries = Object.values(sourceStats).filter(s => s.applications > 0)
    const stageMetricEntries = Object.values(stageMetrics).filter(s => s.avg_days > 0)
    const maxFunnelCount = Math.max(...funnelEntries.map(f => f.count), 1)

    return (
        <AppLayout>
            <Head title="Job Application Analytics" />

            <PageHeader title="Analytics" description="Track your job search performance">
                <Button variant="outline" asChild>
                    <Link href="/job-applications">Back to Applications</Link>
                </Button>
                <Button variant="outline" onClick={handleExport}>
                    <Download className="mr-2 h-4 w-4" />
                    Export CSV
                </Button>
            </PageHeader>

            {/* Date Range Filter */}
            <Card className="mb-6">
                <CardContent className="flex flex-wrap items-end gap-4 p-4">
                    <div>
                        <p className="mb-1 text-sm font-medium">From</p>
                        <DatePicker value={localDateFrom} onChange={setLocalDateFrom} />
                    </div>
                    <div>
                        <p className="mb-1 text-sm font-medium">To</p>
                        <DatePicker value={localDateTo} onChange={setLocalDateTo} />
                    </div>
                    <Button onClick={applyDateRange}>Apply</Button>
                </CardContent>
            </Card>

            {/* Key Stats */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Total Applications" value={stats.total_applications} icon={Briefcase} />
                <StatCard label="Interview Rate" value={`${stats.interview_rate}%`} icon={TrendingUp} />
                <StatCard label="Offer Rate" value={`${stats.offer_rate}%`} icon={Target} />
                <StatCard label="Avg. Time to Offer" value={`${stats.avg_time_to_offer} days`} icon={Clock} />
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Active" value={stats.active_applications} />
                <StatCard label="Total Interviews" value={stats.total_interviews} icon={CalendarDays} />
                <StatCard label="Offers Received" value={stats.offers_received} icon={Award} />
                <StatCard label="Acceptance Rate" value={`${stats.acceptance_rate}%`} />
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
                {/* Application Funnel */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BarChart3 className="h-5 w-5" />
                            Application Funnel
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {funnelEntries.map((entry) => (
                                <div key={entry.status}>
                                    <div className="mb-1 flex items-center justify-between text-sm">
                                        <span className="capitalize">{entry.label}</span>
                                        <span className="font-medium">{entry.count}</span>
                                    </div>
                                    <div className="h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full bg-primary transition-all"
                                            style={{ width: `${(entry.count / maxFunnelCount) * 100}%` }}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Source Effectiveness */}
                <Card>
                    <CardHeader>
                        <CardTitle>Source Effectiveness</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {sourceStatEntries.length > 0 ? (
                            <div className="rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Source</TableHead>
                                            <TableHead className="text-right">Apps</TableHead>
                                            <TableHead className="text-right">Interviews</TableHead>
                                            <TableHead className="text-right">Offers</TableHead>
                                            <TableHead className="text-right">Rate</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {sourceStatEntries.map((stat) => (
                                            <TableRow key={stat.source}>
                                                <TableCell className="font-medium">{stat.label}</TableCell>
                                                <TableCell className="text-right">{stat.applications}</TableCell>
                                                <TableCell className="text-right">{stat.interviews}</TableCell>
                                                <TableCell className="text-right">{stat.offers}</TableCell>
                                                <TableCell className="text-right">{stat.interview_rate}%</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No data available for the selected period.</p>
                        )}
                    </CardContent>
                </Card>

                {/* Time in Stage */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Clock className="h-5 w-5" />
                            Average Time in Stage
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {stageMetricEntries.length > 0 ? (
                            <div className="space-y-3">
                                {stageMetricEntries.map((metric) => (
                                    <div key={metric.status} className="flex items-center justify-between">
                                        <span className="text-sm capitalize">{metric.label}</span>
                                        <span className="text-sm font-medium">{metric.avg_days} days</span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No data available.</p>
                        )}
                    </CardContent>
                </Card>

                {/* Recent Activity */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentActivity.length > 0 ? (
                            <div className="space-y-3">
                                {recentActivity.map((item, index) => (
                                    <div key={index} className="flex items-start gap-3 text-sm">
                                        <div className="flex-1">
                                            <Link
                                                href={`/job-applications/${item.application.id}`}
                                                className="font-medium hover:underline"
                                            >
                                                {item.application.company_name}
                                            </Link>
                                            <div className="mt-1 flex items-center gap-2">
                                                {item.history.from_status ? (
                                                    <>
                                                        <StatusBadge status={item.history.from_status} />
                                                        <span className="text-muted-foreground">&rarr;</span>
                                                    </>
                                                ) : null}
                                                <StatusBadge status={item.history.to_status} />
                                            </div>
                                        </div>
                                        <span className="text-xs text-muted-foreground">
                                            {formatDate(item.history.changed_at)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No recent activity.</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}

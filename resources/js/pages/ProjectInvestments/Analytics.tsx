import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
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
import { ArrowLeft } from 'lucide-react'
import { formatCurrency } from '@/lib/utils'
import type { ProjectInvestment } from '@/types/models'

interface BreakdownEntry {
    count: number
    invested: number
    current_value: number
}

interface ProjectInvestmentAnalyticsProps {
    analytics: {
        total_projects: number
        active_projects: number
        completed_projects: number
        sold_projects: number
        abandoned_projects: number
        total_invested: number
        total_current_value: number
        total_gain_loss: number
        by_stage: Record<string, BreakdownEntry>
        by_business_model: Record<string, BreakdownEntry>
        by_project_type: Record<string, BreakdownEntry>
        projects: ProjectInvestment[]
    }
}

export default function ProjectInvestmentAnalytics({ analytics }: ProjectInvestmentAnalyticsProps) {
    return (
        <AppLayout>
            <Head title="Investment Analytics" />

            <PageHeader title="Investment Analytics" description="Overview of your project investment portfolio">
                <Button variant="outline" asChild>
                    <Link href="/project-investments">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back to Investments
                    </Link>
                </Button>
            </PageHeader>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Projects</p>
                        <p className="text-xl font-semibold">{analytics.total_projects}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Active</p>
                        <p className="text-xl font-semibold">{analytics.active_projects}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Invested</p>
                        <p className="text-xl font-semibold">{formatCurrency(analytics.total_invested, 'USD')}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Gain / Loss</p>
                        <p className={`text-xl font-semibold ${analytics.total_gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {analytics.total_gain_loss >= 0 ? '+' : ''}{formatCurrency(analytics.total_gain_loss, 'USD')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Completed</p>
                        <p className="text-xl font-semibold">{analytics.completed_projects}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Sold</p>
                        <p className="text-xl font-semibold">{analytics.sold_projects}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Abandoned</p>
                        <p className="text-xl font-semibold">{analytics.abandoned_projects}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Current Value</p>
                        <p className="text-xl font-semibold">{formatCurrency(analytics.total_current_value, 'USD')}</p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                {Object.keys(analytics.by_stage).length > 0 ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>By Stage</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {Object.entries(analytics.by_stage).map(([stage, data]) => (
                                    <div key={stage} className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium capitalize">{stage}</p>
                                            <p className="text-xs text-muted-foreground">{data.count} project{data.count !== 1 ? 's' : ''}</p>
                                        </div>
                                        <p className="text-sm font-medium">{formatCurrency(data.invested, 'USD')}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                ) : null}

                {Object.keys(analytics.by_business_model).length > 0 ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>By Business Model</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {Object.entries(analytics.by_business_model).map(([model, data]) => (
                                    <div key={model} className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium capitalize">{model}</p>
                                            <p className="text-xs text-muted-foreground">{data.count} project{data.count !== 1 ? 's' : ''}</p>
                                        </div>
                                        <p className="text-sm font-medium">{formatCurrency(data.invested, 'USD')}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                ) : null}

                {Object.keys(analytics.by_project_type).length > 0 ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>By Project Type</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {Object.entries(analytics.by_project_type).map(([type, data]) => (
                                    <div key={type} className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium">{type}</p>
                                            <p className="text-xs text-muted-foreground">{data.count} project{data.count !== 1 ? 's' : ''}</p>
                                        </div>
                                        <p className="text-sm font-medium">{formatCurrency(data.invested, 'USD')}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                ) : null}
            </div>

            {analytics.projects.length > 0 ? (
                <Card className="mt-6">
                    <CardHeader>
                        <CardTitle>All Projects by Investment</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Stage</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Total Invested</TableHead>
                                        <TableHead>Current Value</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {analytics.projects.map((project) => (
                                        <TableRow key={project.id}>
                                            <TableCell>
                                                <Link href={`/project-investments/${project.id}`} className="font-medium hover:underline">
                                                    {project.name}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="capitalize">{project.stage ?? '\u2014'}</TableCell>
                                            <TableCell className="capitalize">{project.status}</TableCell>
                                            <TableCell>{formatCurrency((project as unknown as { total_invested: number }).total_invested ?? 0, 'USD')}</TableCell>
                                            <TableCell>{project.current_value != null ? formatCurrency(project.current_value, 'USD') : '\u2014'}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            ) : null}
        </AppLayout>
    )
}

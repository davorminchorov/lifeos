import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { EmptyState } from '@/components/shared/empty-state'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { AlertTriangle, CheckCircle, Info, ShieldAlert } from 'lucide-react'

interface Alert {
    type: string
    severity: 'high' | 'medium' | 'low'
    message: string
    recommendation: string
}

interface RebalancingAlertsProps {
    alerts: Alert[]
}

const severityConfig = {
    high: {
        icon: ShieldAlert,
        bgColor: 'bg-red-50 dark:bg-red-950',
        borderColor: 'border-red-200 dark:border-red-800',
        iconColor: 'text-red-600 dark:text-red-400',
        label: 'High',
        labelColor: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
    },
    medium: {
        icon: AlertTriangle,
        bgColor: 'bg-yellow-50 dark:bg-yellow-950',
        borderColor: 'border-yellow-200 dark:border-yellow-800',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        label: 'Medium',
        labelColor: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
    },
    low: {
        icon: Info,
        bgColor: 'bg-blue-50 dark:bg-blue-950',
        borderColor: 'border-blue-200 dark:border-blue-800',
        iconColor: 'text-blue-600 dark:text-blue-400',
        label: 'Low',
        labelColor: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
    },
}

export default function RebalancingAlerts({ alerts }: RebalancingAlertsProps) {
    const highAlerts = alerts.filter(a => a.severity === 'high')
    const mediumAlerts = alerts.filter(a => a.severity === 'medium')
    const lowAlerts = alerts.filter(a => a.severity === 'low')

    return (
        <AppLayout>
            <Head title="Rebalancing Alerts" />

            <PageHeader title="Rebalancing Alerts" description="Portfolio rebalancing recommendations and alerts">
                <Button variant="outline" asChild>
                    <Link href="/investments">Back to Investments</Link>
                </Button>
                <Button variant="outline" asChild>
                    <Link href="/investments/tax-reports">Tax Reports</Link>
                </Button>
            </PageHeader>

            {/* Alert Summary */}
            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">High Priority</p>
                        <p className="text-2xl font-bold text-red-600">{highAlerts.length}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Medium Priority</p>
                        <p className="text-2xl font-bold text-yellow-600">{mediumAlerts.length}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Low Priority</p>
                        <p className="text-2xl font-bold text-blue-600">{lowAlerts.length}</p>
                    </CardContent>
                </Card>
            </div>

            {alerts.length > 0 ? (
                <div className="space-y-4">
                    {alerts.map((alert, index) => {
                        const config = severityConfig[alert.severity]
                        const Icon = config.icon

                        return (
                            <Card key={index} className={`border ${config.borderColor} ${config.bgColor}`}>
                                <CardContent className="p-4">
                                    <div className="flex items-start gap-4">
                                        <Icon className={`mt-0.5 h-5 w-5 flex-shrink-0 ${config.iconColor}`} />
                                        <div className="flex-1">
                                            <div className="mb-1 flex items-center gap-2">
                                                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${config.labelColor}`}>
                                                    {config.label}
                                                </span>
                                                <span className="text-xs capitalize text-muted-foreground">
                                                    {alert.type.replace('_', ' ')}
                                                </span>
                                            </div>
                                            <p className="font-medium">{alert.message}</p>
                                            <p className="mt-1 text-sm text-muted-foreground">{alert.recommendation}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )
                    })}
                </div>
            ) : (
                <EmptyState
                    icon={CheckCircle}
                    title="No rebalancing alerts"
                    description="Your portfolio is well balanced. No action needed at this time."
                />
            )}
        </AppLayout>
    )
}

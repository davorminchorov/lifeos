import { type LucideIcon } from 'lucide-react'
import { Card, CardContent } from '@/components/ui/card'
import { cn } from '@/lib/utils'

interface StatCardProps {
    label: string
    value: string | number
    icon?: LucideIcon
    description?: string
    trend?: {
        value: number
        label: string
    }
    className?: string
}

export function StatCard({ label, value, icon: Icon, description, trend, className }: StatCardProps) {
    return (
        <Card className={cn('', className)}>
            <CardContent className="p-6">
                <div className="flex items-center justify-between">
                    <p className="text-sm font-medium text-muted-foreground">{label}</p>
                    {Icon && <Icon className="h-4 w-4 text-muted-foreground" />}
                </div>
                <div className="mt-2">
                    <p className="text-2xl font-semibold tracking-tight">{value}</p>
                    {description && (
                        <p className="mt-1 text-xs text-muted-foreground">{description}</p>
                    )}
                    {trend && (
                        <p className={cn(
                            'mt-1 text-xs font-medium',
                            trend.value >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                        )}>
                            {trend.value >= 0 ? '+' : ''}{trend.value}% {trend.label}
                        </p>
                    )}
                </div>
            </CardContent>
        </Card>
    )
}

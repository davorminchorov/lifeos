import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'

type StatusVariant = 'success' | 'warning' | 'danger' | 'info' | 'default' | 'muted'

interface StatusBadgeProps {
    status: string
    variant?: StatusVariant
    className?: string
}

const variantStyles: Record<StatusVariant, string> = {
    success: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-400 dark:border-green-800',
    warning: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-800',
    danger: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-800',
    info: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-400 dark:border-blue-800',
    default: 'bg-secondary text-secondary-foreground',
    muted: 'bg-muted text-muted-foreground',
}

const statusToVariant: Record<string, StatusVariant> = {
    active: 'success',
    paid: 'success',
    completed: 'success',
    accepted: 'success',
    approved: 'success',
    pending: 'warning',
    draft: 'warning',
    interview: 'info',
    screening: 'info',
    assessment: 'info',
    applied: 'info',
    cancelled: 'danger',
    rejected: 'danger',
    overdue: 'danger',
    expired: 'danger',
    paused: 'muted',
    withdrawn: 'muted',
    archived: 'muted',
    wishlist: 'default',
}

export function StatusBadge({ status, variant, className }: StatusBadgeProps) {
    const resolvedVariant = variant ?? statusToVariant[status.toLowerCase()] ?? 'default'

    return (
        <Badge
            variant="outline"
            className={cn(
                'capitalize',
                variantStyles[resolvedVariant],
                className
            )}
        >
            {status.replace(/_/g, ' ')}
        </Badge>
    )
}

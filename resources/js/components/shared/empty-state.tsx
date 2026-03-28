import { type LucideIcon, Inbox } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/react'

interface EmptyStateProps {
    icon?: LucideIcon
    title: string
    description?: string
    action?: {
        label: string
        href: string
    }
}

export function EmptyState({ icon: Icon = Inbox, title, description, action }: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center rounded-lg border border-dashed border-border py-12">
            <Icon className="h-10 w-10 text-muted-foreground" />
            <h3 className="mt-4 text-sm font-semibold text-foreground">{title}</h3>
            {description && (
                <p className="mt-1 text-sm text-muted-foreground">{description}</p>
            )}
            {action && (
                <Button asChild className="mt-4" size="sm">
                    <Link href={action.href}>{action.label}</Link>
                </Button>
            )}
        </div>
    )
}

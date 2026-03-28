import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Bell, CheckCheck, Trash2, ExternalLink, RefreshCw, FileText, Shield } from 'lucide-react'
import { cn } from '@/lib/utils'
import type { PaginatedData } from '@/types'

interface Notification {
    id: string
    data: {
        title?: string
        message?: string
        type?: string
        action_url?: string
    }
    read_at: string | null
    created_at: string
    created_at_human: string
}

interface NotificationIndexProps {
    notifications: PaginatedData<Notification>
    unreadCount: number
}

function getNotificationIcon(type?: string) {
    switch (type) {
        case 'subscription_renewal':
            return RefreshCw
        case 'contract_expiration':
            return FileText
        case 'warranty_expiration':
            return Shield
        default:
            return Bell
    }
}

export default function NotificationIndex({ notifications, unreadCount }: NotificationIndexProps) {
    const [deleteTarget, setDeleteTarget] = useState<string | null>(null)

    const markAsRead = useCallback((id: string) => {
        router.post(`/notifications/${id}/mark-as-read`, {}, {
            preserveScroll: true,
        })
    }, [])

    const markAllAsRead = useCallback(() => {
        router.post('/notifications/mark-all-as-read', {}, {
            preserveScroll: true,
        })
    }, [])

    const handleDelete = useCallback(() => {
        if (!deleteTarget) return
        router.delete(`/notifications/${deleteTarget}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        })
    }, [deleteTarget])

    const readCount = notifications.total - unreadCount

    return (
        <AppLayout>
            <Head title="Notifications" />

            <PageHeader title="Notifications" description="Stay updated with important events and reminders">
                <Button variant="outline" asChild>
                    <Link href="/notifications/preferences">Preferences</Link>
                </Button>
                {unreadCount > 0 && (
                    <Button onClick={markAllAsRead}>
                        <CheckCheck className="mr-2 h-4 w-4" />
                        Mark All Read
                    </Button>
                )}
            </PageHeader>

            {/* Stats */}
            <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Notifications</p>
                        <p className="text-xl font-semibold">{notifications.total}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Unread</p>
                        <p className="text-xl font-semibold">{unreadCount}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Read</p>
                        <p className="text-xl font-semibold">{readCount}</p>
                    </CardContent>
                </Card>
            </div>

            {/* Notifications List */}
            {notifications.data.length > 0 ? (
                <Card>
                    <CardContent className="p-6">
                        <div className="space-y-4">
                            {notifications.data.map((notification) => {
                                const Icon = getNotificationIcon(notification.data.type)
                                const isUnread = !notification.read_at

                                return (
                                    <div
                                        key={notification.id}
                                        className={cn(
                                            'flex items-start rounded-lg border p-4',
                                            isUnread
                                                ? 'border-primary/20 bg-primary/5'
                                                : 'border-border bg-muted/30'
                                        )}
                                    >
                                        <div className="mt-0.5 mr-4 shrink-0">
                                            <div className={cn(
                                                'flex h-8 w-8 items-center justify-center rounded-full',
                                                isUnread ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
                                            )}>
                                                <Icon className="h-4 w-4" />
                                            </div>
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-start justify-between gap-4">
                                                <div className="flex-1">
                                                    <p className="text-sm font-medium">
                                                        {notification.data.title ?? 'Notification'}
                                                    </p>
                                                    {notification.data.message && (
                                                        <p className="mt-1 text-sm text-muted-foreground">
                                                            {notification.data.message}
                                                        </p>
                                                    )}
                                                    <p className="mt-2 text-xs text-muted-foreground">
                                                        {notification.created_at_human}
                                                    </p>
                                                </div>
                                                <div className="flex shrink-0 items-center gap-2">
                                                    {notification.data.action_url && (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                            onClick={() => {
                                                                if (isUnread) markAsRead(notification.id)
                                                            }}
                                                        >
                                                            <Link href={notification.data.action_url}>
                                                                <ExternalLink className="mr-1 h-3 w-3" />
                                                                View
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {isUnread && (
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => markAsRead(notification.id)}
                                                        >
                                                            <CheckCheck className="mr-1 h-3 w-3" />
                                                            Read
                                                        </Button>
                                                    )}
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="text-destructive hover:text-destructive"
                                                        onClick={() => setDeleteTarget(notification.id)}
                                                    >
                                                        <Trash2 className="h-3 w-3" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )
                            })}
                        </div>

                        {/* Pagination */}
                        {notifications.last_page > 1 && (
                            <div className="mt-6 flex items-center justify-between">
                                <p className="text-sm text-muted-foreground">
                                    Showing {notifications.from} to {notifications.to} of {notifications.total}
                                </p>
                                <div className="flex gap-2">
                                    {notifications.links.map((link, i) => (
                                        <Button
                                            key={i}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            ) : (
                <EmptyState
                    icon={Bell}
                    title="No notifications"
                    description="You're all caught up! No notifications to display."
                />
            )}

            <ConfirmationDialog
                open={deleteTarget !== null}
                onOpenChange={(open) => { if (!open) setDeleteTarget(null) }}
                title="Delete Notification"
                description="Are you sure you want to delete this notification?"
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

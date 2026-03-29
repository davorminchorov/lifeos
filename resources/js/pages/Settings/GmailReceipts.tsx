import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Switch } from '@/components/ui/switch'
import { Label } from '@/components/ui/label'
import { Mail, RefreshCw, CheckCircle } from 'lucide-react'
import type { GmailConnection } from '@/types/models'

interface GmailReceiptsProps {
    connection: GmailConnection | null
    stats: {
        total_processed: number
        pending: number
        failed: number
        last_synced: string | null
    } | null
    error?: string
}

export default function SettingsGmailReceipts({ connection, stats, error }: GmailReceiptsProps) {
    const [confirmDisconnect, setConfirmDisconnect] = useState(false)
    const [syncing, setSyncing] = useState(false)

    const handleConnect = useCallback(() => {
        router.post('/settings/gmail-receipts/connect')
    }, [])

    const handleDisconnect = useCallback(() => {
        router.post('/settings/gmail-receipts/disconnect', {}, {
            onFinish: () => setConfirmDisconnect(false),
        })
    }, [])

    const handleSync = useCallback(() => {
        setSyncing(true)
        router.post('/settings/gmail-receipts/sync', {}, {
            onFinish: () => setSyncing(false),
        })
    }, [])

    const handleToggleAutoSync = useCallback((enabled: boolean) => {
        router.post('/settings/gmail-receipts/toggle-auto-sync', {
            sync_enabled: enabled,
        }, { preserveScroll: true })
    }, [])

    return (
        <AppLayout>
            <Head title="Gmail Receipt Integration" />

            <PageHeader title="Gmail Receipt Integration" description="Automatically import expenses from Gmail receipts">
                <Button variant="outline" asChild>
                    <Link href="/settings">Back to Settings</Link>
                </Button>
            </PageHeader>

            {error && (
                <div className="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                    {error}
                </div>
            )}

            <div className="space-y-6">
                {/* Connection Status */}
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center">
                                <div className="mr-4 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                    <Mail className="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-medium">Gmail Connection Status</h3>
                                    {connection ? (
                                        <p className="text-sm font-semibold text-green-600 dark:text-green-400">
                                            Connected: {connection.email_address}
                                        </p>
                                    ) : (
                                        <p className="text-sm text-muted-foreground">Not connected</p>
                                    )}
                                </div>
                            </div>
                            <div>
                                {connection ? (
                                    <Button
                                        variant="destructive"
                                        onClick={() => setConfirmDisconnect(true)}
                                    >
                                        Disconnect
                                    </Button>
                                ) : (
                                    <Button onClick={handleConnect}>Connect Gmail</Button>
                                )}
                            </div>
                        </div>

                        {connection && stats && (
                            <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div className="rounded-lg bg-muted p-4">
                                    <p className="text-sm text-muted-foreground">Total Processed</p>
                                    <p className="text-2xl font-bold">{stats.total_processed}</p>
                                </div>
                                <div className="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-950">
                                    <p className="text-sm text-yellow-600 dark:text-yellow-400">Pending</p>
                                    <p className="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{stats.pending}</p>
                                </div>
                                <div className="rounded-lg bg-red-50 p-4 dark:bg-red-950">
                                    <p className="text-sm text-red-600 dark:text-red-400">Failed</p>
                                    <p className="text-2xl font-bold text-red-700 dark:text-red-300">{stats.failed}</p>
                                </div>
                                <div className="rounded-lg bg-blue-50 p-4 dark:bg-blue-950">
                                    <p className="text-sm text-blue-600 dark:text-blue-400">Last Synced</p>
                                    <p className="text-sm font-semibold text-blue-700 dark:text-blue-300">{stats.last_synced ?? 'Never'}</p>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {connection && (
                    <>
                        {/* Sync Settings */}
                        <Card>
                            <CardContent className="p-6">
                                <h3 className="mb-4 text-lg font-medium">Sync Settings</h3>

                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium">Automatic Sync</p>
                                            <p className="text-sm text-muted-foreground">
                                                Automatically sync receipts every hour
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Switch
                                                id="auto-sync"
                                                checked={connection.sync_enabled}
                                                onCheckedChange={handleToggleAutoSync}
                                            />
                                            <Label htmlFor="auto-sync" className="sr-only">
                                                Automatic Sync
                                            </Label>
                                        </div>
                                    </div>

                                    <div className="border-t pt-4">
                                        <Button onClick={handleSync} disabled={syncing}>
                                            <RefreshCw className={`mr-2 h-4 w-4 ${syncing ? 'animate-spin' : ''}`} />
                                            {syncing ? 'Syncing...' : 'Sync Now'}
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* How It Works */}
                        <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950">
                            <CardContent className="p-6">
                                <h3 className="mb-4 text-lg font-medium text-blue-800 dark:text-blue-200">How It Works</h3>
                                <ul className="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                                    {[
                                        'LifeOS scans your Gmail for receipt emails from common merchants',
                                        'Automatically extracts amount, merchant, date, and category',
                                        'Downloads and attaches receipt PDFs and images',
                                        'Creates expenses with "pending" status for your review',
                                        'Review and confirm expenses in the Expenses section',
                                    ].map((text) => (
                                        <li key={text} className="flex items-start gap-2">
                                            <CheckCircle className="mt-0.5 h-4 w-4 shrink-0" />
                                            <span>{text}</span>
                                        </li>
                                    ))}
                                </ul>
                            </CardContent>
                        </Card>
                    </>
                )}
            </div>

            <ConfirmationDialog
                open={confirmDisconnect}
                onOpenChange={setConfirmDisconnect}
                title="Disconnect Gmail"
                description="Are you sure you want to disconnect Gmail? You can reconnect anytime."
                onConfirm={handleDisconnect}
                confirmLabel="Disconnect"
                variant="danger"
            />
        </AppLayout>
    )
}

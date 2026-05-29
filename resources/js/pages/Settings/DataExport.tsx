import { Head, router } from '@inertiajs/react'
import { useEffect, useMemo, useState } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Download, Trash2, Loader2, Database } from 'lucide-react'
import { formatDate } from '@/lib/utils'

interface ModuleOption {
    key: string
    label: string
}

interface DataExportRecord {
    id: number
    modules: string[]
    filters: Record<string, string> | null
    status: 'pending' | 'processing' | 'completed' | 'failed'
    file_name: string | null
    row_counts: Record<string, number> | null
    error: string | null
    downloadable: boolean
    expires_at: string | null
    created_at: string | null
}

interface DataExportProps {
    modules: ModuleOption[]
    exports: DataExportRecord[]
}

const STATUS_VARIANT: Record<DataExportRecord['status'], 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'secondary',
    processing: 'secondary',
    completed: 'default',
    failed: 'destructive',
}

export default function DataExport({ modules, exports }: DataExportProps) {
    const [selected, setSelected] = useState<string[]>([])
    const [dateFrom, setDateFrom] = useState('')
    const [dateTo, setDateTo] = useState('')
    const [submitting, setSubmitting] = useState(false)
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

    const allSelected = selected.length === modules.length && modules.length > 0
    const hasActiveExports = useMemo(
        () => exports.some((e) => e.status === 'pending' || e.status === 'processing'),
        [exports],
    )

    // Poll for in-progress exports so the history table reflects completion.
    useEffect(() => {
        if (!hasActiveExports) {
            return
        }

        const interval = setInterval(() => {
            router.reload({ only: ['exports'] })
        }, 4000)

        return () => clearInterval(interval)
    }, [hasActiveExports])

    const toggleModule = (key: string) => {
        setSelected((prev) =>
            prev.includes(key) ? prev.filter((k) => k !== key) : [...prev, key],
        )
    }

    const toggleAll = () => {
        setSelected(allSelected ? [] : modules.map((m) => m.key))
    }

    const submit = (moduleKeys: string[]) => {
        if (moduleKeys.length === 0 || submitting) {
            return
        }

        setSubmitting(true)
        router.post(
            '/exports',
            {
                modules: moduleKeys,
                date_from: dateFrom || undefined,
                date_to: dateTo || undefined,
            },
            {
                preserveScroll: true,
                onFinish: () => setSubmitting(false),
                onSuccess: () => setSelected([]),
            },
        )
    }

    const handleDelete = () => {
        if (!confirmDelete) {
            return
        }
        router.delete(`/exports/${confirmDelete}`, {
            preserveScroll: true,
            onFinish: () => setConfirmDelete(null),
        })
    }

    const describeModules = (record: DataExportRecord) => {
        if (record.modules.length === 1) {
            return modules.find((m) => m.key === record.modules[0])?.label ?? record.modules[0]
        }
        return `${record.modules.length} modules`
    }

    return (
        <AppLayout>
            <Head title="Data Export" />

            <PageHeader
                title="Data Export"
                description="Download your data as CSV. Exports are prepared in the background and you'll be notified when they're ready."
            />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Choose what to export</CardTitle>
                            <Button variant="ghost" size="sm" onClick={toggleAll}>
                                {allSelected ? 'Clear all' : 'Select all'}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            {modules.map((module) => (
                                <label
                                    key={module.key}
                                    className="flex cursor-pointer items-center gap-3 rounded-md border border-border p-3 transition-colors hover:bg-accent"
                                >
                                    <Checkbox
                                        checked={selected.includes(module.key)}
                                        onCheckedChange={() => toggleModule(module.key)}
                                    />
                                    <span className="text-sm font-medium">{module.label}</span>
                                </label>
                            ))}
                        </div>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div className="space-y-1.5">
                                <Label htmlFor="date_from">From date (optional)</Label>
                                <Input
                                    id="date_from"
                                    type="date"
                                    value={dateFrom}
                                    onChange={(e) => setDateFrom(e.target.value)}
                                />
                            </div>
                            <div className="space-y-1.5">
                                <Label htmlFor="date_to">To date (optional)</Label>
                                <Input
                                    id="date_to"
                                    type="date"
                                    value={dateTo}
                                    onChange={(e) => setDateTo(e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button onClick={() => submit(selected)} disabled={selected.length === 0 || submitting}>
                                <Download className="mr-2 h-4 w-4" />
                                Export selected ({selected.length})
                            </Button>
                            <Button
                                variant="outline"
                                onClick={() => submit(modules.map((m) => m.key))}
                                disabled={submitting}
                            >
                                Export everything
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>About exports</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm text-muted-foreground">
                        <p>Each selected module becomes its own CSV file. Selecting more than one bundles them into a single ZIP archive.</p>
                        <p>Only data for your current workspace is included. Date filters apply to each module's primary date.</p>
                        <p>Finished exports remain available to download for 7 days.</p>
                    </CardContent>
                </Card>
            </div>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Recent exports</CardTitle>
                </CardHeader>
                <CardContent>
                    {exports.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-10 text-center text-muted-foreground">
                            <Database className="mb-3 h-8 w-8" />
                            <p className="text-sm">No exports yet. Choose some modules above to get started.</p>
                        </div>
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Contents</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Requested</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {exports.map((record) => (
                                    <TableRow key={record.id}>
                                        <TableCell>
                                            <div className="font-medium">{describeModules(record)}</div>
                                            {record.file_name && (
                                                <div className="text-xs text-muted-foreground">{record.file_name}</div>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant={STATUS_VARIANT[record.status]} className="capitalize">
                                                {(record.status === 'pending' || record.status === 'processing') && (
                                                    <Loader2 className="mr-1 h-3 w-3 animate-spin" />
                                                )}
                                                {record.status}
                                            </Badge>
                                            {record.status === 'failed' && record.error && (
                                                <div className="mt-1 text-xs text-destructive">{record.error}</div>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {record.created_at ? formatDate(record.created_at) : '—'}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center justify-end gap-2">
                                                {record.downloadable && (
                                                    <Button variant="outline" size="sm" asChild>
                                                        <a href={`/exports/${record.id}/download`}>
                                                            <Download className="mr-1 h-4 w-4" />
                                                            Download
                                                        </a>
                                                    </Button>
                                                )}
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => setConfirmDelete(record.id)}
                                                    aria-label="Delete export"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={confirmDelete !== null}
                onOpenChange={(open) => { if (!open) setConfirmDelete(null) }}
                title="Delete export"
                description="Are you sure you want to delete this export? The downloadable file will be removed."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

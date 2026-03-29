import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { HandCoins, Plus, Search, MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Iou } from '@/types/models'
import type { PaginatedData } from '@/types'

interface IouIndexProps {
    ious: PaginatedData<Iou>
    summary: {
        total_owe: number
        total_owed: number
        overdue_count: number
    }
    filters?: {
        search?: string
        type?: string
        status?: string
        category?: string
    }
}

const typeOptions = [
    { value: 'owe', label: 'I Owe' },
    { value: 'owed', label: 'Owed to Me' },
]

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'partially_paid', label: 'Partially Paid' },
    { value: 'paid', label: 'Paid' },
    { value: 'cancelled', label: 'Cancelled' },
]

export default function IouIndex({ ious, summary, filters = {} }: IouIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/ious', { ...filters, [key]: value || undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [filters])

    const handleSearch = useCallback((e: ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value)
    }, [])

    const handleSearchSubmit = useCallback(() => {
        applyFilter('search', search)
    }, [search, applyFilter])

    const clearFilters = useCallback(() => {
        router.get('/ious', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (!confirmDelete) return
        router.delete(`/ious/${confirmDelete}`, {
            onFinish: () => setConfirmDelete(null),
        })
    }, [confirmDelete])

    return (
        <AppLayout>
            <Head title="IOUs" />

            <PageHeader title="IOUs" description="Track money owed and owing">
                <Button asChild>
                    <Link href="/ious/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add IOU
                    </Link>
                </Button>
            </PageHeader>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">I Owe</p>
                        <p className="text-xl font-semibold text-red-600">{formatCurrency(summary.total_owe, 'MKD')}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Owed to Me</p>
                        <p className="text-xl font-semibold text-green-600">{formatCurrency(summary.total_owed, 'MKD')}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Overdue</p>
                        <p className="text-xl font-semibold">{summary.overdue_count}</p>
                    </CardContent>
                </Card>
            </div>

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search IOUs..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.type ?? '__all__'} onValueChange={(v) => applyFilter('type', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {typeOptions.map(t => (
                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {statusOptions.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {ious.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Person</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Remaining</TableHead>
                                    <TableHead>Due Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {ious.data.map((iou) => {
                                    const remaining = iou.amount - (iou.amount_paid ?? 0)
                                    return (
                                        <TableRow key={iou.id}>
                                            <TableCell>
                                                <Link href={`/ious/${iou.id}`} className="font-medium hover:underline">
                                                    {iou.person_name}
                                                </Link>
                                                {iou.description ? (
                                                    <p className="text-xs text-muted-foreground line-clamp-1">{iou.description}</p>
                                                ) : null}
                                            </TableCell>
                                            <TableCell>
                                                <span className={`text-sm font-medium ${iou.type === 'owe' ? 'text-red-600' : 'text-green-600'}`}>
                                                    {iou.type === 'owe' ? 'I Owe' : 'Owed to Me'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatCurrency(iou.amount, iou.currency ?? 'MKD')}
                                            </TableCell>
                                            <TableCell className="text-sm">
                                                {formatCurrency(remaining, iou.currency ?? 'MKD')}
                                            </TableCell>
                                            <TableCell className="text-sm">
                                                {iou.due_date ? formatDate(iou.due_date) : '\u2014'}
                                            </TableCell>
                                            <TableCell>
                                                <StatusBadge status={iou.status} />
                                            </TableCell>
                                            <TableCell>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon" className="h-8 w-8">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/ious/${iou.id}`}>
                                                                <Eye className="mr-2 h-4 w-4" /> View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/ious/${iou.id}/edit`}>
                                                                <Pencil className="mr-2 h-4 w-4" /> Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onClick={() => setConfirmDelete(iou.id)}
                                                            className="text-destructive"
                                                        >
                                                            <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    )
                                })}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {ious.data.map((iou) => {
                            const remaining = iou.amount - (iou.amount_paid ?? 0)
                            return (
                                <Card key={iou.id}>
                                    <CardContent className="p-4">
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <Link href={`/ious/${iou.id}`} className="font-medium hover:underline">
                                                    {iou.person_name}
                                                </Link>
                                                <p className={`text-sm ${iou.type === 'owe' ? 'text-red-600' : 'text-green-600'}`}>
                                                    {iou.type === 'owe' ? 'I Owe' : 'Owed to Me'}
                                                </p>
                                            </div>
                                            <StatusBadge status={iou.status} />
                                        </div>
                                        <div className="mt-3 flex items-center justify-between text-sm">
                                            <span className="font-medium">
                                                {formatCurrency(remaining, iou.currency ?? 'MKD')} remaining
                                            </span>
                                            {iou.due_date ? (
                                                <span className="text-muted-foreground">Due: {formatDate(iou.due_date)}</span>
                                            ) : null}
                                        </div>
                                    </CardContent>
                                </Card>
                            )
                        })}
                    </div>

                    {ious.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {ious.from} to {ious.to} of {ious.total}
                            </p>
                            <div className="flex gap-2">
                                {ious.links.map((link, i) => (
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
                    ) : null}
                </>
            ) : (
                <EmptyState
                    icon={HandCoins}
                    title="No IOUs yet"
                    description="Start tracking money owed and owing"
                    action={{ label: 'Add IOU', href: '/ious/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmDelete !== null}
                onOpenChange={(open) => { if (!open) setConfirmDelete(null) }}
                title="Delete IOU"
                description="Are you sure you want to delete this IOU? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

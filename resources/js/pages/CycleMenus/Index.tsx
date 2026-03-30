import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
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
import { UtensilsCrossed, Plus, MoreHorizontal, Eye, Pencil, Trash2, Upload } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { CycleMenu } from '@/types/models'
import type { PaginatedData } from '@/types'

interface CycleMenuIndexProps {
    menus: PaginatedData<CycleMenu>
}

export default function CycleMenuIndex({ menus }: CycleMenuIndexProps) {
    const [deleteId, setDeleteId] = useState<number | null>(null)

    const handleDelete = useCallback(() => {
        if (!deleteId) return
        router.delete(`/cycle-menus/${deleteId}`, {
            onFinish: () => setDeleteId(null),
        })
    }, [deleteId])

    return (
        <AppLayout>
            <Head title="Cycle Menus" />

            <PageHeader title="Cycle Menus" description="Plan meals in repeating cycles and see each day's items">
                <Button variant="outline" asChild>
                    <Link href="/cycle-menu-items/import">
                        <Upload className="mr-2 h-4 w-4" />
                        Import CSV
                    </Link>
                </Button>
                <Button asChild>
                    <Link href="/cycle-menus/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Cycle Menu
                    </Link>
                </Button>
            </PageHeader>

            {menus.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Starts On</TableHead>
                                    <TableHead>Length</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {menus.data.map((menu) => (
                                    <TableRow key={menu.id}>
                                        <TableCell>
                                            <Link href={`/cycle-menus/${menu.id}`} className="font-medium hover:underline">
                                                {menu.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {menu.starts_on ? formatDate(menu.starts_on) : '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {menu.cycle_length_days} days
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={menu.is_active ? 'active' : 'paused'} />
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
                                                        <Link href={`/cycle-menus/${menu.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/cycle-menus/${menu.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setDeleteId(menu.id)}
                                                        className="text-destructive"
                                                    >
                                                        <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {menus.data.map((menu) => (
                            <Card key={menu.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/cycle-menus/${menu.id}`} className="font-medium hover:underline">
                                                {menu.name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">
                                                {menu.cycle_length_days} days
                                            </p>
                                        </div>
                                        <StatusBadge status={menu.is_active ? 'active' : 'paused'} />
                                    </div>
                                    {menu.starts_on ? (
                                        <p className="mt-2 text-sm text-muted-foreground">
                                            Starts: {formatDate(menu.starts_on)}
                                        </p>
                                    ) : null}
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {menus.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {menus.from} to {menus.to} of {menus.total}
                            </p>
                            <div className="flex gap-2">
                                {menus.links.map((link, i) => (
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
                    icon={UtensilsCrossed}
                    title="No cycle menus yet"
                    description="Create one to start planning meals in repeating cycles"
                    action={{ label: 'New Cycle Menu', href: '/cycle-menus/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteId !== null}
                onOpenChange={(open) => { if (!open) setDeleteId(null) }}
                title="Delete Cycle Menu"
                description="Are you sure you want to delete this cycle menu? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

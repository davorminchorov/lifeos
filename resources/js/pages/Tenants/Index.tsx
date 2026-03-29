import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
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
import { Building2, Plus, MoreHorizontal, Eye, Pencil, ArrowRightLeft, Trash2 } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { Tenant } from '@/types/models'

interface TenantWithRole extends Tenant {
    pivot?: {
        role: string
    }
    is_owner: boolean
}

interface TenantIndexProps {
    tenants: TenantWithRole[]
}

export default function TenantIndex({ tenants }: TenantIndexProps) {
    const [deleteTarget, setDeleteTarget] = useState<TenantWithRole | null>(null)

    const handleSwitch = useCallback((tenantId: number) => {
        router.post(`/tenants/${tenantId}/switch`)
    }, [])

    const handleDelete = useCallback(() => {
        if (!deleteTarget) return
        router.delete(`/tenants/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        })
    }, [deleteTarget])

    return (
        <AppLayout>
            <Head title="Workspaces" />

            <PageHeader title="Workspaces" description="Manage your workspaces and teams">
                <Button asChild>
                    <Link href="/tenants/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Workspace
                    </Link>
                </Button>
            </PageHeader>

            {tenants.length > 0 ? (
                <>
                    {/* Desktop Table */}
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Created</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {tenants.map((tenant) => (
                                    <TableRow key={tenant.id}>
                                        <TableCell>
                                            <Link href={`/tenants/${tenant.id}`} className="font-medium hover:underline">
                                                {tenant.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm capitalize text-muted-foreground">
                                            {tenant.is_owner ? 'Owner' : (tenant.pivot?.role ?? 'Member')}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {formatDate(tenant.created_at)}
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
                                                        <Link href={`/tenants/${tenant.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onClick={() => handleSwitch(tenant.id)}>
                                                        <ArrowRightLeft className="mr-2 h-4 w-4" /> Switch
                                                    </DropdownMenuItem>
                                                    {tenant.is_owner && (
                                                        <>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`/tenants/${tenant.id}/edit`}>
                                                                    <Pencil className="mr-2 h-4 w-4" /> Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onClick={() => setDeleteTarget(tenant)}
                                                                className="text-destructive"
                                                            >
                                                                <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                            </DropdownMenuItem>
                                                        </>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    {/* Mobile Cards */}
                    <div className="space-y-3 md:hidden">
                        {tenants.map((tenant) => (
                            <Card key={tenant.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/tenants/${tenant.id}`} className="font-medium hover:underline">
                                                {tenant.name}
                                            </Link>
                                            <p className="text-sm capitalize text-muted-foreground">
                                                {tenant.is_owner ? 'Owner' : (tenant.pivot?.role ?? 'Member')}
                                            </p>
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleSwitch(tenant.id)}
                                        >
                                            Switch
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </>
            ) : (
                <EmptyState
                    icon={Building2}
                    title="No workspaces yet"
                    description="Create your first workspace to get started"
                    action={{ label: 'Create Workspace', href: '/tenants/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteTarget !== null}
                onOpenChange={(open) => { if (!open) setDeleteTarget(null) }}
                title="Delete Workspace"
                description={`Are you sure you want to delete "${deleteTarget?.name}"? This action cannot be undone.`}
                onConfirm={handleDelete}
                confirmLabel="Delete Workspace"
                variant="danger"
            />
        </AppLayout>
    )
}

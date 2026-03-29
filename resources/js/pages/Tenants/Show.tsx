import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
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
import { Pencil, Trash2, ArrowRightLeft } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { Tenant, User } from '@/types/models'

interface MemberWithRole extends User {
    pivot?: {
        role: string
    }
}

interface TenantShowProps {
    tenant: Tenant
    members: MemberWithRole[]
    isOwner: boolean
}

export default function TenantShow({ tenant, members, isOwner }: TenantShowProps) {
    const [confirmDelete, setConfirmDelete] = useState(false)

    const handleSwitch = useCallback(() => {
        router.post(`/tenants/${tenant.id}/switch`)
    }, [tenant.id])

    const handleDelete = useCallback(() => {
        router.delete(`/tenants/${tenant.id}`, {
            onFinish: () => setConfirmDelete(false),
        })
    }, [tenant.id])

    return (
        <AppLayout>
            <Head title={tenant.name} />

            <PageHeader title={tenant.name} description="Workspace details and members">
                <Button variant="outline" onClick={handleSwitch}>
                    <ArrowRightLeft className="mr-2 h-4 w-4" />
                    Switch to this workspace
                </Button>
                {isOwner && (
                    <>
                        <Button variant="outline" asChild>
                            <Link href={`/tenants/${tenant.id}/edit`}>
                                <Pencil className="mr-2 h-4 w-4" />
                                Edit
                            </Link>
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={() => setConfirmDelete(true)}
                        >
                            <Trash2 className="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </>
                )}
            </PageHeader>

            <div className="space-y-6">
                {/* Workspace Details */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-4 text-lg font-medium">Workspace Details</h3>
                        <dl className="divide-y divide-border">
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Name</dt>
                                <dd className="text-sm">{tenant.name}</dd>
                            </div>
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Slug</dt>
                                <dd className="text-sm font-mono text-muted-foreground">{tenant.slug}</dd>
                            </div>
                            {tenant.default_currency && (
                                <div className="flex justify-between py-3">
                                    <dt className="text-sm font-medium text-muted-foreground">Default Currency</dt>
                                    <dd className="text-sm">{tenant.default_currency}</dd>
                                </div>
                            )}
                            {tenant.default_country && (
                                <div className="flex justify-between py-3">
                                    <dt className="text-sm font-medium text-muted-foreground">Default Country</dt>
                                    <dd className="text-sm">{tenant.default_country}</dd>
                                </div>
                            )}
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Created</dt>
                                <dd className="text-sm">{formatDate(tenant.created_at)}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                {/* Members */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-4 text-lg font-medium">Members ({members.length})</h3>

                        <div className="rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Role</TableHead>
                                        <TableHead>Joined</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {members.map((member) => (
                                        <TableRow key={member.id}>
                                            <TableCell className="font-medium">{member.name}</TableCell>
                                            <TableCell className="text-sm text-muted-foreground">{member.email}</TableCell>
                                            <TableCell className="text-sm capitalize">
                                                {member.id === tenant.owner_id ? 'Owner' : (member.pivot?.role ?? 'Member')}
                                            </TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {formatDate(member.created_at)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div className="mt-6 flex justify-center">
                <Button variant="outline" asChild>
                    <Link href="/tenants">Back to Workspaces</Link>
                </Button>
            </div>

            <ConfirmationDialog
                open={confirmDelete}
                onOpenChange={setConfirmDelete}
                title="Delete Workspace"
                description={`Are you sure you want to delete "${tenant.name}"? This action cannot be undone and all associated data will be permanently removed.`}
                onConfirm={handleDelete}
                confirmLabel="Delete Workspace"
                variant="danger"
            />
        </AppLayout>
    )
}

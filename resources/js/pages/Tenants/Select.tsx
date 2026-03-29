import { Head, Link, router } from '@inertiajs/react'
import { useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { EmptyState } from '@/components/shared/empty-state'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Building2, Plus, ArrowRight } from 'lucide-react'
import type { Tenant } from '@/types/models'

interface TenantWithRole extends Tenant {
    pivot?: {
        role: string
    }
    is_owner: boolean
}

interface TenantSelectProps {
    tenants: TenantWithRole[]
}

export default function TenantSelect({ tenants }: TenantSelectProps) {
    const handleSelect = useCallback((tenantId: number) => {
        router.post(`/tenants/${tenantId}/switch`)
    }, [])

    return (
        <AppLayout>
            <Head title="Select Workspace" />

            <PageHeader title="Select Workspace" description="Choose a workspace to continue">
                <Button asChild>
                    <Link href="/tenants/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Workspace
                    </Link>
                </Button>
            </PageHeader>

            {tenants.length > 0 ? (
                <div className="mx-auto grid max-w-3xl grid-cols-1 gap-4 sm:grid-cols-2">
                    {tenants.map((tenant) => (
                        <Card
                            key={tenant.id}
                            className="cursor-pointer transition-shadow hover:shadow-md"
                            onClick={() => handleSelect(tenant.id)}
                        >
                            <CardContent className="p-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center">
                                        <div className="mr-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                            <Building2 className="h-6 w-6" />
                                        </div>
                                        <div>
                                            <h3 className="font-medium">{tenant.name}</h3>
                                            <p className="text-sm capitalize text-muted-foreground">
                                                {tenant.is_owner ? 'Owner' : (tenant.pivot?.role ?? 'Member')}
                                            </p>
                                        </div>
                                    </div>
                                    <ArrowRight className="h-5 w-5 text-muted-foreground" />
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            ) : (
                <EmptyState
                    icon={Building2}
                    title="No workspaces available"
                    description="Create your first workspace to get started"
                    action={{ label: 'Create Workspace', href: '/tenants/create' }}
                />
            )}
        </AppLayout>
    )
}

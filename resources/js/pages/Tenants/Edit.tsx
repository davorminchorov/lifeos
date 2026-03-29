import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import type { Tenant } from '@/types/models'

interface TenantEditProps {
    tenant: Tenant
}

export default function TenantEdit({ tenant }: TenantEditProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: tenant.name,
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        patch(`/tenants/${tenant.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${tenant.name}`} />

            <PageHeader title="Edit Workspace" description={`Update settings for ${tenant.name}`}>
                <Button variant="outline" asChild>
                    <Link href={`/tenants/${tenant.id}`}>Back to Workspace</Link>
                </Button>
            </PageHeader>

            <Card className="mx-auto max-w-lg">
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <FormField
                            label="Workspace Name"
                            name="name"
                            value={data.name}
                            onChange={e => setData('name', e.target.value)}
                            error={errors.name}
                            required
                        />

                        <div className="flex justify-end gap-3">
                            <Button variant="outline" asChild>
                                <Link href={`/tenants/${tenant.id}`}>Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

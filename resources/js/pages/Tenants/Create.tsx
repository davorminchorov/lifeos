import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'

export default function TenantCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/tenants')
    }

    return (
        <AppLayout>
            <Head title="Create Workspace" />

            <PageHeader title="Create Workspace" description="Set up a new workspace for your team or project">
                <Button variant="outline" asChild>
                    <Link href="/tenants">Back to Workspaces</Link>
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
                            placeholder="My Workspace"
                            required
                        />

                        <div className="flex justify-end gap-3">
                            <Button variant="outline" asChild>
                                <Link href="/tenants">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Workspace'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

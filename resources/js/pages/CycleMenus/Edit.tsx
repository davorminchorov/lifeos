import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import type { CycleMenu } from '@/types/models'

interface CycleMenuEditProps {
    menu: CycleMenu
}

export default function CycleMenuEdit({ menu }: CycleMenuEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: menu.name,
        starts_on: menu.starts_on ?? '',
        cycle_length_days: String(menu.cycle_length_days),
        is_active: menu.is_active,
        notes: menu.notes ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/cycle-menus/${menu.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${menu.name}`} />

            <PageHeader title={`Edit ${menu.name}`} description="Update cycle menu details">
                <Button variant="outline" asChild>
                    <Link href={`/cycle-menus/${menu.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Menu Details" description="Basic information about the cycle menu">
                            <FormField
                                label="Name"
                                name="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="e.g. Weekly Meal Plan"
                            />
                            <FormField
                                label="Cycle Length (days)"
                                name="cycle_length_days"
                                type="number"
                                value={data.cycle_length_days}
                                onChange={e => setData('cycle_length_days', e.target.value)}
                                error={errors.cycle_length_days}
                                required
                                min="1"
                                max="365"
                            />
                            <FormField label="Starts On" name="starts_on" error={errors.starts_on}>
                                <DatePicker value={data.starts_on} onChange={v => setData('starts_on', v)} />
                            </FormField>
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) => setData('is_active', checked === true)}
                                />
                                <Label htmlFor="is_active">Active</Label>
                            </div>
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                rows={4}
                                placeholder="Optional notes"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/cycle-menus/${menu.id}`}>Cancel</Link>
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

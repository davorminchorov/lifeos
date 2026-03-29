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

export default function CycleMenuCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        starts_on: '',
        cycle_length_days: '7',
        is_active: true,
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/cycle-menus')
    }

    return (
        <AppLayout>
            <Head title="New Cycle Menu" />

            <PageHeader title="New Cycle Menu" description="Create a new meal cycle plan">
                <Button variant="outline" asChild>
                    <Link href="/cycle-menus">Back to List</Link>
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
                                <Link href="/cycle-menus">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

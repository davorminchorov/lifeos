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

export default function TaxRateCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        percentage_basis_points: '',
        country: '',
        active: true,
        valid_from: '',
        valid_to: '',
        description: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/invoicing/tax-rates')
    }

    return (
        <AppLayout>
            <Head title="Create Tax Rate" />

            <PageHeader title="Create Tax Rate" description="Add a new tax rate">
                <Button variant="outline" asChild>
                    <Link href="/invoicing/tax-rates">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Tax Rate Details" description="Define the tax rate">
                            <FormField
                                label="Name"
                                name="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="e.g. VAT, Sales Tax"
                            />
                            <FormField
                                label="Rate (basis points)"
                                name="percentage_basis_points"
                                type="number"
                                value={data.percentage_basis_points}
                                onChange={e => setData('percentage_basis_points', e.target.value)}
                                error={errors.percentage_basis_points}
                                required
                                min="0"
                                max="1000000"
                                placeholder="e.g. 2000 for 20%"
                            />
                            <FormField
                                label="Country Code"
                                name="country"
                                value={data.country}
                                onChange={e => setData('country', e.target.value)}
                                error={errors.country}
                                placeholder="e.g. MK, US"
                            />
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="active"
                                    checked={data.active}
                                    onCheckedChange={(checked) => setData('active', checked === true)}
                                />
                                <Label htmlFor="active">Active</Label>
                            </div>
                        </FormSection>

                        <FormSection title="Validity Period" description="Optional date range when this tax rate is valid">
                            <FormField label="Valid From" name="valid_from" error={errors.valid_from}>
                                <DatePicker value={data.valid_from} onChange={v => setData('valid_from', v)} />
                            </FormField>
                            <FormField label="Valid To" name="valid_to" error={errors.valid_to}>
                                <DatePicker value={data.valid_to} onChange={v => setData('valid_to', v)} />
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Description"
                                name="description"
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                error={errors.description}
                                multiline
                                placeholder="Optional description"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/invoicing/tax-rates">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Tax Rate'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

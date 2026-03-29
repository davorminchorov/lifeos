import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

const warrantyTypes = [
    { value: 'manufacturer', label: 'Manufacturer' },
    { value: 'extended', label: 'Extended' },
    { value: 'both', label: 'Both' },
]

export default function WarrantyCreate() {
    const { data, setData, post, processing, errors } = useForm({
        product_name: '',
        brand: '',
        model: '',
        serial_number: '',
        purchase_date: '',
        purchase_price: '',
        retailer: '',
        warranty_duration_months: '',
        warranty_type: '',
        warranty_terms: '',
        warranty_expiration_date: '',
        notes: '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/warranties')
    }

    return (
        <AppLayout>
            <Head title="Create Warranty" />

            <PageHeader title="Create Warranty" description="Add a new product warranty">
                <Button variant="outline" asChild>
                    <Link href="/warranties">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Product Information" description="Details about the product">
                            <FormField
                                label="Product Name"
                                name="product_name"
                                value={data.product_name}
                                onChange={e => setData('product_name', e.target.value)}
                                error={errors.product_name}
                                required
                                placeholder="e.g. MacBook Pro 16"
                            />
                            <FormField
                                label="Brand"
                                name="brand"
                                value={data.brand}
                                onChange={e => setData('brand', e.target.value)}
                                error={errors.brand}
                                required
                                placeholder="e.g. Apple"
                            />
                            <FormField
                                label="Model"
                                name="model"
                                value={data.model}
                                onChange={e => setData('model', e.target.value)}
                                error={errors.model}
                                placeholder="e.g. A2485"
                            />
                            <FormField
                                label="Serial Number"
                                name="serial_number"
                                value={data.serial_number}
                                onChange={e => setData('serial_number', e.target.value)}
                                error={errors.serial_number}
                                placeholder="Product serial number"
                            />
                        </FormSection>

                        <FormSection title="Purchase Details">
                            <FormField label="Purchase Date" name="purchase_date" error={errors.purchase_date} required>
                                <DatePicker value={data.purchase_date} onChange={v => setData('purchase_date', v)} />
                            </FormField>
                            <FormField
                                label="Purchase Price"
                                name="purchase_price"
                                type="number"
                                value={data.purchase_price}
                                onChange={e => setData('purchase_price', e.target.value)}
                                error={errors.purchase_price}
                                required
                                min="1"
                                step="0.01"
                                placeholder="0.00"
                            />
                            <FormField
                                label="Retailer"
                                name="retailer"
                                value={data.retailer}
                                onChange={e => setData('retailer', e.target.value)}
                                error={errors.retailer}
                                placeholder="Where purchased"
                            />
                        </FormSection>

                        <FormSection title="Warranty Details">
                            <FormField label="Warranty Type" name="warranty_type" error={errors.warranty_type} required>
                                <Select value={data.warranty_type} onValueChange={v => setData('warranty_type', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warrantyTypes.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Duration (months)"
                                name="warranty_duration_months"
                                type="number"
                                value={data.warranty_duration_months}
                                onChange={e => setData('warranty_duration_months', e.target.value)}
                                error={errors.warranty_duration_months}
                                required
                                min="1"
                                max="120"
                                placeholder="e.g. 24"
                            />
                            <FormField label="Expiration Date" name="warranty_expiration_date" error={errors.warranty_expiration_date} required>
                                <DatePicker value={data.warranty_expiration_date} onChange={v => setData('warranty_expiration_date', v)} />
                            </FormField>
                            <FormField
                                label="Warranty Terms"
                                name="warranty_terms"
                                value={data.warranty_terms}
                                onChange={e => setData('warranty_terms', e.target.value)}
                                error={errors.warranty_terms}
                                multiline
                                placeholder="Coverage details"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Additional notes"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/warranties">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Creating...' : 'Create Warranty'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

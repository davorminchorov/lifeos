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
import type { Warranty } from '@/types/models'

interface WarrantyEditProps {
    warranty: Warranty
}

const warrantyTypes = [
    { value: 'manufacturer', label: 'Manufacturer' },
    { value: 'extended', label: 'Extended' },
    { value: 'both', label: 'Both' },
]

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'claimed', label: 'Claimed' },
    { value: 'transferred', label: 'Transferred' },
    { value: 'expired', label: 'Expired' },
]

export default function WarrantyEdit({ warranty }: WarrantyEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        product_name: warranty.product_name,
        brand: warranty.brand ?? '',
        model: warranty.model ?? '',
        serial_number: warranty.serial_number ?? '',
        purchase_date: warranty.purchase_date ?? '',
        purchase_price: warranty.purchase_price != null ? String(warranty.purchase_price) : '',
        retailer: warranty.retailer ?? '',
        warranty_duration_months: warranty.warranty_duration_months != null ? String(warranty.warranty_duration_months) : '',
        warranty_type: warranty.warranty_type ?? '',
        warranty_terms: warranty.warranty_terms ?? '',
        warranty_expiration_date: warranty.warranty_expiration_date ?? '',
        current_status: warranty.current_status,
        notes: warranty.notes ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/warranties/${warranty.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${warranty.product_name}`} />

            <PageHeader title={`Edit ${warranty.product_name}`} description="Update warranty details">
                <Button variant="outline" asChild>
                    <Link href={`/warranties/${warranty.id}`}>Back to Details</Link>
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
                            />
                            <FormField
                                label="Brand"
                                name="brand"
                                value={data.brand}
                                onChange={e => setData('brand', e.target.value)}
                                error={errors.brand}
                                required
                            />
                            <FormField
                                label="Model"
                                name="model"
                                value={data.model}
                                onChange={e => setData('model', e.target.value)}
                                error={errors.model}
                            />
                            <FormField
                                label="Serial Number"
                                name="serial_number"
                                value={data.serial_number}
                                onChange={e => setData('serial_number', e.target.value)}
                                error={errors.serial_number}
                            />
                            <FormField label="Status" name="current_status" error={errors.current_status}>
                                <Select value={data.current_status} onValueChange={v => setData('current_status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {statusOptions.map(s => (
                                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
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
                            />
                            <FormField
                                label="Retailer"
                                name="retailer"
                                value={data.retailer}
                                onChange={e => setData('retailer', e.target.value)}
                                error={errors.retailer}
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
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/warranties/${warranty.id}`}>Cancel</Link>
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

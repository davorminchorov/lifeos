import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import type { Customer } from '@/types/models'

interface CustomerEditProps {
    customer: Customer
}

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

export default function CustomerEdit({ customer }: CustomerEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: customer.name,
        email: customer.email ?? '',
        phone: customer.phone ?? '',
        company_name: customer.company_name ?? '',
        tax_id: customer.tax_id ?? '',
        tax_country: customer.tax_country ?? '',
        currency: customer.currency,
        notes: customer.notes ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/invoicing/customers/${customer.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${customer.name}`} />

            <PageHeader title={`Edit ${customer.name}`} description="Update customer details">
                <Button variant="outline" asChild>
                    <Link href={`/invoicing/customers/${customer.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="Customer contact details">
                            <FormField
                                label="Name"
                                name="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="Customer name"
                            />
                            <FormField
                                label="Email"
                                name="email"
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                error={errors.email}
                                placeholder="customer@example.com"
                            />
                            <FormField
                                label="Phone"
                                name="phone"
                                value={data.phone}
                                onChange={e => setData('phone', e.target.value)}
                                error={errors.phone}
                                placeholder="+1 234 567 890"
                            />
                            <FormField
                                label="Company Name"
                                name="company_name"
                                value={data.company_name}
                                onChange={e => setData('company_name', e.target.value)}
                                error={errors.company_name}
                                placeholder="Company Ltd."
                            />
                        </FormSection>

                        <FormSection title="Tax & Billing" description="Tax and currency settings">
                            <FormField
                                label="Tax ID"
                                name="tax_id"
                                value={data.tax_id}
                                onChange={e => setData('tax_id', e.target.value)}
                                error={errors.tax_id}
                                placeholder="Tax identification number"
                            />
                            <FormField
                                label="Tax Country"
                                name="tax_country"
                                value={data.tax_country}
                                onChange={e => setData('tax_country', e.target.value)}
                                error={errors.tax_country}
                                placeholder="e.g. MK, US"
                            />
                            <FormField label="Currency" name="currency" error={errors.currency} required>
                                <Select value={data.currency} onValueChange={v => setData('currency', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {currencies.map(c => (
                                            <SelectItem key={c} value={c}>{c}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Additional notes about this customer"
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/invoicing/customers/${customer.id}`}>Cancel</Link>
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

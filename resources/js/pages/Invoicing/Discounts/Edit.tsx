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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import type { Discount } from '@/types/models'

interface DiscountEditProps {
    discount: Discount
}

const discountTypes = [
    { value: 'percent', label: 'Percentage' },
    { value: 'fixed', label: 'Fixed Amount' },
]

export default function DiscountEdit({ discount }: DiscountEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        code: discount.code,
        type: discount.type as string,
        value: String(discount.value),
        active: discount.active,
        starts_at: discount.starts_at ?? '',
        ends_at: discount.ends_at ?? '',
        max_redemptions: discount.max_redemptions ? String(discount.max_redemptions) : '',
        description: discount.description ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/invoicing/discounts/${discount.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${discount.code}`} />

            <PageHeader title={`Edit ${discount.code}`} description="Update discount details">
                <Button variant="outline" asChild>
                    <Link href="/invoicing/discounts">Back to List</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Discount Details" description="Define the discount">
                            <FormField
                                label="Code"
                                name="code"
                                value={data.code}
                                onChange={e => setData('code', e.target.value.toUpperCase())}
                                error={errors.code}
                                required
                                placeholder="e.g. SUMMER20"
                            />
                            <FormField label="Type" name="type" error={errors.type} required>
                                <Select value={data.type} onValueChange={v => setData('type', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {discountTypes.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label={data.type === 'percent' ? 'Value (basis points, e.g. 2000 for 20%)' : 'Value (in cents)'}
                                name="value"
                                type="number"
                                value={data.value}
                                onChange={e => setData('value', e.target.value)}
                                error={errors.value}
                                required
                                min="0"
                                placeholder={data.type === 'percent' ? 'e.g. 2000' : 'e.g. 1000'}
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

                        <FormSection title="Validity & Limits" description="When and how often this discount can be used">
                            <FormField label="Starts At" name="starts_at" error={errors.starts_at}>
                                <DatePicker value={data.starts_at} onChange={v => setData('starts_at', v)} />
                            </FormField>
                            <FormField label="Ends At" name="ends_at" error={errors.ends_at}>
                                <DatePicker value={data.ends_at} onChange={v => setData('ends_at', v)} />
                            </FormField>
                            <FormField
                                label="Max Redemptions"
                                name="max_redemptions"
                                type="number"
                                value={data.max_redemptions}
                                onChange={e => setData('max_redemptions', e.target.value)}
                                error={errors.max_redemptions}
                                min="1"
                                placeholder="Leave empty for unlimited"
                            />
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
                                <Link href="/invoicing/discounts">Cancel</Link>
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

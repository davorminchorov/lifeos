import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Plus, Trash2 } from 'lucide-react'
import { formatCurrency } from '@/lib/utils'
import type { Invoice, Customer, TaxRate } from '@/types/models'

interface InvoiceEditProps {
    invoice: Invoice
    customers: Customer[]
    taxRates?: TaxRate[]
}

interface LineItem {
    name: string
    description: string
    quantity: string
    unit_amount: string
    tax_rate_id: string
}

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']
const taxBehaviors = [
    { value: 'exclusive', label: 'Tax Exclusive' },
    { value: 'inclusive', label: 'Tax Inclusive' },
]

const emptyLineItem: LineItem = {
    name: '',
    description: '',
    quantity: '1',
    unit_amount: '',
    tax_rate_id: '',
}

export default function InvoiceEdit({ invoice, customers, taxRates = [] }: InvoiceEditProps) {
    const existingItems: LineItem[] = (invoice.items ?? []).map(item => ({
        name: item.name,
        description: item.description ?? '',
        quantity: String(item.quantity),
        unit_amount: String(item.unit_amount / 100),
        tax_rate_id: item.tax_rate_id ? String(item.tax_rate_id) : '',
    }))

    const { data, setData, put, processing, errors } = useForm({
        customer_id: String(invoice.customer_id),
        currency: invoice.currency,
        tax_behavior: invoice.tax_behavior as string,
        net_terms_days: String(invoice.net_terms_days),
        due_at: invoice.due_at ?? '',
        notes: invoice.notes ?? '',
        internal_notes: invoice.internal_notes ?? '',
        items: existingItems.length > 0 ? existingItems : [{ ...emptyLineItem }],
    })

    const addLineItem = useCallback(() => {
        setData('items', [...data.items, { ...emptyLineItem }])
    }, [data.items, setData])

    const removeLineItem = useCallback((index: number) => {
        if (data.items.length <= 1) return
        setData('items', data.items.filter((_, i) => i !== index))
    }, [data.items, setData])

    const updateLineItem = useCallback((index: number, field: keyof LineItem, value: string) => {
        const updated = data.items.map((item, i) =>
            i === index ? { ...item, [field]: value } : item
        )
        setData('items', updated)
    }, [data.items, setData])

    const lineItemTotals = data.items.map((item) => {
        const qty = parseFloat(item.quantity) || 0
        const unitAmount = parseFloat(item.unit_amount) || 0
        const subtotal = qty * unitAmount
        const taxRate = taxRates.find(t => String(t.id) === item.tax_rate_id)
        const taxPercent = taxRate ? taxRate.percentage_basis_points / 10000 : 0
        const taxAmount = subtotal * taxPercent
        return { subtotal, taxAmount, total: subtotal + taxAmount }
    })

    const invoiceSubtotal = lineItemTotals.reduce((sum, t) => sum + t.subtotal, 0)
    const invoiceTax = lineItemTotals.reduce((sum, t) => sum + t.taxAmount, 0)
    const invoiceTotal = invoiceSubtotal + invoiceTax

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/invoicing/invoices/${invoice.id}`)
    }

    return (
        <AppLayout>
            <Head title={`Edit Invoice ${invoice.number ?? 'Draft'}`} />

            <PageHeader
                title={`Edit Invoice ${invoice.number ?? 'Draft'}`}
                description="Update draft invoice details"
            >
                <Button variant="outline" asChild>
                    <Link href={`/invoicing/invoices/${invoice.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Invoice Details" description="Basic invoice information">
                            <FormField label="Customer" name="customer_id" error={errors.customer_id} required>
                                <Select value={data.customer_id} onValueChange={v => setData('customer_id', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select customer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {customers.map(c => (
                                            <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
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
                            <FormField label="Tax Behavior" name="tax_behavior" error={errors.tax_behavior} required>
                                <Select value={data.tax_behavior} onValueChange={v => setData('tax_behavior', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {taxBehaviors.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Net Terms (Days)"
                                name="net_terms_days"
                                type="number"
                                value={data.net_terms_days}
                                onChange={e => setData('net_terms_days', e.target.value)}
                                error={errors.net_terms_days}
                                min="0"
                                max="365"
                            />
                            <FormField label="Due Date" name="due_at" error={errors.due_at}>
                                <DatePicker value={data.due_at} onChange={v => setData('due_at', v)} />
                            </FormField>
                        </FormSection>

                        {/* Line Items Editor */}
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-lg font-medium">Line Items</h3>
                                    <p className="text-sm text-muted-foreground">Edit items on this invoice</p>
                                </div>
                                <Button type="button" variant="outline" size="sm" onClick={addLineItem}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add Item
                                </Button>
                            </div>

                            <div className="rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="min-w-[200px]">Description</TableHead>
                                            <TableHead className="w-[100px]">Qty</TableHead>
                                            <TableHead className="w-[130px]">Unit Price</TableHead>
                                            <TableHead className="w-[150px]">Tax Rate</TableHead>
                                            <TableHead className="w-[120px] text-right">Subtotal</TableHead>
                                            <TableHead className="w-[50px]" />
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.items.map((item, index) => (
                                            <TableRow key={index}>
                                                <TableCell>
                                                    <Input
                                                        value={item.name}
                                                        onChange={e => updateLineItem(index, 'name', e.target.value)}
                                                        placeholder="Item name"
                                                        className="mb-1"
                                                    />
                                                    <Input
                                                        value={item.description}
                                                        onChange={e => updateLineItem(index, 'description', e.target.value)}
                                                        placeholder="Description (optional)"
                                                        className="text-xs"
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <Input
                                                        type="number"
                                                        value={item.quantity}
                                                        onChange={e => updateLineItem(index, 'quantity', e.target.value)}
                                                        min="0.01"
                                                        step="0.01"
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <Input
                                                        type="number"
                                                        value={item.unit_amount}
                                                        onChange={e => updateLineItem(index, 'unit_amount', e.target.value)}
                                                        min="0"
                                                        step="0.01"
                                                        placeholder="0.00"
                                                    />
                                                </TableCell>
                                                <TableCell>
                                                    <Select
                                                        value={item.tax_rate_id}
                                                        onValueChange={v => updateLineItem(index, 'tax_rate_id', v)}
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="None" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="">None</SelectItem>
                                                            {taxRates.map(t => (
                                                                <SelectItem key={t.id} value={String(t.id)}>
                                                                    {t.name} ({t.percentage_basis_points / 100}%)
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </TableCell>
                                                <TableCell className="text-right font-medium">
                                                    {formatCurrency(lineItemTotals[index]?.subtotal ?? 0, data.currency)}
                                                </TableCell>
                                                <TableCell>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        className="h-8 w-8"
                                                        onClick={() => removeLineItem(index)}
                                                        disabled={data.items.length <= 1}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            <div className="flex justify-end">
                                <div className="w-full max-w-xs space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Subtotal</span>
                                        <span className="font-medium">{formatCurrency(invoiceSubtotal, data.currency)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Tax</span>
                                        <span className="font-medium">{formatCurrency(invoiceTax, data.currency)}</span>
                                    </div>
                                    <div className="flex justify-between border-t pt-2 text-base">
                                        <span className="font-semibold">Total</span>
                                        <span className="font-semibold">{formatCurrency(invoiceTotal, data.currency)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <FormSection title="Notes">
                            <FormField
                                label="Customer Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Notes visible to the customer"
                            />
                            <FormField
                                label="Internal Notes"
                                name="internal_notes"
                                value={data.internal_notes}
                                onChange={e => setData('internal_notes', e.target.value)}
                                error={errors.internal_notes}
                                multiline
                                placeholder="Private notes (not visible to customer)"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/invoicing/invoices/${invoice.id}`}>Cancel</Link>
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

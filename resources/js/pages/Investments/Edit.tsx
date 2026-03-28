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
import type { Investment } from '@/types/models'

interface InvestmentEditProps {
    investment: Investment
}

const investmentTypes = [
    { value: 'stock', label: 'Stocks' },
    { value: 'bond', label: 'Bonds' },
    { value: 'crypto', label: 'Cryptocurrency' },
    { value: 'real_estate', label: 'Real Estate' },
    { value: 'mutual_fund', label: 'Mutual Fund' },
    { value: 'etf', label: 'ETF' },
    { value: 'commodities', label: 'Commodities' },
    { value: 'cash', label: 'Cash' },
]

const currencies = ['MKD', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'RSD', 'BGN']

const riskTolerances = [
    { value: 'conservative', label: 'Conservative' },
    { value: 'moderate', label: 'Moderate' },
    { value: 'aggressive', label: 'Aggressive' },
]

const investmentGoalOptions = ['retirement', 'growth', 'income', 'speculation']

export default function InvestmentEdit({ investment }: InvestmentEditProps) {
    const existingGoals = Array.isArray(investment.investment_goals) ? investment.investment_goals as string[] : []

    const { data, setData, put, processing, errors } = useForm({
        name: investment.name,
        investment_type: investment.investment_type,
        symbol_identifier: investment.symbol_identifier ?? '',
        quantity: String(investment.quantity),
        purchase_price: String(investment.purchase_price),
        currency: investment.currency ?? 'MKD',
        purchase_date: investment.purchase_date,
        total_fees_paid: String(investment.total_fees_paid),
        account_broker: investment.account_broker ?? '',
        account_number: investment.account_number ?? '',
        risk_tolerance: investment.risk_tolerance ?? 'moderate',
        target_allocation_percentage: investment.target_allocation_percentage ? String(investment.target_allocation_percentage) : '',
        investment_goals: existingGoals,
        status: investment.status,
        notes: investment.notes ?? '',
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        put(`/investments/${investment.id}`)
    }

    function toggleGoal(goal: string) {
        const goals = data.investment_goals.includes(goal)
            ? data.investment_goals.filter(g => g !== goal)
            : [...data.investment_goals, goal]
        setData('investment_goals', goals)
    }

    return (
        <AppLayout>
            <Head title={`Edit ${investment.name}`} />

            <PageHeader title={`Edit ${investment.name}`} description="Update investment details">
                <Button variant="outline" asChild>
                    <Link href={`/investments/${investment.id}`}>Back to Details</Link>
                </Button>
            </PageHeader>

            <Card>
                <CardContent className="p-6">
                    <form onSubmit={handleSubmit} className="space-y-8">
                        <FormSection title="Basic Information" description="General details about the investment">
                            <FormField
                                label="Investment Name"
                                name="name"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="e.g., Apple Inc., Bitcoin, S&P 500 ETF"
                            />
                            <FormField label="Investment Type" name="investment_type" error={errors.investment_type} required>
                                <Select value={data.investment_type} onValueChange={v => setData('investment_type', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {investmentTypes.map(t => (
                                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Symbol/Ticker"
                                name="symbol_identifier"
                                value={data.symbol_identifier}
                                onChange={e => setData('symbol_identifier', e.target.value)}
                                error={errors.symbol_identifier}
                                placeholder="e.g., AAPL, BTC, SPY"
                            />
                            <FormField label="Status" name="status" error={errors.status} required>
                                <Select value={data.status} onValueChange={v => setData('status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="monitoring">Monitoring</SelectItem>
                                        <SelectItem value="sold">Sold</SelectItem>
                                    </SelectContent>
                                </Select>
                            </FormField>
                        </FormSection>

                        <FormSection title="Purchase Details" description="Cost and quantity information">
                            <FormField
                                label="Quantity"
                                name="quantity"
                                type="number"
                                value={data.quantity}
                                onChange={e => setData('quantity', e.target.value)}
                                error={errors.quantity}
                                required
                                min="0"
                                step="0.00000001"
                                placeholder="0.00"
                            />
                            <FormField
                                label="Purchase Price (per unit)"
                                name="purchase_price"
                                type="number"
                                value={data.purchase_price}
                                onChange={e => setData('purchase_price', e.target.value)}
                                error={errors.purchase_price}
                                required
                                min="0"
                                step="0.00000001"
                                placeholder="0.00"
                            />
                            <FormField label="Currency" name="currency" error={errors.currency}>
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
                            <FormField label="Purchase Date" name="purchase_date" error={errors.purchase_date} required>
                                <DatePicker value={data.purchase_date} onChange={v => setData('purchase_date', v)} />
                            </FormField>
                            <FormField
                                label="Total Fees Paid"
                                name="total_fees_paid"
                                type="number"
                                value={data.total_fees_paid}
                                onChange={e => setData('total_fees_paid', e.target.value)}
                                error={errors.total_fees_paid}
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            />
                        </FormSection>

                        <FormSection title="Account & Risk" description="Broker and risk information">
                            <FormField
                                label="Broker/Platform"
                                name="account_broker"
                                value={data.account_broker}
                                onChange={e => setData('account_broker', e.target.value)}
                                error={errors.account_broker}
                                placeholder="e.g., Fidelity, Robinhood"
                            />
                            <FormField
                                label="Account Number"
                                name="account_number"
                                value={data.account_number}
                                onChange={e => setData('account_number', e.target.value)}
                                error={errors.account_number}
                                placeholder="Optional account number"
                            />
                            <FormField label="Risk Tolerance" name="risk_tolerance" error={errors.risk_tolerance}>
                                <Select value={data.risk_tolerance} onValueChange={v => setData('risk_tolerance', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {riskTolerances.map(r => (
                                            <SelectItem key={r.value} value={r.value}>{r.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Target Allocation %"
                                name="target_allocation_percentage"
                                type="number"
                                value={data.target_allocation_percentage}
                                onChange={e => setData('target_allocation_percentage', e.target.value)}
                                error={errors.target_allocation_percentage}
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0.00"
                            />
                        </FormSection>

                        <FormSection title="Investment Goals" description="Select goals that apply to this investment">
                            <div className="flex flex-wrap gap-4 sm:col-span-2">
                                {investmentGoalOptions.map(goal => (
                                    <div key={goal} className="flex items-center gap-2">
                                        <Checkbox
                                            id={`goal_${goal}`}
                                            checked={data.investment_goals.includes(goal)}
                                            onCheckedChange={() => toggleGoal(goal)}
                                        />
                                        <Label htmlFor={`goal_${goal}`} className="capitalize">{goal}</Label>
                                    </div>
                                ))}
                            </div>
                        </FormSection>

                        <FormSection title="Additional Information">
                            <FormField
                                label="Notes"
                                name="notes"
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                error={errors.notes}
                                multiline
                                placeholder="Investment thesis, research notes..."
                                className="sm:col-span-2"
                            />
                        </FormSection>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href={`/investments/${investment.id}`}>Cancel</Link>
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

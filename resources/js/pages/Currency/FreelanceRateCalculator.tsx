import { Head } from '@inertiajs/react'
import { useState, useMemo, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { Calculator, DollarSign, Calendar, Briefcase } from 'lucide-react'

interface FreelanceRateCalculatorProps {
    working_days: number
    month_name: string
    total_days: number
    holidays_in_month: number
    weekends_in_month: number
}

const currencies = [
    { value: 'MKD', label: 'MKD - Macedonian Denar', symbol: 'MKD' },
    { value: 'USD', label: 'USD - US Dollar', symbol: '$' },
    { value: 'EUR', label: 'EUR - Euro', symbol: '\u20AC' },
    { value: 'GBP', label: 'GBP - British Pound', symbol: '\u00A3' },
    { value: 'CAD', label: 'CAD - Canadian Dollar', symbol: 'C$' },
    { value: 'AUD', label: 'AUD - Australian Dollar', symbol: 'A$' },
    { value: 'JPY', label: 'JPY - Japanese Yen', symbol: '\u00A5' },
    { value: 'CHF', label: 'CHF - Swiss Franc', symbol: 'CHF' },
    { value: 'RSD', label: 'RSD - Serbian Dinar', symbol: 'RSD' },
    { value: 'BGN', label: 'BGN - Bulgarian Lev', symbol: '\u043B\u0432' },
]

function formatMoney(amount: number, symbol: string): string {
    return `${symbol}${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

export default function FreelanceRateCalculator({
    working_days,
    month_name,
    total_days,
    holidays_in_month,
    weekends_in_month,
}: FreelanceRateCalculatorProps) {
    const [workType, setWorkType] = useState<'part-time' | 'full-time'>('full-time')
    const [currency, setCurrency] = useState('USD')
    const [annualIncome, setAnnualIncome] = useState(60000)
    const [hoursPerWeek, setHoursPerWeek] = useState(40)
    const [weeksPerYear, setWeeksPerYear] = useState(48)
    const [expensesPercentage, setExpensesPercentage] = useState(20)
    const [profitMargin, setProfitMargin] = useState(15)
    const [taxRate, setTaxRate] = useState(25)

    // Monthly earnings calculator state
    const [earningsHourlyRate, setEarningsHourlyRate] = useState(50)
    const [earningsHoursPerDay, setEarningsHoursPerDay] = useState(8)

    const currencySymbol = currencies.find(c => c.value === currency)?.symbol ?? currency

    const handleWorkTypeChange = useCallback((type: 'part-time' | 'full-time') => {
        setWorkType(type)
        if (type === 'part-time') {
            setHoursPerWeek(25)
        } else {
            setHoursPerWeek(40)
        }
    }, [])

    const calculations = useMemo(() => {
        const totalHours = hoursPerWeek * weeksPerYear
        const baseRate = totalHours > 0 ? annualIncome / totalHours : 0
        const expensesAmount = baseRate * (expensesPercentage / 100)
        const afterExpenses = baseRate + expensesAmount
        const profitAmount = afterExpenses * (profitMargin / 100)
        const afterProfit = afterExpenses + profitAmount
        const taxAmount = afterProfit * (taxRate / 100)
        const hourlyRate = afterProfit + taxAmount

        const weeklyIncome = hourlyRate * hoursPerWeek
        const monthlyIncome = weeklyIncome * (weeksPerYear / 12)
        const yearlyIncome = hourlyRate * totalHours

        return {
            baseRate,
            expensesAmount,
            profitAmount,
            taxAmount,
            hourlyRate,
            weeklyIncome,
            monthlyIncome,
            yearlyIncome,
        }
    }, [annualIncome, hoursPerWeek, weeksPerYear, expensesPercentage, profitMargin, taxRate])

    const monthlyEarnings = useMemo(() => {
        const dailyEarnings = earningsHourlyRate * earningsHoursPerDay
        const monthlyTotal = dailyEarnings * working_days

        return {
            dailyEarnings,
            monthlyTotal,
            workingDays: working_days,
        }
    }, [earningsHourlyRate, earningsHoursPerDay, working_days])

    return (
        <AppLayout>
            <Head title="Freelance Hourly Rate Calculator" />

            <PageHeader
                title="Freelance Hourly Rate Calculator"
                description="Calculate your ideal freelance hourly rate based on your desired income and expenses."
            />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Input Form */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Calculator className="h-5 w-5" />
                            Your Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-5">
                        {/* Work Type Toggle */}
                        <div>
                            <Label className="mb-3 block">Work Type</Label>
                            <div className="flex gap-3">
                                <Button
                                    type="button"
                                    variant={workType === 'part-time' ? 'default' : 'outline'}
                                    onClick={() => handleWorkTypeChange('part-time')}
                                    className="flex-1"
                                >
                                    <div className="text-center">
                                        <div className="font-semibold">Part-Time</div>
                                        <div className="text-xs opacity-75">20-30 hours/week</div>
                                    </div>
                                </Button>
                                <Button
                                    type="button"
                                    variant={workType === 'full-time' ? 'default' : 'outline'}
                                    onClick={() => handleWorkTypeChange('full-time')}
                                    className="flex-1"
                                >
                                    <div className="text-center">
                                        <div className="font-semibold">Full-Time</div>
                                        <div className="text-xs opacity-75">40 hours/week</div>
                                    </div>
                                </Button>
                            </div>
                        </div>

                        {/* Currency */}
                        <div className="space-y-2">
                            <Label htmlFor="currency">Currency</Label>
                            <Select value={currency} onValueChange={setCurrency}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {currencies.map(c => (
                                        <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Annual Income */}
                        <div className="space-y-2">
                            <Label htmlFor="annual-income">Desired Annual Income</Label>
                            <Input
                                id="annual-income"
                                type="number"
                                value={annualIncome}
                                onChange={e => setAnnualIncome(Number(e.target.value))}
                                min={0}
                                step={1000}
                            />
                        </div>

                        {/* Hours per Week */}
                        <div className="space-y-2">
                            <Label htmlFor="hours-per-week">Working Hours per Week</Label>
                            <Input
                                id="hours-per-week"
                                type="number"
                                value={hoursPerWeek}
                                onChange={e => setHoursPerWeek(Number(e.target.value))}
                                min={1}
                                max={80}
                            />
                        </div>

                        {/* Weeks per Year */}
                        <div className="space-y-2">
                            <Label htmlFor="weeks-per-year">Working Weeks per Year</Label>
                            <Input
                                id="weeks-per-year"
                                type="number"
                                value={weeksPerYear}
                                onChange={e => setWeeksPerYear(Number(e.target.value))}
                                min={1}
                                max={52}
                            />
                            <p className="text-xs text-muted-foreground">52 weeks minus vacation time (typically 2-4 weeks)</p>
                        </div>

                        {/* Business Expenses */}
                        <div className="space-y-2">
                            <Label htmlFor="expenses-percentage">Business Expenses (%)</Label>
                            <Input
                                id="expenses-percentage"
                                type="number"
                                value={expensesPercentage}
                                onChange={e => setExpensesPercentage(Number(e.target.value))}
                                min={0}
                                max={100}
                            />
                            <p className="text-xs text-muted-foreground">Software, equipment, insurance, etc. (typically 15-30%)</p>
                        </div>

                        {/* Profit Margin */}
                        <div className="space-y-2">
                            <Label htmlFor="profit-margin">Profit Margin (%)</Label>
                            <Input
                                id="profit-margin"
                                type="number"
                                value={profitMargin}
                                onChange={e => setProfitMargin(Number(e.target.value))}
                                min={0}
                                max={100}
                            />
                            <p className="text-xs text-muted-foreground">Your business profit (typically 10-20%)</p>
                        </div>

                        {/* Tax Rate */}
                        <div className="space-y-2">
                            <Label htmlFor="tax-rate">Tax Rate (%)</Label>
                            <Input
                                id="tax-rate"
                                type="number"
                                value={taxRate}
                                onChange={e => setTaxRate(Number(e.target.value))}
                                min={0}
                                max={100}
                            />
                            <p className="text-xs text-muted-foreground">Your estimated tax rate (varies by location)</p>
                        </div>
                    </CardContent>
                </Card>

                {/* Results */}
                <div className="space-y-4">
                    {/* Hourly Rate */}
                    <Card className="border-primary bg-primary text-primary-foreground">
                        <CardContent className="p-6">
                            <p className="text-sm opacity-90">Recommended Hourly Rate</p>
                            <p className="text-4xl font-bold">
                                {formatMoney(calculations.hourlyRate, currencySymbol)}
                            </p>
                            <p className="text-sm opacity-90">Per hour</p>
                        </CardContent>
                    </Card>

                    {/* Weekly Income */}
                    <Card>
                        <CardContent className="flex items-center justify-between p-6">
                            <div>
                                <p className="text-sm text-muted-foreground">Weekly Income</p>
                                <p className="text-2xl font-bold">
                                    {formatMoney(calculations.weeklyIncome, currencySymbol)}
                                </p>
                            </div>
                            <DollarSign className="h-10 w-10 text-muted-foreground/20" />
                        </CardContent>
                    </Card>

                    {/* Monthly Income */}
                    <Card>
                        <CardContent className="flex items-center justify-between p-6">
                            <div>
                                <p className="text-sm text-muted-foreground">Monthly Income</p>
                                <p className="text-2xl font-bold">
                                    {formatMoney(calculations.monthlyIncome, currencySymbol)}
                                </p>
                            </div>
                            <Calendar className="h-10 w-10 text-muted-foreground/20" />
                        </CardContent>
                    </Card>

                    {/* Yearly Income */}
                    <Card>
                        <CardContent className="flex items-center justify-between p-6">
                            <div>
                                <p className="text-sm text-muted-foreground">Yearly Income</p>
                                <p className="text-2xl font-bold">
                                    {formatMoney(calculations.yearlyIncome, currencySymbol)}
                                </p>
                            </div>
                            <Briefcase className="h-10 w-10 text-muted-foreground/20" />
                        </CardContent>
                    </Card>

                    {/* Working Days */}
                    <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950">
                        <CardContent className="flex items-center justify-between p-6">
                            <div>
                                <p className="text-sm font-medium text-green-700 dark:text-green-300">
                                    Working Days in {month_name}
                                </p>
                                <p className="text-3xl font-bold text-green-800 dark:text-green-200">
                                    {working_days}
                                </p>
                                <p className="text-xs text-green-600 dark:text-green-400">
                                    Weekdays excluding holidays
                                </p>
                            </div>
                            <Calendar className="h-10 w-10 text-green-500/20" />
                        </CardContent>
                    </Card>

                    {/* Rate Breakdown */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm">Rate Breakdown</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Base hourly rate:</span>
                                <span className="font-medium">{formatMoney(calculations.baseRate, currencySymbol)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">+ Business expenses ({expensesPercentage}%):</span>
                                <span className="font-medium">{formatMoney(calculations.expensesAmount, currencySymbol)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">+ Profit margin ({profitMargin}%):</span>
                                <span className="font-medium">{formatMoney(calculations.profitAmount, currencySymbol)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">+ Taxes ({taxRate}%):</span>
                                <span className="font-medium">{formatMoney(calculations.taxAmount, currencySymbol)}</span>
                            </div>
                            <Separator />
                            <div className="flex justify-between font-semibold">
                                <span>Total hourly rate:</span>
                                <span>{formatMoney(calculations.hourlyRate, currencySymbol)}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Monthly Earnings Calculator */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Monthly Earnings Calculator</CardTitle>
                    <p className="text-sm text-muted-foreground">
                        Estimate how much you will earn this month based on your hourly rate.
                    </p>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="earnings-hourly-rate">Hourly Rate ({currencySymbol})</Label>
                                <Input
                                    id="earnings-hourly-rate"
                                    type="number"
                                    value={earningsHourlyRate}
                                    onChange={e => setEarningsHourlyRate(Number(e.target.value))}
                                    min={0}
                                    step={1}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="earnings-hours-per-day">Hours per Day</Label>
                                <Input
                                    id="earnings-hours-per-day"
                                    type="number"
                                    value={earningsHoursPerDay}
                                    onChange={e => setEarningsHoursPerDay(Number(e.target.value))}
                                    min={1}
                                    max={24}
                                />
                            </div>
                        </div>

                        <div className="flex items-center justify-center">
                            <div className="text-center">
                                <p className="text-sm text-muted-foreground">Working Days</p>
                                <p className="text-4xl font-bold">{working_days}</p>
                                <p className="text-xs text-muted-foreground">in {month_name}</p>
                            </div>
                        </div>

                        <div className="space-y-3">
                            <div className="rounded-lg border border-border p-4">
                                <p className="text-sm text-muted-foreground">Daily Earnings</p>
                                <p className="text-xl font-bold">
                                    {formatMoney(monthlyEarnings.dailyEarnings, currencySymbol)}
                                </p>
                            </div>
                            <div className="rounded-lg border-2 border-primary bg-primary/5 p-4">
                                <p className="text-sm text-muted-foreground">Monthly Total</p>
                                <p className="text-2xl font-bold text-primary">
                                    {formatMoney(monthlyEarnings.monthlyTotal, currencySymbol)}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {working_days} days &times; {formatMoney(monthlyEarnings.dailyEarnings, currencySymbol)}/day
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

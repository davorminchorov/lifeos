import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Pencil, Trash2, ArrowLeft } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Budget, Expense } from '@/types/models'

interface BudgetShowProps {
    budget: Budget & {
        current_spending: number
        remaining_amount: number
        utilization_percentage: number
        status: string
    }
    expenses: Expense[]
    dailySpending: Record<string, number>
    projectedSpending: number
}

export default function BudgetShow({ budget, expenses, dailySpending, projectedSpending }: BudgetShowProps) {
    const [confirmDelete, setConfirmDelete] = useState(false)

    const handleDelete = useCallback(() => {
        router.delete(`/budgets/${budget.id}`, {
            onFinish: () => setConfirmDelete(false),
        })
    }, [budget.id])

    const currency = budget.currency ?? 'MKD'
    const dailyEntries = Object.entries(dailySpending).sort(([a], [b]) => b.localeCompare(a))
    const isOverBudget = budget.utilization_percentage >= 100
    const isWarning = budget.utilization_percentage >= budget.alert_threshold

    return (
        <AppLayout>
            <Head title={`${budget.category} Budget`} />

            <PageHeader
                title={`${budget.category} Budget`}
                description={`${budget.budget_period.charAt(0).toUpperCase() + budget.budget_period.slice(1)} budget from ${formatDate(budget.start_date)} to ${formatDate(budget.end_date)}`}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href="/budgets">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/budgets/${budget.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="destructive" size="sm" onClick={() => setConfirmDelete(true)}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Budget Amount</p>
                        <p className="text-xl font-semibold">{formatCurrency(budget.amount, currency)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Amount Spent</p>
                        <p className={`text-xl font-semibold ${isOverBudget ? 'text-destructive' : ''}`}>
                            {formatCurrency(budget.current_spending, currency)}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Remaining</p>
                        <p className="text-xl font-semibold">{formatCurrency(budget.remaining_amount, currency)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Projected Spending</p>
                        <p className={`text-xl font-semibold ${projectedSpending > budget.amount ? 'text-destructive' : ''}`}>
                            {formatCurrency(projectedSpending, currency)}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div className="mb-6">
                <Card>
                    <CardContent className="p-4">
                        <div className="mb-2 flex items-center justify-between">
                            <span className="text-sm font-medium">Budget Utilization</span>
                            <span className="text-sm text-muted-foreground">{budget.utilization_percentage}%</span>
                        </div>
                        <div className="h-3 w-full rounded-full bg-muted">
                            <div
                                className={`h-3 rounded-full transition-all ${
                                    isOverBudget
                                        ? 'bg-destructive'
                                        : isWarning
                                            ? 'bg-yellow-500'
                                            : 'bg-green-500'
                                }`}
                                style={{ width: `${Math.min(budget.utilization_percentage, 100)}%` }}
                            />
                        </div>
                        <div className="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                            <span>0%</span>
                            <span>Alert: {budget.alert_threshold}%</span>
                            <span>100%</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Budget Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Category</p>
                                    <p className="font-medium">{budget.category}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={budget.is_active ? budget.status : 'inactive'} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Period</p>
                                    <p className="font-medium capitalize">{budget.budget_period}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Currency</p>
                                    <p className="font-medium">{currency}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Start Date</p>
                                    <p className="font-medium">{formatDate(budget.start_date)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">End Date</p>
                                    <p className="font-medium">{formatDate(budget.end_date)}</p>
                                </div>
                            </div>
                            {budget.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{budget.notes}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {expenses.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Expenses in This Period</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Description</TableHead>
                                                <TableHead>Merchant</TableHead>
                                                <TableHead className="text-right">Amount</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {expenses.map((expense) => (
                                                <TableRow key={expense.id}>
                                                    <TableCell className="text-sm">
                                                        {formatDate(expense.expense_date)}
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {expense.description ?? '\u2014'}
                                                    </TableCell>
                                                    <TableCell className="text-sm text-muted-foreground">
                                                        {expense.merchant ?? '\u2014'}
                                                    </TableCell>
                                                    <TableCell className="text-right font-medium">
                                                        {formatCurrency(expense.amount, expense.currency ?? currency)}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Settings</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Active</p>
                                <p className="font-medium">{budget.is_active ? 'Yes' : 'No'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Alert Threshold</p>
                                <p className="font-medium">{budget.alert_threshold}%</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Rollover Unused</p>
                                <p className="font-medium">{budget.rollover_unused ? 'Yes' : 'No'}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {dailyEntries.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Daily Spending</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    {dailyEntries.map(([date, amount]) => (
                                        <div key={date} className="flex items-center justify-between text-sm">
                                            <span className="text-muted-foreground">{formatDate(date)}</span>
                                            <span className="font-medium">{formatCurrency(amount, currency)}</span>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>
            </div>

            <ConfirmationDialog
                open={confirmDelete}
                onOpenChange={setConfirmDelete}
                title="Delete Budget"
                description="Are you sure you want to delete this budget? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

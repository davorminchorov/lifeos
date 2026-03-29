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
import type { Investment, InvestmentTransaction, InvestmentDividend } from '@/types/models'

interface InvestmentShowProps {
    investment: Investment & {
        transactions?: InvestmentTransaction[]
        dividends?: InvestmentDividend[]
    }
}

export default function InvestmentShow({ investment }: InvestmentShowProps) {
    const [confirmDelete, setConfirmDelete] = useState(false)

    const handleDelete = useCallback(() => {
        router.delete(`/investments/${investment.id}`, {
            onFinish: () => setConfirmDelete(false),
        })
    }, [investment.id])

    const currency = investment.currency ?? 'USD'
    const totalCostBasis = investment.purchase_price * investment.quantity
    const unrealizedGainLoss = investment.current_value !== null
        ? investment.current_value - totalCostBasis
        : null
    const gainLossPercentage = unrealizedGainLoss !== null && totalCostBasis > 0
        ? (unrealizedGainLoss / totalCostBasis) * 100
        : null
    const investmentGoals = Array.isArray(investment.investment_goals) ? investment.investment_goals as string[] : []
    const transactions = investment.transactions ?? []
    const dividends = investment.dividends ?? []

    return (
        <AppLayout>
            <Head title={investment.name} />

            <PageHeader title={investment.name} description={investment.symbol_identifier ?? undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/investments">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/investments/${investment.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="destructive" size="sm" onClick={() => setConfirmDelete(true)}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Investment Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Name</p>
                                    <p className="font-medium">{investment.name}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={investment.status} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Type</p>
                                    <p className="font-medium capitalize">{investment.investment_type.replace('_', ' ')}</p>
                                </div>
                                {investment.symbol_identifier ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Symbol</p>
                                        <p className="font-medium">{investment.symbol_identifier}</p>
                                    </div>
                                ) : null}
                                {investment.risk_tolerance ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Risk Tolerance</p>
                                        <p className="font-medium capitalize">{investment.risk_tolerance}</p>
                                    </div>
                                ) : null}
                                {investment.account_broker ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Broker/Platform</p>
                                        <p className="font-medium">{investment.account_broker}</p>
                                    </div>
                                ) : null}
                            </div>
                            {investmentGoals.length > 0 ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Investment Goals</p>
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {investmentGoals.map((goal) => (
                                                <span key={String(goal)} className="rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium capitalize text-secondary-foreground">
                                                    {String(goal)}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </>
                            ) : null}
                            {investment.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{investment.notes}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Purchase Date</p>
                                    <p className="font-medium">{formatDate(investment.purchase_date)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Purchase Price (per unit)</p>
                                    <p className="font-medium">{formatCurrency(investment.purchase_price, currency)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Quantity</p>
                                    <p className="font-medium">{investment.quantity}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Total Cost Basis</p>
                                    <p className="font-medium">{formatCurrency(totalCostBasis, currency)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Total Fees Paid</p>
                                    <p className="font-medium">{formatCurrency(investment.total_fees_paid, currency)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Total Dividends Received</p>
                                    <p className="font-medium">{formatCurrency(investment.total_dividends_received, currency)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {transactions.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Transaction History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Type</TableHead>
                                                <TableHead>Quantity</TableHead>
                                                <TableHead>Price/Share</TableHead>
                                                <TableHead>Total</TableHead>
                                                <TableHead>Fees</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {transactions.map((tx) => (
                                                <TableRow key={tx.id}>
                                                    <TableCell>{formatDate(tx.transaction_date)}</TableCell>
                                                    <TableCell className="capitalize">{tx.transaction_type}</TableCell>
                                                    <TableCell>{tx.quantity}</TableCell>
                                                    <TableCell>{formatCurrency(tx.price_per_share, tx.currency ?? currency)}</TableCell>
                                                    <TableCell>{formatCurrency(tx.total_amount, tx.currency ?? currency)}</TableCell>
                                                    <TableCell>{formatCurrency(tx.fees, tx.currency ?? currency)}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {dividends.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Dividend History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Payment Date</TableHead>
                                                <TableHead>Amount</TableHead>
                                                <TableHead>Per Share</TableHead>
                                                <TableHead>Type</TableHead>
                                                <TableHead>Reinvested</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {dividends.map((div) => (
                                                <TableRow key={div.id}>
                                                    <TableCell>{div.payment_date ? formatDate(div.payment_date) : '\u2014'}</TableCell>
                                                    <TableCell>{formatCurrency(div.amount, div.currency ?? currency)}</TableCell>
                                                    <TableCell>{div.dividend_per_share ? formatCurrency(div.dividend_per_share, div.currency ?? currency) : '\u2014'}</TableCell>
                                                    <TableCell className="capitalize">{div.dividend_type ?? '\u2014'}</TableCell>
                                                    <TableCell>{div.reinvested ? 'Yes' : 'No'}</TableCell>
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
                            <CardTitle>Valuation</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Current Value</p>
                                <p className="text-2xl font-semibold">
                                    {investment.current_value !== null
                                        ? formatCurrency(investment.current_value, currency)
                                        : '\u2014'}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Cost Basis</p>
                                <p className="font-medium">{formatCurrency(totalCostBasis, currency)}</p>
                            </div>
                            {unrealizedGainLoss !== null ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Unrealized Gain/Loss</p>
                                    <p className={`font-medium ${unrealizedGainLoss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                        {unrealizedGainLoss >= 0 ? '+' : ''}{formatCurrency(unrealizedGainLoss, currency)}
                                        {gainLossPercentage !== null ? (
                                            <span className="ml-1 text-sm">({gainLossPercentage >= 0 ? '+' : ''}{gainLossPercentage.toFixed(2)}%)</span>
                                        ) : null}
                                    </p>
                                </div>
                            ) : null}
                            {investment.target_allocation_percentage !== null ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Target Allocation</p>
                                    <p className="font-medium">{investment.target_allocation_percentage}%</p>
                                </div>
                            ) : null}
                            {investment.last_price_update ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Last Price Update</p>
                                    <p className="font-medium">{formatDate(investment.last_price_update)}</p>
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={confirmDelete}
                onOpenChange={setConfirmDelete}
                title="Delete Investment"
                description="Are you sure you want to delete this investment? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

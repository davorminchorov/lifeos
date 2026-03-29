import { Head } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Coins, RefreshCw, Info } from 'lucide-react'

interface CurrencyRate {
    from_currency: string
    to_currency: string
    rate_info: {
        rate: number
        freshness: string
        last_updated: number | null
        age_seconds: number
    }
    is_fresh: boolean
    is_stale: boolean
    formatted_age: string
}

interface CurrencyIndexProps {
    currencyRates: CurrencyRate[]
    defaultCurrency: string
}

function getFreshnessVariant(rate: CurrencyRate): 'success' | 'warning' | 'danger' {
    if (rate.is_fresh) return 'success'
    if (rate.is_stale) return 'danger'
    return 'warning'
}

function getFreshnessLabel(rate: CurrencyRate): string {
    if (rate.is_fresh) return 'fresh'
    if (rate.is_stale) return 'stale'
    return 'warning'
}

function formatLastUpdated(timestamp: number | null): { date: string; time: string } | null {
    if (!timestamp) return null
    const d = new Date(timestamp * 1000)
    return {
        date: d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        time: d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }),
    }
}

export default function CurrencyIndex({ currencyRates, defaultCurrency }: CurrencyIndexProps) {
    const [refreshing, setRefreshing] = useState<string | null>(null)

    const handleRefresh = useCallback(async (fromCurrency: string, toCurrency: string) => {
        const key = `${fromCurrency}-${toCurrency}`
        setRefreshing(key)

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
            const response = await fetch('/currency/refresh-rate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    from_currency: fromCurrency,
                    to_currency: toCurrency,
                }),
            })

            const data = await response.json()

            if (data.success) {
                window.location.reload()
            } else {
                alert(`Failed to refresh: ${data.message ?? 'Unknown error'}`)
            }
        } catch {
            alert('Failed to refresh exchange rate. Please try again.')
        } finally {
            setRefreshing(null)
        }
    }, [])

    return (
        <AppLayout>
            <Head title="Currency Exchange Rates" />

            <PageHeader
                title="Currency Exchange Rates"
                description={`Monitor exchange rate freshness and update rates as needed. All rates are converted to ${defaultCurrency}.`}
            />

            {currencyRates.length > 0 ? (
                <>
                    <div className="rounded-md border border-border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Currency Pair</TableHead>
                                    <TableHead>Exchange Rate</TableHead>
                                    <TableHead>Freshness</TableHead>
                                    <TableHead>Last Updated</TableHead>
                                    <TableHead className="w-[140px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {currencyRates.map((rate) => {
                                    const key = `${rate.from_currency}-${rate.to_currency}`
                                    const lastUpdated = formatLastUpdated(rate.rate_info.last_updated)

                                    return (
                                        <TableRow key={key}>
                                            <TableCell className="font-medium">
                                                {rate.from_currency} &rarr; {rate.to_currency}
                                            </TableCell>
                                            <TableCell>
                                                {rate.rate_info.rate.toFixed(4)}
                                            </TableCell>
                                            <TableCell>
                                                <StatusBadge
                                                    status={getFreshnessLabel(rate)}
                                                    variant={getFreshnessVariant(rate)}
                                                />
                                            </TableCell>
                                            <TableCell className="text-sm">
                                                {lastUpdated ? (
                                                    <div>
                                                        <div>{lastUpdated.date}</div>
                                                        <div className="text-xs text-muted-foreground">{lastUpdated.time}</div>
                                                        <div className="text-xs text-muted-foreground">{rate.formatted_age} ago</div>
                                                    </div>
                                                ) : (
                                                    <span className="text-muted-foreground">Never updated</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    disabled={refreshing === key}
                                                    onClick={() => handleRefresh(rate.from_currency, rate.to_currency)}
                                                >
                                                    <RefreshCw className={`mr-1 h-3 w-3 ${refreshing === key ? 'animate-spin' : ''}`} />
                                                    {refreshing === key ? 'Refreshing...' : 'Refresh'}
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    )
                                })}
                            </TableBody>
                        </Table>
                    </div>

                    <Card className="mt-6">
                        <CardHeader className="flex flex-row items-center gap-2 space-y-0 pb-3">
                            <Info className="h-4 w-4 text-blue-500" />
                            <CardTitle className="text-sm font-medium">About Currency Freshness</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul className="list-inside list-disc space-y-1 text-sm text-muted-foreground">
                                <li><span className="font-medium text-foreground">Fresh:</span> Exchange rate was updated within the last 24 hours</li>
                                <li><span className="font-medium text-foreground">Stale:</span> Exchange rate is 1-7 days old and may be slightly outdated</li>
                                <li><span className="font-medium text-foreground">Warning:</span> Exchange rate is over 7 days old and should be refreshed</li>
                            </ul>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Click &quot;Refresh&quot; to get the latest exchange rate from your configured provider.
                            </p>
                        </CardContent>
                    </Card>
                </>
            ) : (
                <EmptyState
                    icon={Coins}
                    title="No Exchange Rates"
                    description={`No currency exchange rates are currently available. This could be because all supported currencies are the same as the default currency (${defaultCurrency}).`}
                />
            )}
        </AppLayout>
    )
}

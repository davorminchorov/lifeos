import { type ReactNode } from 'react'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { RefreshCw, Download } from 'lucide-react'
import { cn } from '@/lib/utils'

interface ChartContainerProps {
    title: string
    children: ReactNode
    className?: string
    period?: string
    onPeriodChange?: (period: string) => void
    periods?: { value: string; label: string }[]
    onRefresh?: () => void
    onExport?: () => void
    isLoading?: boolean
}

const DEFAULT_PERIODS = [
    { value: '3m', label: '3 Months' },
    { value: '6m', label: '6 Months' },
    { value: '1y', label: '1 Year' },
    { value: '2y', label: '2 Years' },
]

export function ChartContainer({
    title,
    children,
    className,
    period,
    onPeriodChange,
    periods = DEFAULT_PERIODS,
    onRefresh,
    onExport,
    isLoading = false,
}: ChartContainerProps) {
    return (
        <Card className={cn('', className)}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-base font-medium">{title}</CardTitle>
                <div className="flex items-center gap-2">
                    {onPeriodChange && (
                        <Select value={period} onValueChange={onPeriodChange}>
                            <SelectTrigger className="h-8 w-[120px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {periods.map(p => (
                                    <SelectItem key={p.value} value={p.value}>
                                        {p.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                    {onRefresh && (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8"
                            onClick={onRefresh}
                            disabled={isLoading}
                        >
                            <RefreshCw className={cn('h-4 w-4', isLoading && 'animate-spin')} />
                        </Button>
                    )}
                    {onExport && (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8"
                            onClick={onExport}
                        >
                            <Download className="h-4 w-4" />
                        </Button>
                    )}
                </div>
            </CardHeader>
            <CardContent>
                {children}
            </CardContent>
        </Card>
    )
}

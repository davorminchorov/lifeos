import {
    BarChart as RechartsBarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    Legend,
} from 'recharts'
import { cn } from '@/lib/utils'

interface BarChartProps {
    data: Record<string, unknown>[]
    dataKeys: { key: string; color: string; name?: string }[]
    xAxisKey: string
    height?: number
    className?: string
    showGrid?: boolean
    showLegend?: boolean
    stacked?: boolean
}

export function BarChart({
    data,
    dataKeys,
    xAxisKey,
    height = 300,
    className,
    showGrid = true,
    showLegend = false,
    stacked = false,
}: BarChartProps) {
    return (
        <div className={cn('w-full', className)}>
            <ResponsiveContainer width="100%" height={height}>
                <RechartsBarChart data={data} margin={{ top: 5, right: 10, left: 10, bottom: 5 }}>
                    {showGrid && <CartesianGrid strokeDasharray="3 3" className="stroke-border" />}
                    <XAxis
                        dataKey={xAxisKey}
                        className="text-xs"
                        tick={{ fill: 'var(--muted-foreground)' }}
                    />
                    <YAxis
                        className="text-xs"
                        tick={{ fill: 'var(--muted-foreground)' }}
                    />
                    <Tooltip
                        contentStyle={{
                            backgroundColor: 'var(--popover)',
                            border: '1px solid var(--border)',
                            borderRadius: 'var(--radius)',
                            color: 'var(--popover-foreground)',
                        }}
                    />
                    {showLegend && <Legend />}
                    {dataKeys.map(({ key, color, name }) => (
                        <Bar
                            key={key}
                            dataKey={key}
                            name={name ?? key}
                            fill={color}
                            radius={[4, 4, 0, 0]}
                            stackId={stacked ? 'stack' : undefined}
                        />
                    ))}
                </RechartsBarChart>
            </ResponsiveContainer>
        </div>
    )
}

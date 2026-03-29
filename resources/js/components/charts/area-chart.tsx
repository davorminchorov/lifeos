import {
    AreaChart as RechartsAreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    Legend,
} from 'recharts'
import { cn } from '@/lib/utils'

interface AreaChartProps {
    data: Record<string, unknown>[]
    dataKeys: { key: string; color: string; name?: string }[]
    xAxisKey: string
    height?: number
    className?: string
    showGrid?: boolean
    showLegend?: boolean
}

export function AreaChart({
    data,
    dataKeys,
    xAxisKey,
    height = 300,
    className,
    showGrid = true,
    showLegend = false,
}: AreaChartProps) {
    return (
        <div className={cn('w-full', className)}>
            <ResponsiveContainer width="100%" height={height}>
                <RechartsAreaChart data={data} margin={{ top: 5, right: 10, left: 10, bottom: 5 }}>
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
                        <Area
                            key={key}
                            type="monotone"
                            dataKey={key}
                            name={name ?? key}
                            stroke={color}
                            fill={color}
                            fillOpacity={0.1}
                        />
                    ))}
                </RechartsAreaChart>
            </ResponsiveContainer>
        </div>
    )
}

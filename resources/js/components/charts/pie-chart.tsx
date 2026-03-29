import {
    PieChart as RechartsPieChart,
    Pie,
    Cell,
    Tooltip,
    ResponsiveContainer,
    Legend,
} from 'recharts'
import { cn } from '@/lib/utils'

interface PieChartDataItem {
    name: string
    value: number
    color?: string
}

interface PieChartProps {
    data: PieChartDataItem[]
    height?: number
    className?: string
    showLegend?: boolean
    innerRadius?: number
    outerRadius?: number
    colors?: string[]
}

const DEFAULT_COLORS = [
    'var(--chart-1)',
    'var(--chart-2)',
    'var(--chart-3)',
    'var(--chart-4)',
    'var(--chart-5)',
]

export function PieChart({
    data,
    height = 300,
    className,
    showLegend = true,
    innerRadius = 60,
    outerRadius = 100,
    colors = DEFAULT_COLORS,
}: PieChartProps) {
    return (
        <div className={cn('w-full', className)}>
            <ResponsiveContainer width="100%" height={height}>
                <RechartsPieChart>
                    <Pie
                        data={data}
                        cx="50%"
                        cy="50%"
                        innerRadius={innerRadius}
                        outerRadius={outerRadius}
                        dataKey="value"
                        nameKey="name"
                        paddingAngle={2}
                    >
                        {data.map((entry, index) => (
                            <Cell
                                key={entry.name}
                                fill={entry.color ?? colors[index % colors.length]}
                            />
                        ))}
                    </Pie>
                    <Tooltip
                        contentStyle={{
                            backgroundColor: 'var(--popover)',
                            border: '1px solid var(--border)',
                            borderRadius: 'var(--radius)',
                            color: 'var(--popover-foreground)',
                        }}
                    />
                    {showLegend && <Legend />}
                </RechartsPieChart>
            </ResponsiveContainer>
        </div>
    )
}

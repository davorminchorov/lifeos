import React, { useEffect, useRef } from 'react';
import { Chart, ChartConfiguration, ChartTypeRegistry } from 'chart.js/auto';
import { formatCurrency, formatCompactCurrency } from '../../utils/format';

interface ValuationChartProps {
  data: { date: string; value: number }[];
  initialValue: number;
}

const ValuationChart: React.FC<ValuationChartProps> = ({ data, initialValue }) => {
  const chartRef = useRef<HTMLCanvasElement>(null);
  const chartInstance = useRef<Chart | null>(null);

  useEffect(() => {
    if (chartRef.current && data.length > 0) {
      // Destroy previous chart if it exists
      if (chartInstance.current) {
        chartInstance.current.destroy();
      }

      // Sort data by date
      const sortedData = [...data].sort((a, b) =>
        new Date(a.date).getTime() - new Date(b.date).getTime()
      );

      // Add initial value to the beginning if it's not already there
      if (sortedData.length > 0) {
        const firstDate = new Date(sortedData[0].date);
        const initialDate = new Date(firstDate);
        initialDate.setMonth(initialDate.getMonth() - 1);

        // Only add if it's significantly earlier
        if (initialDate.getTime() < firstDate.getTime()) {
          sortedData.unshift({
            date: initialDate.toISOString().split('T')[0],
            value: initialValue
          });
        }
      }

      // Prepare data for chart
      const labels = sortedData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
      });

      const values = sortedData.map(item => item.value);

      // Calculate profit/loss for coloring
      const startValue = values[0] || 0;
      const endValue = values[values.length - 1] || 0;
      const isProfit = endValue >= startValue;

      // Create gradient for area fill
      const ctx = chartRef.current.getContext('2d');
      if (ctx) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        if (isProfit) {
          gradient.addColorStop(0, 'rgba(34, 197, 94, 0.2)');
          gradient.addColorStop(1, 'rgba(34, 197, 94, 0.0)');
        } else {
          gradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
          gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');
        }

        // Define chart configuration
        const config: ChartConfiguration<keyof ChartTypeRegistry, number[], string> = {
          type: 'line',
          data: {
            labels,
            datasets: [
              {
                label: 'Value',
                data: values,
                fill: true,
                backgroundColor: gradient,
                borderColor: isProfit ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)',
                borderWidth: 2,
                tension: 0.2,
                pointRadius: 3,
                pointBackgroundColor: isProfit ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)',
                pointBorderColor: '#fff',
                pointBorderWidth: 1,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: isProfit ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: {
                grid: {
                  display: false,
                },
                ticks: {
                  font: {
                    size: 11,
                  },
                },
              },
              y: {
                beginAtZero: false,
                grid: {
                  color: 'rgba(0, 0, 0, 0.05)',
                },
                ticks: {
                  callback: function(value) {
                    return formatCompactCurrency(Number(value), 'USD');
                  },
                  font: {
                    size: 11,
                  },
                },
              },
            },
            plugins: {
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const value = context.parsed.y;
                    return formatCurrency(value, 'USD');
                  },
                },
              },
              legend: {
                display: false,
              },
            },
          },
        };

        // Create chart
        chartInstance.current = new Chart(chartRef.current, config);
      }
    }

    // Cleanup function
    return () => {
      if (chartInstance.current) {
        chartInstance.current.destroy();
      }
    };
  }, [data, initialValue]);

  return (
    <div className="h-80 w-full">
      <canvas ref={chartRef}></canvas>
    </div>
  );
};

export default ValuationChart;

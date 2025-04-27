import React, { useState, useEffect } from 'react';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import { Bar } from 'react-chartjs-2';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

interface MonthlyData {
  month: string;
  month_label: string;
  total_amount: number;
  expense_count: number;
}

interface MonthlySpendingChartProps {
  months?: number;
}

export const MonthlySpendingChart: React.FC<MonthlySpendingChartProps> = ({ months = 6 }) => {
  const [monthlyData, setMonthlyData] = useState<MonthlyData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError('');

      try {
        const response = await axios.get(`/api/expense-reports/monthly-summary?months=${months}`);
        setMonthlyData(response.data.data);
      } catch (err) {
        setError('Failed to load monthly spending data');
        console.error('Failed to fetch monthly spending data', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [months]);

  if (loading) {
    return <div className="flex justify-center py-4">Loading chart data...</div>;
  }

  if (error) {
    return <div className="text-red-600">{error}</div>;
  }

  if (!monthlyData || monthlyData.length === 0) {
    return <div className="text-gray-500">No data available for the selected period.</div>;
  }

  const chartData = {
    labels: monthlyData.map(item => item.month_label),
    datasets: [
      {
        label: 'Monthly Spending',
        data: monthlyData.map(item => item.total_amount),
        backgroundColor: 'rgba(59, 130, 246, 0.6)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 1,
      },
    ],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top' as const,
      },
      title: {
        display: false,
      },
      tooltip: {
        callbacks: {
          label: function(context: any) {
            let label = context.dataset.label || '';
            if (label) {
              label += ': ';
            }
            if (context.parsed.y !== null) {
              label += new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
              }).format(context.parsed.y);
            }
            return label;
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value: any) {
            return new Intl.NumberFormat('en-US', {
              style: 'currency',
              currency: 'USD',
              maximumSignificantDigits: 3
            }).format(value);
          }
        }
      }
    }
  };

  return (
    <div className="h-64">
      <Bar data={chartData} options={options} />
    </div>
  );
};

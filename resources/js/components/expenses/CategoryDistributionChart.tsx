import React, { useState, useEffect } from 'react';
import axios from 'axios';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js';
import { Pie } from 'react-chartjs-2';

// Register Chart.js components
ChartJS.register(
  ArcElement,
  Tooltip,
  Legend
);

interface CategoryData {
  category_id: string;
  name: string;
  color: string;
  total_amount: number;
  percentage: number;
}

interface CategoryDistributionChartProps {
  period?: string;
}

export const CategoryDistributionChart: React.FC<CategoryDistributionChartProps> = ({ period = 'all' }) => {
  const [categoryData, setCategoryData] = useState<CategoryData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError('');

      try {
        const response = await axios.get(`/api/expense-reports/category-distribution?period=${period}`);
        setCategoryData(response.data.data);
      } catch (err) {
        setError('Failed to load category distribution data');
        console.error('Failed to fetch category distribution', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [period]);

  if (loading) {
    return <div className="flex justify-center py-4">Loading chart data...</div>;
  }

  if (error) {
    return <div className="text-red-600">{error}</div>;
  }

  if (!categoryData || categoryData.length === 0) {
    return <div className="text-gray-500">No category data available.</div>;
  }

  const chartData = {
    labels: categoryData.map(item => item.name),
    datasets: [
      {
        data: categoryData.map(item => item.total_amount),
        backgroundColor: categoryData.map(item => item.color),
        borderColor: categoryData.map(item => item.color.replace('0.6', '1')),
        borderWidth: 1,
      },
    ],
  };

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right' as const,
        labels: {
          boxWidth: 15,
          padding: 10,
        },
      },
      tooltip: {
        callbacks: {
          label: function(context: any) {
            const index = context.dataIndex;
            const value = categoryData[index].total_amount;
            const percentage = categoryData[index].percentage;
            return ` ${context.label}: ${new Intl.NumberFormat('en-US', {
              style: 'currency',
              currency: 'USD'
            }).format(value)} (${percentage}%)`;
          }
        }
      }
    },
  };

  return (
    <div className="h-64">
      <Pie data={chartData} options={options} />
    </div>
  );
};

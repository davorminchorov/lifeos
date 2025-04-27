import React, { useState, useEffect } from 'react';
import axios from 'axios';

interface CategoryData {
  category: string;
  amount: number;
  percentage: number;
  color: string;
}

export const CategoryDistributionChart: React.FC = () => {
  const [data, setData] = useState<CategoryData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError('');

      try {
        const response = await axios.get('/api/statistics/category-distribution');

        if (response.data && response.data.data) {
          setData(response.data.data);
        } else {
          setData([]);
        }
      } catch (err) {
        console.error('Failed to fetch category distribution data', err);
        setError('Failed to load chart data');

        // Mock empty data for development
        setData([]);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) {
    return (
      <div className="animate-pulse">
        <div className="h-32 bg-gray-200 rounded-full mx-auto w-32"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-red-600 p-4 bg-red-50 rounded-lg border border-red-200">
        {error}
      </div>
    );
  }

  if (!data || data.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-lg border border-gray-200">
        <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
        <p className="text-gray-500">No category data available</p>
      </div>
    );
  }

  // If we have data, show mini donut chart
  return (
    <div className="flex flex-col items-center">
      {/* Simple donut chart */}
      <div className="relative w-32 h-32">
        <svg viewBox="0 0 100 100" className="w-full h-full">
          {data.length > 0 ? (
            data.map((item, index) => {
              const startAngle = index > 0
                ? data.slice(0, index).reduce((sum, d) => sum + d.percentage, 0) * 3.6
                : 0;
              const endAngle = startAngle + item.percentage * 3.6;

              // Convert angles to radians and calculate path
              const startRad = (startAngle - 90) * Math.PI / 180;
              const endRad = (endAngle - 90) * Math.PI / 180;

              const x1 = 50 + 40 * Math.cos(startRad);
              const y1 = 50 + 40 * Math.sin(startRad);
              const x2 = 50 + 40 * Math.cos(endRad);
              const y2 = 50 + 40 * Math.sin(endRad);

              const largeArcFlag = endAngle - startAngle > 180 ? 1 : 0;

              // Create donut path
              const pathData = [
                `M 50 50`,
                `L ${x1} ${y1}`,
                `A 40 40 0 ${largeArcFlag} 1 ${x2} ${y2}`,
                `Z`
              ].join(' ');

              return (
                <path
                  key={index}
                  d={pathData}
                  fill={item.color}
                  stroke="#fff"
                  strokeWidth="1"
                />
              );
            })
          ) : (
            <circle cx="50" cy="50" r="40" fill="#E5E7EB" />
          )}
          {/* Inner white circle to create donut effect */}
          <circle cx="50" cy="50" r="25" fill="white" />
        </svg>
      </div>

      {/* Legend */}
      <div className="mt-4 grid grid-cols-2 gap-2 w-full">
        {data.map((item, index) => (
          <div key={index} className="flex items-center text-sm">
            <div
              className="w-3 h-3 rounded-full mr-2"
              style={{ backgroundColor: item.color }}
            ></div>
            <span className="truncate">{item.category}</span>
            <span className="ml-1 text-gray-500">{item.percentage}%</span>
          </div>
        ))}
      </div>
    </div>
  );
};

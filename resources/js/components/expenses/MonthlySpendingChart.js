import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
export const MonthlySpendingChart = ({ months = 6 }) => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    useEffect(() => {
        const fetchData = async () => {
            setLoading(true);
            setError('');
            try {
                // In a real app, you would pass months as a parameter
                const response = await axios.get(`/api/statistics/monthly-spending?months=${months}`);
                if (response.data && response.data.data) {
                    setData(response.data.data);
                }
                else {
                    setData([]);
                }
            }
            catch (err) {
                console.error('Failed to fetch monthly spending data', err);
                setError('Failed to load chart data');
                // Mock data for development
                const today = new Date();
                const mockData = [];
                for (let i = months - 1; i >= 0; i--) {
                    const date = new Date(today.getFullYear(), today.getMonth() - i, 1);
                    const month = date.toLocaleString('default', { month: 'short' });
                    const year = date.getFullYear();
                    mockData.push({
                        label: `${month} ${year}`,
                        value: 0, // Since we want to show empty state
                    });
                }
                setData(mockData);
            }
            finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [months]);
    if (loading) {
        return (_jsx("div", { className: "animate-pulse", children: _jsx("div", { className: "h-32 bg-gray-200 rounded" }) }));
    }
    if (error) {
        return (_jsx("div", { className: "text-red-600 p-4 bg-red-50 rounded-lg border border-red-200", children: error }));
    }
    // Check if we have actual data (non-zero values)
    const hasData = data.some(item => item.value > 0);
    if (!hasData) {
        return (_jsxs("div", { className: "flex flex-col items-center justify-center p-6 bg-gray-50 rounded-lg border border-gray-200", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-12 w-12 text-gray-300 mb-3", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" }) }), _jsx("p", { className: "text-gray-500", children: "No data available for the selected period." })] }));
    }
    // Find max value for scaling
    const maxValue = Math.max(...data.map(d => d.value));
    return (_jsx("div", { className: "w-full", children: _jsx("div", { className: "flex items-end h-32 space-x-2", children: data.map((item, index) => {
                const height = item.value ? (item.value / maxValue) * 100 : 0;
                return (_jsxs("div", { className: "flex flex-col items-center flex-1", children: [_jsx("div", { className: "w-full bg-blue-500 rounded-t", style: { height: `${height}%` } }), _jsx("div", { className: "text-xs text-gray-600 mt-1 truncate w-full text-center", children: item.label })] }, index));
            }) }) }));
};

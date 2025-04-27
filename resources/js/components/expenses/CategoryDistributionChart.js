import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import axios from 'axios';
export const CategoryDistributionChart = () => {
    const [data, setData] = useState([]);
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
                }
                else {
                    setData([]);
                }
            }
            catch (err) {
                console.error('Failed to fetch category distribution data', err);
                setError('Failed to load chart data');
                // Mock empty data for development
                setData([]);
            }
            finally {
                setLoading(false);
            }
        };
        fetchData();
    }, []);
    if (loading) {
        return (_jsx("div", { className: "animate-pulse", children: _jsx("div", { className: "h-32 bg-gray-200 rounded-full mx-auto w-32" }) }));
    }
    if (error) {
        return (_jsx("div", { className: "text-red-600 p-4 bg-red-50 rounded-lg border border-red-200", children: error }));
    }
    if (!data || data.length === 0) {
        return (_jsxs("div", { className: "flex flex-col items-center justify-center p-6 bg-gray-50 rounded-lg border border-gray-200", children: [_jsxs("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-12 w-12 text-gray-300 mb-3", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: [_jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" }), _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 1.5, d: "M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" })] }), _jsx("p", { className: "text-gray-500", children: "No category data available" })] }));
    }
    // If we have data, show mini donut chart
    return (_jsxs("div", { className: "flex flex-col items-center", children: [_jsx("div", { className: "relative w-32 h-32", children: _jsxs("svg", { viewBox: "0 0 100 100", className: "w-full h-full", children: [data.length > 0 ? (data.map((item, index) => {
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
                            return (_jsx("path", { d: pathData, fill: item.color, stroke: "#fff", strokeWidth: "1" }, index));
                        })) : (_jsx("circle", { cx: "50", cy: "50", r: "40", fill: "#E5E7EB" })), _jsx("circle", { cx: "50", cy: "50", r: "25", fill: "white" })] }) }), _jsx("div", { className: "mt-4 grid grid-cols-2 gap-2 w-full", children: data.map((item, index) => (_jsxs("div", { className: "flex items-center text-sm", children: [_jsx("div", { className: "w-3 h-3 rounded-full mr-2", style: { backgroundColor: item.color } }), _jsx("span", { className: "truncate", children: item.category }), _jsxs("span", { className: "ml-1 text-gray-500", children: [item.percentage, "%"] })] }, index))) })] }));
};

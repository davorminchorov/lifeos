import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
const InvestmentList = () => {
    const [investments, setInvestments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        const fetchInvestments = async () => {
            try {
                setLoading(true);
                const response = await axios.get('/api/investments');
                setInvestments(response.data.data);
                setError(null);
            }
            catch (err) {
                setError('Failed to fetch investments. Please try again later.');
                console.error('Error fetching investments:', err);
            }
            finally {
                setLoading(false);
            }
        };
        fetchInvestments();
    }, []);
    const handleDelete = async (id) => {
        if (!confirm('Are you sure you want to delete this investment?')) {
            return;
        }
        try {
            await axios.delete(`/api/investments/${id}`);
            setInvestments(investments.filter(investment => investment.id !== id));
        }
        catch (err) {
            setError('Failed to delete investment. Please try again later.');
            console.error('Error deleting investment:', err);
        }
    };
    if (loading) {
        return _jsx("div", { className: "text-center p-4", children: "Loading investments..." });
    }
    if (error) {
        return _jsx("div", { className: "alert alert-danger", children: error });
    }
    return (_jsxs("div", { className: "container", children: [_jsxs("div", { className: "d-flex justify-content-between align-items-center mb-4", children: [_jsx("h1", { children: "Investments" }), _jsx(Link, { to: "/investments/create", className: "btn btn-primary", children: "Add Investment" })] }), investments.length === 0 ? (_jsx("div", { className: "alert alert-info", children: "No investments found. Click the \"Add Investment\" button to create one." })) : (_jsx("div", { className: "table-responsive", children: _jsxs("table", { className: "table table-striped", children: [_jsx("thead", { children: _jsxs("tr", { children: [_jsx("th", { children: "Name" }), _jsx("th", { children: "Type" }), _jsx("th", { children: "Initial Value" }), _jsx("th", { children: "Current Value" }), _jsx("th", { children: "ROI" }), _jsx("th", { children: "Actions" })] }) }), _jsx("tbody", { children: investments.map((investment) => {
                                const roi = ((investment.current_value - investment.initial_value) / investment.initial_value) * 100;
                                return (_jsxs("tr", { children: [_jsx("td", { children: _jsx(Link, { to: `/investments/${investment.id}`, children: investment.name }) }), _jsx("td", { children: investment.type }), _jsxs("td", { children: ["$", investment.initial_value.toFixed(2)] }), _jsxs("td", { children: ["$", investment.current_value.toFixed(2)] }), _jsxs("td", { className: roi >= 0 ? 'text-success' : 'text-danger', children: [roi.toFixed(2), "%"] }), _jsx("td", { children: _jsxs("div", { className: "btn-group", children: [_jsx(Link, { to: `/investments/${investment.id}/edit`, className: "btn btn-sm btn-outline-primary", children: "Edit" }), _jsx("button", { onClick: () => handleDelete(investment.id), className: "btn btn-sm btn-outline-danger ms-1", children: "Delete" })] }) })] }, investment.id));
                            }) })] }) }))] }));
};
export default InvestmentList;

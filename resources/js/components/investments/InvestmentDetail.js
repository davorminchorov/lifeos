import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
const InvestmentDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [investment, setInvestment] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        const fetchInvestment = async () => {
            try {
                setLoading(true);
                const response = await axios.get(`/api/investments/${id}`);
                setInvestment(response.data.data);
                setError(null);
            }
            catch (err) {
                setError('Failed to fetch investment details. Please try again later.');
                console.error('Error fetching investment:', err);
            }
            finally {
                setLoading(false);
            }
        };
        fetchInvestment();
    }, [id]);
    const handleDelete = async () => {
        if (!confirm('Are you sure you want to delete this investment?')) {
            return;
        }
        try {
            await axios.delete(`/api/investments/${id}`);
            navigate('/investments', { state: { message: 'Investment deleted successfully' } });
        }
        catch (err) {
            setError('Failed to delete investment. Please try again later.');
            console.error('Error deleting investment:', err);
        }
    };
    if (loading) {
        return _jsx("div", { className: "text-center p-4", children: "Loading investment details..." });
    }
    if (error) {
        return _jsx("div", { className: "alert alert-danger", children: error });
    }
    if (!investment) {
        return _jsx("div", { className: "alert alert-warning", children: "Investment not found" });
    }
    const roi = ((investment.current_value - investment.initial_value) / investment.initial_value) * 100;
    return (_jsx("div", { className: "container", children: _jsxs("div", { className: "card mb-4", children: [_jsxs("div", { className: "card-header d-flex justify-content-between align-items-center", children: [_jsx("h1", { className: "card-title mb-0", children: investment.name }), _jsxs("div", { children: [_jsx(Link, { to: "/investments", className: "btn btn-outline-secondary me-2", children: "Back to List" }), _jsx(Link, { to: `/investments/${id}/edit`, className: "btn btn-outline-primary me-2", children: "Edit" }), _jsx("button", { onClick: handleDelete, className: "btn btn-outline-danger", children: "Delete" })] })] }), _jsx("div", { className: "card-body", children: _jsxs("div", { className: "row mb-4", children: [_jsxs("div", { className: "col-md-6", children: [_jsx("h5", { children: "Investment Details" }), _jsx("table", { className: "table", children: _jsxs("tbody", { children: [_jsxs("tr", { children: [_jsx("th", { children: "Type" }), _jsx("td", { children: investment.type })] }), _jsxs("tr", { children: [_jsx("th", { children: "Description" }), _jsx("td", { children: investment.description || 'No description provided' })] }), _jsxs("tr", { children: [_jsx("th", { children: "Created" }), _jsx("td", { children: new Date(investment.created_at).toLocaleDateString() })] }), _jsxs("tr", { children: [_jsx("th", { children: "Last Updated" }), _jsx("td", { children: new Date(investment.updated_at).toLocaleDateString() })] })] }) })] }), _jsxs("div", { className: "col-md-6", children: [_jsx("h5", { children: "Performance" }), _jsx("div", { className: "card bg-light", children: _jsxs("div", { className: "card-body", children: [_jsxs("div", { className: "row", children: [_jsx("div", { className: "col-6", children: _jsxs("div", { className: "mb-3", children: [_jsx("div", { className: "text-muted small", children: "Initial Value" }), _jsxs("div", { className: "fs-4", children: ["$", investment.initial_value.toFixed(2)] })] }) }), _jsx("div", { className: "col-6", children: _jsxs("div", { className: "mb-3", children: [_jsx("div", { className: "text-muted small", children: "Current Value" }), _jsxs("div", { className: "fs-4", children: ["$", investment.current_value.toFixed(2)] })] }) })] }), _jsxs("div", { className: "mb-2", children: [_jsx("div", { className: "text-muted small", children: "Return on Investment" }), _jsxs("div", { className: `fs-4 ${roi >= 0 ? 'text-success' : 'text-danger'}`, children: [roi.toFixed(2), "%"] })] }), _jsx("div", { className: "progress", children: _jsx("div", { className: `progress-bar ${roi >= 0 ? 'bg-success' : 'bg-danger'}`, role: "progressbar", style: { width: `${Math.min(Math.abs(roi), 100)}%` }, "aria-valuenow": Math.abs(roi), "aria-valuemin": 0, "aria-valuemax": 100 }) })] }) })] })] }) })] }) }));
};
export default InvestmentDetail;

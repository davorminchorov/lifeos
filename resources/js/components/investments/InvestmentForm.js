import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { useToast } from '../../ui/Toast';
const InvestmentForm = ({ isEditing }) => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { toast } = useToast();
    const [formData, setFormData] = useState({
        name: '',
        type: 'stock', // Default type
        description: '',
        initial_value: '',
        current_value: '',
        purchase_date: '',
        notes: ''
    });
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);
    const [validationErrors, setValidationErrors] = useState({});
    // Investment types available for selection
    const investmentTypes = [
        { value: 'stock', label: 'Stock' },
        { value: 'bond', label: 'Bond' },
        { value: 'etf', label: 'ETF' },
        { value: 'mutual_fund', label: 'Mutual Fund' },
        { value: 'crypto', label: 'Cryptocurrency' },
        { value: 'real_estate', label: 'Real Estate' },
        { value: 'other', label: 'Other' }
    ];
    useEffect(() => {
        if (isEditing && id) {
            fetchInvestment();
        }
    }, [isEditing, id]);
    const fetchInvestment = async () => {
        var _a, _b;
        try {
            setLoading(true);
            const response = await axios.get(`/api/investments/${id}`);
            const investment = response.data.data;
            setFormData({
                name: investment.name,
                type: investment.type,
                description: investment.description || '',
                initial_value: ((_a = investment.initial_value) === null || _a === void 0 ? void 0 : _a.toString()) || '',
                current_value: ((_b = investment.current_value) === null || _b === void 0 ? void 0 : _b.toString()) || '',
                purchase_date: investment.purchase_date || '',
                notes: investment.notes || ''
            });
            setError(null);
        }
        catch (err) {
            console.error('Error fetching investment details:', err);
            toast({
                title: "Error",
                description: "Failed to load investment details. Please try again.",
                variant: "destructive",
            });
            setError('Failed to load investment details. Please try again.');
        }
        finally {
            setLoading(false);
        }
    };
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => (Object.assign(Object.assign({}, prev), { [name]: value })));
        // Clear validation error when field is edited
        if (validationErrors[name]) {
            setValidationErrors(prev => {
                const newErrors = Object.assign({}, prev);
                delete newErrors[name];
                return newErrors;
            });
        }
    };
    const handleSubmit = async (e) => {
        var _a, _b, _c;
        e.preventDefault();
        try {
            setSaving(true);
            setValidationErrors({});
            // Convert string values to numbers for API
            const apiData = Object.assign(Object.assign({}, formData), { initial_value: formData.initial_value ? parseFloat(formData.initial_value) : null, current_value: formData.current_value ? parseFloat(formData.current_value) : null });
            if (isEditing) {
                await axios.put(`/api/investments/${id}`, apiData);
                toast({
                    title: "Success",
                    description: "Investment updated successfully",
                    variant: "success",
                });
            }
            else {
                await axios.post('/api/investments', apiData);
                toast({
                    title: "Success",
                    description: "Investment created successfully",
                    variant: "success",
                });
            }
            // Navigate back to investments list
            navigate('/investments');
        }
        catch (err) {
            console.error('Error saving investment:', err);
            // Handle validation errors from the API
            if (((_a = err.response) === null || _a === void 0 ? void 0 : _a.status) === 422 && ((_c = (_b = err.response) === null || _b === void 0 ? void 0 : _b.data) === null || _c === void 0 ? void 0 : _c.errors)) {
                const apiErrors = err.response.data.errors;
                const formattedErrors = {};
                // Format errors to match our state structure
                Object.keys(apiErrors).forEach(field => {
                    formattedErrors[field] = apiErrors[field][0];
                });
                setValidationErrors(formattedErrors);
                toast({
                    title: "Validation Error",
                    description: "Please correct the errors in the form",
                    variant: "destructive",
                });
            }
            else {
                setError('Failed to save investment. Please try again later.');
                toast({
                    title: "Error",
                    description: "Failed to save investment. Please try again later.",
                    variant: "destructive",
                });
            }
        }
        finally {
            setSaving(false);
        }
    };
    if (loading) {
        return _jsx("div", { className: "text-center p-4", children: "Loading investment data..." });
    }
    return (_jsx("div", { className: "container", children: _jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h1", { className: "card-title", children: isEditing ? 'Edit Investment' : 'Create New Investment' }) }), _jsxs("div", { className: "card-body", children: [error && (_jsx("div", { className: "alert alert-danger mb-4", children: error })), _jsxs("form", { onSubmit: handleSubmit, children: [_jsxs("div", { className: "mb-3", children: [_jsx("label", { htmlFor: "name", className: "form-label", children: "Name" }), _jsx("input", { type: "text", className: `form-control ${validationErrors.name ? 'is-invalid' : ''}`, id: "name", name: "name", value: formData.name, onChange: handleChange, required: true }), validationErrors.name && (_jsx("div", { className: "invalid-feedback", children: validationErrors.name }))] }), _jsxs("div", { className: "mb-3", children: [_jsx("label", { htmlFor: "type", className: "form-label", children: "Type" }), _jsx("select", { className: `form-select ${validationErrors.type ? 'is-invalid' : ''}`, id: "type", name: "type", value: formData.type, onChange: handleChange, required: true, children: investmentTypes.map(type => (_jsx("option", { value: type.value, children: type.label }, type.value))) }), validationErrors.type && (_jsx("div", { className: "invalid-feedback", children: validationErrors.type }))] }), _jsxs("div", { className: "mb-3", children: [_jsx("label", { htmlFor: "description", className: "form-label", children: "Description" }), _jsx("textarea", { className: `form-control ${validationErrors.description ? 'is-invalid' : ''}`, id: "description", name: "description", value: formData.description, onChange: handleChange, rows: 3 }), validationErrors.description && (_jsx("div", { className: "invalid-feedback", children: validationErrors.description }))] }), _jsxs("div", { className: "row", children: [_jsxs("div", { className: "col-md-6 mb-3", children: [_jsx("label", { htmlFor: "initial_value", className: "form-label", children: "Initial Value ($)" }), _jsx("input", { type: "number", step: "0.01", min: "0", className: `form-control ${validationErrors.initial_value ? 'is-invalid' : ''}`, id: "initial_value", name: "initial_value", value: formData.initial_value, onChange: handleChange, required: true }), validationErrors.initial_value && (_jsx("div", { className: "invalid-feedback", children: validationErrors.initial_value }))] }), _jsxs("div", { className: "col-md-6 mb-3", children: [_jsx("label", { htmlFor: "current_value", className: "form-label", children: "Current Value ($)" }), _jsx("input", { type: "number", step: "0.01", min: "0", className: `form-control ${validationErrors.current_value ? 'is-invalid' : ''}`, id: "current_value", name: "current_value", value: formData.current_value, onChange: handleChange, required: true }), validationErrors.current_value && (_jsx("div", { className: "invalid-feedback", children: validationErrors.current_value }))] })] }), _jsxs("div", { className: "d-flex justify-content-between mt-4", children: [_jsx(Link, { to: "/investments", className: "btn btn-outline-secondary", children: "Cancel" }), _jsx("button", { type: "submit", className: "btn btn-primary", disabled: saving, children: saving ? (_jsxs(_Fragment, { children: [_jsx("span", { className: "spinner-border spinner-border-sm me-2", role: "status", "aria-hidden": "true" }), "Saving..."] })) : (isEditing ? 'Update Investment' : 'Create Investment') })] })] })] })] }) }));
};
export default InvestmentForm;

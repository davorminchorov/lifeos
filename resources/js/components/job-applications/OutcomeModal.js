import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Modal, Button, Input, Select, Textarea } from '../../ui';
import { axiosClient } from '../../lib/axios';
const OutcomeModal = ({ applicationId, onClose, onSave, }) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        outcome: 'offered',
        outcome_date: new Date().toISOString().split('T')[0],
        salary_offered: '',
        feedback: '',
        notes: ''
    });
    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: value })));
    };
    const handleSubmit = async (e) => {
        var _a, _b;
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
            await axiosClient.post(`/api/job-applications/${applicationId}/outcome`, formData);
            onSave();
        }
        catch (err) {
            console.error('Error recording outcome:', err);
            setError(((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) || 'Failed to record outcome');
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsx(Modal, { title: "Record Application Outcome", onClose: onClose, children: _jsxs("form", { onSubmit: handleSubmit, children: [_jsxs("div", { className: "space-y-4 mb-6", children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs(Select, { label: "Outcome", name: "outcome", value: formData.outcome, onChange: handleInputChange, required: true, children: [_jsx("option", { value: "offered", children: "Offered" }), _jsx("option", { value: "rejected", children: "Rejected" }), _jsx("option", { value: "withdrawn", children: "Withdrawn" })] }), _jsx(Input, { label: "Outcome Date", name: "outcome_date", type: "date", value: formData.outcome_date, onChange: handleInputChange, required: true })] }), formData.outcome === 'offered' && (_jsx(Input, { label: "Salary Offered (optional)", name: "salary_offered", value: formData.salary_offered || '', onChange: handleInputChange, placeholder: "e.g. $75,000 per year" })), _jsx(Textarea, { label: "Feedback (optional)", name: "feedback", value: formData.feedback || '', onChange: handleInputChange, placeholder: "Any feedback received about your application", rows: 3 }), _jsx(Textarea, { label: "Notes (optional)", name: "notes", value: formData.notes || '', onChange: handleInputChange, placeholder: "Additional notes about the outcome", rows: 3 })] }), error && _jsx("p", { className: "text-red-500 mb-4", children: error }), _jsxs("div", { className: "flex justify-end gap-2", children: [_jsx(Button, { variant: "outline", onClick: onClose, type: "button", children: "Cancel" }), _jsx(Button, { type: "submit", disabled: loading, variant: formData.outcome === 'offered' ? 'default' : 'outline', children: loading ? 'Saving...' : 'Record Outcome' })] })] }) }));
};
export default OutcomeModal;

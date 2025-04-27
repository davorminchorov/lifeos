import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Modal, Button, Input, Select, Textarea } from '../../ui';
import { axiosClient } from '../../lib/axios';
const InterviewModal = ({ applicationId, onClose, onSave, }) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        interview_date: new Date().toISOString().split('T')[0],
        interview_time: '10:00',
        interview_type: 'video',
        with_person: '',
        location: '',
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
            await axiosClient.post(`/api/job-applications/${applicationId}/interviews`, formData);
            onSave();
        }
        catch (err) {
            console.error('Error scheduling interview:', err);
            setError(((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) || 'Failed to schedule interview');
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsx(Modal, { title: "Schedule Interview", onClose: onClose, children: _jsxs("form", { onSubmit: handleSubmit, children: [_jsxs("div", { className: "space-y-4 mb-6", children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsx(Input, { label: "Interview Date", name: "interview_date", type: "date", value: formData.interview_date, onChange: handleInputChange, required: true }), _jsx(Input, { label: "Interview Time", name: "interview_time", type: "time", value: formData.interview_time, onChange: handleInputChange, required: true })] }), _jsxs(Select, { label: "Interview Type", name: "interview_type", value: formData.interview_type, onChange: handleInputChange, required: true, children: [_jsx("option", { value: "phone", children: "Phone" }), _jsx("option", { value: "video", children: "Video" }), _jsx("option", { value: "in-person", children: "In-person" })] }), _jsx(Input, { label: "With Person", name: "with_person", value: formData.with_person, onChange: handleInputChange, placeholder: "e.g. Hiring Manager, HR, Team Lead", required: true }), _jsx(Input, { label: "Location (optional)", name: "location", value: formData.location || '', onChange: handleInputChange, placeholder: "Address, Zoom link, or phone number" }), _jsx(Textarea, { label: "Notes (optional)", name: "notes", value: formData.notes || '', onChange: handleInputChange, placeholder: "Preparation notes, questions to ask, etc.", rows: 3 })] }), error && _jsx("p", { className: "text-red-500 mb-4", children: error }), _jsxs("div", { className: "flex justify-end gap-2", children: [_jsx(Button, { variant: "outline", onClick: onClose, type: "button", children: "Cancel" }), _jsx(Button, { type: "submit", disabled: loading, children: loading ? 'Scheduling...' : 'Schedule Interview' })] })] }) }));
};
export default InterviewModal;

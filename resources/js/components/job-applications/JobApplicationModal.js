import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { Button } from '../../ui';
import { axiosClient } from '../../lib/axios';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '../../ui/Dialog';
import { Input } from '../../ui/Input';
import { Textarea } from '../../ui/Textarea';
import { Label } from '../../ui/Label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../ui/Select';
import { Building, Briefcase, Calendar, DollarSign, Link, User, Mail, FileText, MessageSquare } from 'lucide-react';
const JobApplicationModal = ({ application, onClose, onSave, }) => {
    const isEditing = !!application;
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        company_name: (application === null || application === void 0 ? void 0 : application.company_name) || '',
        position: (application === null || application === void 0 ? void 0 : application.position) || '',
        application_date: (application === null || application === void 0 ? void 0 : application.application_date) || new Date().toISOString().split('T')[0],
        job_description: (application === null || application === void 0 ? void 0 : application.job_description) || '',
        application_url: (application === null || application === void 0 ? void 0 : application.application_url) || '',
        salary_range: (application === null || application === void 0 ? void 0 : application.salary_range) || '',
        contact_person: (application === null || application === void 0 ? void 0 : application.contact_person) || '',
        contact_email: (application === null || application === void 0 ? void 0 : application.contact_email) || '',
        status: (application === null || application === void 0 ? void 0 : application.status) || 'applied',
        notes: (application === null || application === void 0 ? void 0 : application.notes) || '',
    });
    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { [name]: value })));
    };
    const handleStatusChange = (value) => {
        setFormData((prev) => (Object.assign(Object.assign({}, prev), { status: value })));
    };
    const handleSubmit = async (e) => {
        var _a, _b;
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
            if (isEditing) {
                await axiosClient.put(`/api/job-applications/${application.id}`, formData);
            }
            else {
                await axiosClient.post('/api/job-applications', formData);
            }
            onSave();
        }
        catch (err) {
            console.error('Error saving job application:', err);
            setError(((_b = (_a = err.response) === null || _a === void 0 ? void 0 : _a.data) === null || _b === void 0 ? void 0 : _b.message) || 'Failed to save job application');
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsx(Dialog, { open: true, onOpenChange: (open) => !open && onClose(), children: _jsx(DialogContent, { className: "sm:max-w-[600px]", children: _jsxs("form", { onSubmit: handleSubmit, children: [_jsxs(DialogHeader, { children: [_jsx(DialogTitle, { children: isEditing ? 'Edit Job Application' : 'Add Job Application' }), _jsx(DialogDescription, { children: isEditing ? 'Update the details of your job application.' : 'Enter the details of your job application.' })] }), _jsxs("div", { className: "grid gap-6 py-4", children: [_jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "company_name", className: "flex items-center", children: [_jsx(Building, { className: "h-4 w-4 mr-2" }), "Company Name"] }), _jsx(Input, { id: "company_name", name: "company_name", value: formData.company_name, onChange: handleInputChange, required: true })] }), _jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "position", className: "flex items-center", children: [_jsx(Briefcase, { className: "h-4 w-4 mr-2" }), "Position"] }), _jsx(Input, { id: "position", name: "position", value: formData.position, onChange: handleInputChange, required: true })] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "application_date", className: "flex items-center", children: [_jsx(Calendar, { className: "h-4 w-4 mr-2" }), "Application Date"] }), _jsx(Input, { id: "application_date", name: "application_date", type: "date", value: formData.application_date, onChange: handleInputChange, required: true })] }), _jsxs("div", { className: "space-y-2", children: [_jsx(Label, { htmlFor: "status", className: "flex items-center", children: "Status" }), _jsxs(Select, { value: formData.status, onValueChange: handleStatusChange, children: [_jsx(SelectTrigger, { children: _jsx(SelectValue, { placeholder: "Select status" }) }), _jsxs(SelectContent, { children: [_jsx(SelectItem, { value: "applied", children: "Applied" }), _jsx(SelectItem, { value: "interviewing", children: "Interviewing" }), _jsx(SelectItem, { value: "offered", children: "Offered" }), _jsx(SelectItem, { value: "rejected", children: "Rejected" }), _jsx(SelectItem, { value: "withdrawn", children: "Withdrawn" })] })] })] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "salary_range", className: "flex items-center", children: [_jsx(DollarSign, { className: "h-4 w-4 mr-2" }), "Salary Range (optional)"] }), _jsx(Input, { id: "salary_range", name: "salary_range", value: formData.salary_range || '', onChange: handleInputChange })] }), _jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "application_url", className: "flex items-center", children: [_jsx(Link, { className: "h-4 w-4 mr-2" }), "Application URL (optional)"] }), _jsx(Input, { id: "application_url", name: "application_url", type: "url", value: formData.application_url || '', onChange: handleInputChange })] })] }), _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-4", children: [_jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "contact_person", className: "flex items-center", children: [_jsx(User, { className: "h-4 w-4 mr-2" }), "Contact Person (optional)"] }), _jsx(Input, { id: "contact_person", name: "contact_person", value: formData.contact_person || '', onChange: handleInputChange })] }), _jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "contact_email", className: "flex items-center", children: [_jsx(Mail, { className: "h-4 w-4 mr-2" }), "Contact Email (optional)"] }), _jsx(Input, { id: "contact_email", name: "contact_email", type: "email", value: formData.contact_email || '', onChange: handleInputChange })] })] }), _jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "job_description", className: "flex items-center", children: [_jsx(FileText, { className: "h-4 w-4 mr-2" }), "Job Description (optional)"] }), _jsx(Textarea, { id: "job_description", name: "job_description", value: formData.job_description || '', onChange: handleInputChange, rows: 3 })] }), _jsxs("div", { className: "space-y-2", children: [_jsxs(Label, { htmlFor: "notes", className: "flex items-center", children: [_jsx(MessageSquare, { className: "h-4 w-4 mr-2" }), "Notes (optional)"] }), _jsx(Textarea, { id: "notes", name: "notes", value: formData.notes || '', onChange: handleInputChange, rows: 3 })] }), error && (_jsx("div", { className: "bg-error/10 text-error p-4 rounded-lg", children: error }))] }), _jsxs(DialogFooter, { children: [_jsx(Button, { variant: "outlined", onClick: onClose, type: "button", children: "Cancel" }), _jsx(Button, { variant: "filled", type: "submit", disabled: loading, children: loading ? 'Saving...' : isEditing ? 'Update' : 'Save' })] })] }) }) }));
};
export default JobApplicationModal;

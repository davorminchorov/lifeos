import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { axiosClient } from '../../lib/axios';
import JobApplicationModal from '../../components/job-applications/JobApplicationModal';
import { Card, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';
const EditJobApplication = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [application, setApplication] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showModal, setShowModal] = useState(false);
    useEffect(() => {
        const fetchApplication = async () => {
            try {
                setLoading(true);
                const response = await axiosClient.get(`/api/job-applications/${id}`);
                setApplication(response.data);
                setShowModal(true);
                setError(null);
            }
            catch (err) {
                console.error('Error fetching job application:', err);
                setError('Failed to load job application details');
            }
            finally {
                setLoading(false);
            }
        };
        fetchApplication();
    }, [id]);
    const handleClose = () => {
        navigate(`/job-applications/${id}`);
    };
    const handleSave = () => {
        navigate(`/job-applications/${id}`);
    };
    if (loading) {
        return (_jsx(PageContainer, { title: "Edit Job Application", children: _jsx("div", { className: "flex justify-center items-center h-64", children: _jsx("div", { className: "animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary" }) }) }));
    }
    if (error || !application) {
        return (_jsx(PageContainer, { title: "Error", children: _jsx(Card, { children: _jsx(CardContent, { children: _jsxs("div", { className: "text-center py-8", children: [_jsx("p", { className: "text-error mb-4", children: error || 'Job application not found' }), _jsx(Button, { variant: "outlined", onClick: () => navigate('/job-applications'), children: "Back to Applications" })] }) }) }) }));
    }
    return (_jsx(PageContainer, { title: "Edit Job Application", subtitle: `${application.company_name} - ${application.position}`, actions: _jsx(Button, { variant: "outlined", onClick: handleClose, children: "Cancel" }), children: _jsx(Card, { className: "py-6", children: showModal && (_jsx(JobApplicationModal, { application: application, onClose: handleClose, onSave: handleSave })) }) }));
};
export default EditJobApplication;

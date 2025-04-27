import { jsx as _jsx } from "react/jsx-runtime";
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import JobApplicationModal from '../../components/job-applications/JobApplicationModal';
import { Card } from '../../ui/Card';
import { Button } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';
const CreateJobApplication = () => {
    const navigate = useNavigate();
    const [showModal, setShowModal] = useState(true);
    const handleClose = () => {
        navigate('/job-applications');
    };
    const handleSave = () => {
        // Navigate back to the list page after successful save
        navigate('/job-applications');
    };
    return (_jsx(PageContainer, { title: "Create Job Application", subtitle: "Add a new job application to track", actions: _jsx(Button, { variant: "outlined", onClick: handleClose, children: "Cancel" }), children: _jsx(Card, { className: "py-6", children: showModal && (_jsx(JobApplicationModal, { onClose: handleClose, onSave: handleSave })) }) }));
};
export default CreateJobApplication;

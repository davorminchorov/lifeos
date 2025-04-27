import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import JobApplicationModal from '../../components/job-applications/JobApplicationModal';
import { Card } from '../../ui/Card';
import { Button } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';

const CreateJobApplication: React.FC = () => {
  const navigate = useNavigate();
  const [showModal, setShowModal] = useState(true);

  const handleClose = () => {
    navigate('/job-applications');
  };

  const handleSave = () => {
    // Navigate back to the list page after successful save
    navigate('/job-applications');
  };

  return (
    <PageContainer
      title="Create Job Application"
      subtitle="Add a new job application to track"
      actions={
        <Button variant="outlined" onClick={handleClose}>
          Cancel
        </Button>
      }
    >
      <Card className="py-6">
        {showModal && (
          <JobApplicationModal
            onClose={handleClose}
            onSave={handleSave}
          />
        )}
      </Card>
    </PageContainer>
  );
};

export default CreateJobApplication;

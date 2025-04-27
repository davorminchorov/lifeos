import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { axiosClient } from '../../lib/axios';
import JobApplicationModal from '../../components/job-applications/JobApplicationModal';
import { Card, CardContent } from '../../ui/Card';
import { Button } from '../../ui';
import { PageContainer } from '../../ui/PageContainer';
import { JobApplication } from '../../types/job-applications';

const EditJobApplication: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [application, setApplication] = useState<JobApplication | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    const fetchApplication = async () => {
      try {
        setLoading(true);
        const response = await axiosClient.get(`/api/job-applications/${id}`);
        setApplication(response.data);
        setShowModal(true);
        setError(null);
      } catch (err) {
        console.error('Error fetching job application:', err);
        setError('Failed to load job application details');
      } finally {
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
    return (
      <PageContainer title="Edit Job Application">
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      </PageContainer>
    );
  }

  if (error || !application) {
    return (
      <PageContainer title="Error">
        <Card>
          <CardContent>
            <div className="text-center py-8">
              <p className="text-error mb-4">{error || 'Job application not found'}</p>
              <Button variant="outlined" onClick={() => navigate('/job-applications')}>
                Back to Applications
              </Button>
            </div>
          </CardContent>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer
      title="Edit Job Application"
      subtitle={`${application.company_name} - ${application.position}`}
      actions={
        <Button variant="outlined" onClick={handleClose}>
          Cancel
        </Button>
      }
    >
      <Card className="py-6">
        {showModal && (
          <JobApplicationModal
            application={application}
            onClose={handleClose}
            onSave={handleSave}
          />
        )}
      </Card>
    </PageContainer>
  );
};

export default EditJobApplication;

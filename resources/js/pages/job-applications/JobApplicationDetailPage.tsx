import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { axiosClient } from '../../lib/axios';
import { Button, Card, Heading, Spinner, Tabs, Badge } from '../../ui';
import { Alert } from '../../components/ui/Alert';
import { formatDate } from '../../utils/dates';
import { ChevronLeftIcon, CalendarIcon, BuildingIcon } from '../../ui/icons';
import { JobApplication, Interview } from '../../types/job-applications';
import JobApplicationModal from '../../components/job-applications/JobApplicationModal';
import InterviewModal from '../../components/job-applications/InterviewModal';
import OutcomeModal from '../../components/job-applications/OutcomeModal';

const JobApplicationDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [application, setApplication] = useState<JobApplication | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showInterviewModal, setShowInterviewModal] = useState(false);
  const [showOutcomeModal, setShowOutcomeModal] = useState(false);

  const fetchApplication = async () => {
    try {
      setLoading(true);
      const response = await axiosClient.get(`/api/job-applications/${id}`);
      setApplication(response.data);
      setError(null);
    } catch (err) {
      setError('Failed to load job application details');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (id) {
      fetchApplication();
    }
  }, [id]);

  const handleApplicationUpdated = () => {
    setShowEditModal(false);
    fetchApplication();
  };

  const handleInterviewAdded = () => {
    setShowInterviewModal(false);
    fetchApplication();
  };

  const handleOutcomeRecorded = () => {
    setShowOutcomeModal(false);
    fetchApplication();
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'applied':
        return 'bg-blue-100 text-blue-800';
      case 'interviewing':
        return 'bg-purple-100 text-purple-800';
      case 'offered':
        return 'bg-green-100 text-green-800';
      case 'rejected':
        return 'bg-red-100 text-red-800';
      case 'withdrawn':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center p-12">
        <Spinner size="lg" />
      </div>
    );
  }

  if (error || !application) {
    return (
      <div className="p-6">
        <Alert type="error" message={error || 'Job application not found'} />
        <div className="mt-4">
          <Button onClick={() => navigate('/job-applications')}>Back to Applications</Button>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="mb-6">
        <Link
          to="/job-applications"
          className="inline-flex items-center text-gray-600 hover:text-gray-900"
        >
          <ChevronLeftIcon className="w-4 h-4 mr-1" />
          Back to Applications
        </Link>
      </div>

      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
          <Heading as="h1" className="mb-1">{application.position}</Heading>
          <div className="flex items-center text-gray-600">
            <BuildingIcon className="w-4 h-4 mr-1" />
            <span>{application.company_name}</span>
            <span className="mx-2">•</span>
            <CalendarIcon className="w-4 h-4 mr-1" />
            <span>Applied on {formatDate(application.application_date)}</span>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Badge className={getStatusColor(application.status)}>
            {application.status.charAt(0).toUpperCase() + application.status.slice(1)}
          </Badge>
          <Button onClick={() => setShowEditModal(true)} variant="outline" size="sm">
            Edit
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <Card className="col-span-2">
          <Card.Header>
            <Card.Title>Application Details</Card.Title>
          </Card.Header>
          <Card.Content>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <h3 className="text-sm font-medium text-gray-500">Company</h3>
                <p className="mt-1">{application.company_name}</p>
              </div>
              <div>
                <h3 className="text-sm font-medium text-gray-500">Position</h3>
                <p className="mt-1">{application.position}</p>
              </div>
              <div>
                <h3 className="text-sm font-medium text-gray-500">Application Date</h3>
                <p className="mt-1">{formatDate(application.application_date)}</p>
              </div>
              <div>
                <h3 className="text-sm font-medium text-gray-500">Salary Range</h3>
                <p className="mt-1">{application.salary_range || 'Not specified'}</p>
              </div>
              <div>
                <h3 className="text-sm font-medium text-gray-500">Contact Person</h3>
                <p className="mt-1">{application.contact_person || 'Not specified'}</p>
              </div>
              <div>
                <h3 className="text-sm font-medium text-gray-500">Contact Email</h3>
                <p className="mt-1">
                  {application.contact_email ? (
                    <a href={`mailto:${application.contact_email}`} className="text-primary-600 hover:underline">
                      {application.contact_email}
                    </a>
                  ) : (
                    'Not specified'
                  )}
                </p>
              </div>
              {application.application_url && (
                <div className="md:col-span-2">
                  <h3 className="text-sm font-medium text-gray-500">Application URL</h3>
                  <p className="mt-1">
                    <a
                      href={application.application_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-primary-600 hover:underline"
                    >
                      {application.application_url}
                    </a>
                  </p>
                </div>
              )}
              {application.job_description && (
                <div className="md:col-span-2">
                  <h3 className="text-sm font-medium text-gray-500">Job Description</h3>
                  <p className="mt-1 whitespace-pre-line">{application.job_description}</p>
                </div>
              )}
              {application.notes && (
                <div className="md:col-span-2">
                  <h3 className="text-sm font-medium text-gray-500">Notes</h3>
                  <p className="mt-1 whitespace-pre-line">{application.notes}</p>
                </div>
              )}
            </div>
          </Card.Content>
        </Card>

        <Card>
          <Card.Header>
            <Card.Title>Actions</Card.Title>
          </Card.Header>
          <Card.Content>
            <div className="space-y-4">
              <Button
                onClick={() => setShowInterviewModal(true)}
                className="w-full"
                disabled={['offered', 'rejected', 'withdrawn'].includes(application.status)}
              >
                Schedule Interview
              </Button>

              <Button
                onClick={() => setShowOutcomeModal(true)}
                className="w-full"
                variant="outline"
                disabled={['offered', 'rejected', 'withdrawn'].includes(application.status)}
              >
                Record Outcome
              </Button>
            </div>
          </Card.Content>
        </Card>
      </div>

      <Tabs defaultValue="interviews">
        <Tabs.List>
          <Tabs.Trigger value="interviews">
            Interviews
            {application.interviews.length > 0 && (
              <Badge variant="secondary" className="ml-2">
                {application.interviews.length}
              </Badge>
            )}
          </Tabs.Trigger>
          <Tabs.Trigger value="outcome" disabled={!application.outcome}>
            Outcome
          </Tabs.Trigger>
        </Tabs.List>

        <Tabs.Content value="interviews" className="p-4">
          {application.interviews.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-gray-500 mb-4">No interviews scheduled yet</p>
              <Button
                onClick={() => setShowInterviewModal(true)}
                disabled={['offered', 'rejected', 'withdrawn'].includes(application.status)}
              >
                Schedule Interview
              </Button>
            </div>
          ) : (
            <div className="space-y-4">
              {application.interviews.map((interview: Interview) => (
                <Card key={interview.id}>
                  <Card.Content className="p-4">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                      <div>
                        <h3 className="font-medium text-lg">
                          {formatDate(interview.interview_date)} at {interview.interview_time}
                        </h3>
                        <p className="text-gray-600">
                          {interview.interview_type.charAt(0).toUpperCase() + interview.interview_type.slice(1)} interview with {interview.with_person}
                        </p>
                        {interview.location && (
                          <p className="text-gray-600 mt-1">Location: {interview.location}</p>
                        )}
                        {interview.notes && (
                          <div className="mt-2">
                            <h4 className="text-sm font-medium text-gray-500">Notes</h4>
                            <p className="whitespace-pre-line">{interview.notes}</p>
                          </div>
                        )}
                      </div>
                      <Badge
                        variant={
                          new Date(interview.interview_date) > new Date()
                            ? 'default'
                            : 'outline'
                        }
                      >
                        {new Date(interview.interview_date) > new Date() ? 'Upcoming' : 'Past'}
                      </Badge>
                    </div>
                  </Card.Content>
                </Card>
              ))}
            </div>
          )}
        </Tabs.Content>

        <Tabs.Content value="outcome" className="p-4">
          {application.outcome ? (
            <Card>
              <Card.Content className="p-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <h3 className="text-sm font-medium text-gray-500">Outcome</h3>
                    <p className="mt-1">
                      <Badge
                        className={
                          application.outcome.outcome === 'offered'
                            ? 'bg-green-100 text-green-800'
                            : application.outcome.outcome === 'rejected'
                            ? 'bg-red-100 text-red-800'
                            : 'bg-gray-100 text-gray-800'
                        }
                      >
                        {application.outcome.outcome.charAt(0).toUpperCase() + application.outcome.outcome.slice(1)}
                      </Badge>
                    </p>
                  </div>
                  <div>
                    <h3 className="text-sm font-medium text-gray-500">Date</h3>
                    <p className="mt-1">{formatDate(application.outcome.outcome_date)}</p>
                  </div>
                  {application.outcome.salary_offered && (
                    <div>
                      <h3 className="text-sm font-medium text-gray-500">Salary Offered</h3>
                      <p className="mt-1">{application.outcome.salary_offered}</p>
                    </div>
                  )}
                  {application.outcome.feedback && (
                    <div className="md:col-span-2">
                      <h3 className="text-sm font-medium text-gray-500">Feedback</h3>
                      <p className="mt-1 whitespace-pre-line">{application.outcome.feedback}</p>
                    </div>
                  )}
                  {application.outcome.notes && (
                    <div className="md:col-span-2">
                      <h3 className="text-sm font-medium text-gray-500">Notes</h3>
                      <p className="mt-1 whitespace-pre-line">{application.outcome.notes}</p>
                    </div>
                  )}
                </div>
              </Card.Content>
            </Card>
          ) : (
            <div className="text-center py-8">
              <p className="text-gray-500 mb-4">No outcome recorded yet</p>
              <Button
                onClick={() => setShowOutcomeModal(true)}
                disabled={['offered', 'rejected', 'withdrawn'].includes(application.status)}
              >
                Record Outcome
              </Button>
            </div>
          )}
        </Tabs.Content>
      </Tabs>

      {showEditModal && (
        <JobApplicationModal
          application={application}
          onClose={() => setShowEditModal(false)}
          onSave={handleApplicationUpdated}
        />
      )}

      {showInterviewModal && (
        <InterviewModal
          applicationId={application.id}
          onClose={() => setShowInterviewModal(false)}
          onSave={handleInterviewAdded}
        />
      )}

      {showOutcomeModal && (
        <OutcomeModal
          applicationId={application.id}
          onClose={() => setShowOutcomeModal(false)}
          onSave={handleOutcomeRecorded}
        />
      )}
    </div>
  );
};

export default JobApplicationDetailPage;

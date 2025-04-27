import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { axiosClient } from '../../lib/axios';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../ui/Tabs';
import { Separator } from '../../ui/Separator';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { formatDate } from '../../utils/dates';
import { ArrowLeft, Edit, Calendar, Building, Clock, MessageSquare, User, Mail, Link as LinkIcon, FileText } from 'lucide-react';
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
  const [showInterviewModal, setShowInterviewModal] = useState(false);
  const [showOutcomeModal, setShowOutcomeModal] = useState(false);
  const [activeTab, setActiveTab] = useState('details');

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

  const handleInterviewAdded = () => {
    setShowInterviewModal(false);
    fetchApplication();
  };

  const handleOutcomeRecorded = () => {
    setShowOutcomeModal(false);
    fetchApplication();
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'applied':
        return <Badge variant="secondary">Applied</Badge>;
      case 'interviewing':
        return <Badge variant="warning">Interviewing</Badge>;
      case 'offered':
        return <Badge variant="success">Offered</Badge>;
      case 'rejected':
        return <Badge variant="danger">Rejected</Badge>;
      case 'withdrawn':
        return <Badge variant="outline">Withdrawn</Badge>;
      default:
        return <Badge variant="default">{status}</Badge>;
    }
  };

  if (loading) {
    return (
      <PageContainer title="Application Details">
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>
      </PageContainer>
    );
  }

  if (error || !application) {
    return (
      <PageContainer title="Error">
        <Card variant="elevated">
          <CardContent>
            <div className="bg-error/10 text-error p-4 rounded-lg mb-4">
              {error || 'Job application not found'}
            </div>
            <Button variant="outlined" onClick={() => navigate('/job-applications')}>Back to Applications</Button>
          </CardContent>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer
      title={application.position}
      subtitle={`${application.company_name} - Applied on ${formatDate(application.application_date)}`}
      actions={
        <div className="flex space-x-2">
          <Button variant="outlined" onClick={() => navigate(`/job-applications/${id}/edit`)} icon={<Edit className="h-4 w-4 mr-2" />}>
            Edit
          </Button>
          {!['offered', 'rejected', 'withdrawn'].includes(application.status) && (
            <>
              <Button
                variant="filled"
                onClick={() => setShowInterviewModal(true)}
                icon={<Calendar className="h-4 w-4 mr-2" />}
              >
                Schedule Interview
              </Button>
              <Button
                variant="outlined"
                onClick={() => setShowOutcomeModal(true)}
              >
                Record Outcome
              </Button>
            </>
          )}
        </div>
      }
    >
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full mb-6">
        <TabsList className="grid grid-cols-3">
          <TabsTrigger value="details">Details</TabsTrigger>
          <TabsTrigger value="interviews">
            Interviews
            {application.interviews.length > 0 && (
              <Badge variant="secondary" className="ml-2">
                {application.interviews.length}
              </Badge>
            )}
          </TabsTrigger>
          <TabsTrigger value="outcome" disabled={!application.outcome}>
            Outcome
          </TabsTrigger>
        </TabsList>

        <TabsContent value="details" className="mt-4">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div className="md:col-span-8">
              <Card variant="elevated">
                <CardHeader>
                  <CardTitle>Application Details</CardTitle>
                  <CardDescription>Information about your job application</CardDescription>
                </CardHeader>
                <CardContent>
                  <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <Building className="h-4 w-4 mr-1" />
                        Company
                      </dt>
                      <dd className="text-on-surface font-medium">{application.company_name}</dd>
                    </div>
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <User className="h-4 w-4 mr-1" />
                        Position
                      </dt>
                      <dd className="text-on-surface font-medium">{application.position}</dd>
                    </div>
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <Calendar className="h-4 w-4 mr-1" />
                        Application Date
                      </dt>
                      <dd className="text-on-surface font-medium">{formatDate(application.application_date)}</dd>
                    </div>
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm">
                        Salary Range
                      </dt>
                      <dd className="text-on-surface font-medium">{application.salary_range || 'Not specified'}</dd>
                    </div>
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <User className="h-4 w-4 mr-1" />
                        Contact Person
                      </dt>
                      <dd className="text-on-surface font-medium">{application.contact_person || 'Not specified'}</dd>
                    </div>
                    <div className="space-y-1">
                      <dt className="text-on-surface-variant text-sm flex items-center">
                        <Mail className="h-4 w-4 mr-1" />
                        Contact Email
                      </dt>
                      <dd className="text-on-surface font-medium">
                        {application.contact_email ? (
                          <a href={`mailto:${application.contact_email}`} className="text-primary hover:underline">
                            {application.contact_email}
                          </a>
                        ) : (
                          'Not specified'
                        )}
                      </dd>
                    </div>
                    {application.application_url && (
                      <div className="md:col-span-2 space-y-1">
                        <dt className="text-on-surface-variant text-sm flex items-center">
                          <LinkIcon className="h-4 w-4 mr-1" />
                          Application URL
                        </dt>
                        <dd className="text-on-surface font-medium">
                          <a
                            href={application.application_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-primary hover:underline"
                          >
                            {application.application_url}
                          </a>
                        </dd>
                      </div>
                    )}
                  </dl>

                  {application.job_description && (
                    <div className="mt-6">
                      <h4 className="text-on-surface-variant text-sm flex items-center mb-2">
                        <FileText className="h-4 w-4 mr-1" />
                        Job Description
                      </h4>
                      <p className="text-on-surface bg-surface-variant p-3 rounded-md whitespace-pre-line">
                        {application.job_description}
                      </p>
                    </div>
                  )}

                  {application.notes && (
                    <div className="mt-6">
                      <h4 className="text-on-surface-variant text-sm flex items-center mb-2">
                        <MessageSquare className="h-4 w-4 mr-1" />
                        Notes
                      </h4>
                      <p className="text-on-surface bg-surface-variant p-3 rounded-md whitespace-pre-line">
                        {application.notes}
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>

            <div className="md:col-span-4">
              <Card variant="filled">
                <CardHeader>
                  <CardTitle>Status</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="flex flex-col items-center py-4">
                    <div className="bg-surface-variant w-16 h-16 rounded-full flex items-center justify-center mb-3">
                      {getStatusBadge(application.status)}
                    </div>
                    <h3 className="text-lg font-medium mb-2">
                      {application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                    </h3>
                    <p className="text-on-surface-variant text-center">
                      {application.status === 'applied' && 'Your application has been submitted.'}
                      {application.status === 'interviewing' && 'You are in the interview process.'}
                      {application.status === 'offered' && 'Congratulations! You have received an offer.'}
                      {application.status === 'rejected' && 'This application was not successful.'}
                      {application.status === 'withdrawn' && 'You have withdrawn this application.'}
                    </p>
                  </div>
                </CardContent>
              </Card>

              <Card variant="outlined" className="mt-6">
                <CardHeader>
                  <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {!['offered', 'rejected', 'withdrawn'].includes(application.status) && (
                      <>
                        <Button
                          variant="filled"
                          className="w-full"
                          onClick={() => setShowInterviewModal(true)}
                          icon={<Calendar className="h-4 w-4 mr-2" />}
                        >
                          Schedule Interview
                        </Button>
                        <Button
                          variant="outlined"
                          className="w-full"
                          onClick={() => setShowOutcomeModal(true)}
                        >
                          Record Outcome
                        </Button>
                      </>
                    )}
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="interviews" className="mt-4">
          <Card variant="elevated">
            <CardHeader>
              <CardTitle>Interviews</CardTitle>
              <CardDescription>Track your interviews for this application</CardDescription>
            </CardHeader>
            <CardContent>
              {application.interviews.length === 0 ? (
                <div className="text-center py-8">
                  <Clock className="h-12 w-12 text-on-surface-variant mx-auto mb-2 opacity-50" />
                  <p className="text-on-surface-variant mb-4">No interviews scheduled yet</p>
                  {!['offered', 'rejected', 'withdrawn'].includes(application.status) && (
                    <Button
                      variant="filled"
                      onClick={() => setShowInterviewModal(true)}
                      icon={<Calendar className="h-4 w-4 mr-2" />}
                    >
                      Schedule Interview
                    </Button>
                  )}
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Date & Time</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>With</TableHead>
                      <TableHead>Location</TableHead>
                      <TableHead>Notes</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {application.interviews.map((interview: Interview) => (
                      <TableRow key={interview.id}>
                        <TableCell>
                          <div className="font-medium">{formatDate(interview.interview_date)}</div>
                          <div className="text-on-surface-variant text-sm">{interview.interview_time}</div>
                        </TableCell>
                        <TableCell>{interview.interview_type}</TableCell>
                        <TableCell>{interview.with_person || 'Not specified'}</TableCell>
                        <TableCell>{interview.location || 'Not specified'}</TableCell>
                        <TableCell>
                          {interview.notes ? (
                            <button
                              onClick={() => alert(interview.notes)}
                              className="text-primary hover:underline text-sm"
                            >
                              View Notes
                            </button>
                          ) : (
                            'No notes'
                          )}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}

              {application.interviews.length > 0 && !['offered', 'rejected', 'withdrawn'].includes(application.status) && (
                <div className="mt-6 flex justify-end">
                  <Button
                    variant="filled"
                    onClick={() => setShowInterviewModal(true)}
                    icon={<Calendar className="h-4 w-4 mr-2" />}
                  >
                    Add Another Interview
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="outcome" className="mt-4">
          {application.outcome ? (
            <Card variant="elevated">
              <CardHeader>
                <CardTitle>Application Outcome</CardTitle>
                <CardDescription>Final result of your application</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <h4 className="font-medium text-on-surface-variant mb-2">Outcome</h4>
                    <div className="flex items-center">
                      {getStatusBadge(application.status)}
                      <span className="ml-2 font-medium">
                        {application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                      </span>
                    </div>
                  </div>

                  <div>
                    <h4 className="font-medium text-on-surface-variant mb-2">Date Recorded</h4>
                    <p>{formatDate(application.outcome.outcome_date)}</p>
                  </div>

                  {application.outcome.salary_offered && (
                    <div>
                      <h4 className="font-medium text-on-surface-variant mb-2">Salary Offered</h4>
                      <p>{application.outcome.salary_offered}</p>
                    </div>
                  )}

                  {application.outcome.feedback && (
                    <div>
                      <h4 className="font-medium text-on-surface-variant mb-2">Feedback</h4>
                      <p>{application.outcome.feedback}</p>
                    </div>
                  )}
                </div>

                {application.outcome.notes && (
                  <div className="mt-6">
                    <h4 className="font-medium text-on-surface-variant mb-2">Additional Notes</h4>
                    <p className="text-on-surface bg-surface-variant p-3 rounded-md whitespace-pre-line">
                      {application.outcome.notes}
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          ) : (
            <Card variant="elevated">
              <CardContent>
                <div className="text-center py-8">
                  <p className="text-on-surface-variant mb-4">No outcome has been recorded yet</p>
                  {!['offered', 'rejected', 'withdrawn'].includes(application.status) && (
                    <Button onClick={() => setShowOutcomeModal(true)} variant="filled">
                      Record Outcome
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>
          )}
        </TabsContent>
      </Tabs>

      <div className="flex justify-between mt-8">
        <Button variant="outlined" onClick={() => navigate('/job-applications')} icon={<ArrowLeft className="h-4 w-4 mr-2" />}>
          Back to Applications
        </Button>
        <Button
          variant="text"
          className="w-full"
          onClick={() => navigate(`/job-applications/${id}/edit`)}
        >
          Edit Application
        </Button>
      </div>

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
    </PageContainer>
  );
};

export default JobApplicationDetailPage;

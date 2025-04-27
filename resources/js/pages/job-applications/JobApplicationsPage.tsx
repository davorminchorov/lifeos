import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { axiosClient } from '../../lib/axios';
import { Button } from '../../ui';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../ui/Card';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '../../ui/Table';
import { Badge } from '../../ui/Badge';
import { PageContainer, PageSection } from '../../ui/PageContainer';
import { formatDate } from '../../utils/dates';
import { PlusCircle } from 'lucide-react';
import { JobApplication } from '../../types/job-applications';

const JobApplicationsPage: React.FC = () => {
  const [applications, setApplications] = useState<JobApplication[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const navigate = useNavigate();

  const fetchApplications = async () => {
    try {
      setLoading(true);
      const response = await axiosClient.get('/api/job-applications');
      setApplications(Array.isArray(response.data) ? response.data : []);
      setError(null);
    } catch (err) {
      setError('Failed to load job applications');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchApplications();
  }, []);

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

  return (
    <PageContainer
      title="Job Applications"
      subtitle="Track and manage your job search applications"
      actions={
        <Button
          onClick={() => navigate('/job-applications/create')}
          variant="filled"
          icon={<PlusCircle className="h-4 w-4 mr-2" />}
        >
          Add Application
        </Button>
      }
    >
      {error && (
        <div className="mb-6">
          <Card variant="elevated">
            <CardContent>
              <div className="bg-error/10 text-error p-4 rounded-lg">
                {error}
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      <Card variant="elevated">
        <CardHeader>
          <CardTitle>All Applications</CardTitle>
          <CardDescription>Track the status of all your job applications</CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="flex justify-center items-center h-64">
              <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
          ) : applications.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-on-surface-variant mb-4">No job applications found</p>
              <Button onClick={() => navigate('/job-applications/create')} variant="filled">Add Your First Application</Button>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Company</TableHead>
                    <TableHead>Position</TableHead>
                    <TableHead>Applied On</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {applications.map((application) => (
                    <TableRow key={application.id}>
                      <TableCell>{application.company_name}</TableCell>
                      <TableCell>{application.position}</TableCell>
                      <TableCell>{formatDate(application.application_date)}</TableCell>
                      <TableCell>{getStatusBadge(application.status)}</TableCell>
                      <TableCell>
                        <Link to={`/job-applications/${application.id}`}>
                          <Button variant="outlined" size="sm">
                            View
                          </Button>
                        </Link>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>

      {applications.length > 0 && (
        <div className="mt-8 text-center">
          <p className="text-on-surface-variant mb-2">Need help organizing your job search?</p>
          <p className="text-on-surface mb-4">Check out our tips for effective job application tracking.</p>
          <Button variant="outlined" onClick={() => window.open('/resources/job-search-tips', '_blank')}>
            View Job Search Tips
          </Button>
        </div>
      )}
    </PageContainer>
  );
};

export default JobApplicationsPage;

import React, { useState, useEffect } from 'react';
import { Button, Card, Heading, Spinner, Table } from '../../ui';
import { Alert } from '../../components/ui/Alert';
import { formatDate } from '../../utils/dates';
import { PlusIcon } from '../../ui/icons';
import { axiosClient } from '../../lib/axios';
import { JobApplication } from '../../types/job-applications';
import JobApplicationModal from '../../components/job-applications/JobApplicationModal';
import { Link } from 'react-router-dom';

const JobApplicationsPage: React.FC = () => {
  const [applications, setApplications] = useState<JobApplication[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);

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

  const handleApplicationAdded = () => {
    setShowModal(false);
    fetchApplications();
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

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <Heading as="h1">Job Applications</Heading>
        <Button onClick={() => setShowModal(true)} className="flex items-center gap-2">
          <PlusIcon className="w-4 h-4" />
          Add Application
        </Button>
      </div>

      {error && <Alert type="error" message={error} className="mb-4" />}

      <Card className="overflow-hidden">
        {loading ? (
          <div className="flex justify-center p-8">
            <Spinner size="lg" />
          </div>
        ) : applications.length === 0 ? (
          <div className="p-8 text-center">
            <p className="text-gray-500 mb-4">No job applications found</p>
            <Button onClick={() => setShowModal(true)}>Add Your First Application</Button>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <Table>
              <Table.Header>
                <Table.Row>
                  <Table.HeaderCell>Company</Table.HeaderCell>
                  <Table.HeaderCell>Position</Table.HeaderCell>
                  <Table.HeaderCell>Applied</Table.HeaderCell>
                  <Table.HeaderCell>Status</Table.HeaderCell>
                  <Table.HeaderCell>Actions</Table.HeaderCell>
                </Table.Row>
              </Table.Header>
              <Table.Body>
                {applications.map((application) => (
                  <Table.Row key={application.id}>
                    <Table.Cell>{application.company_name}</Table.Cell>
                    <Table.Cell>{application.position}</Table.Cell>
                    <Table.Cell>{formatDate(application.application_date)}</Table.Cell>
                    <Table.Cell>
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(application.status)}`}>
                        {application.status.charAt(0).toUpperCase() + application.status.slice(1)}
                      </span>
                    </Table.Cell>
                    <Table.Cell>
                      <Link
                        to={`/job-applications/${application.id}`}
                        className="text-primary-600 hover:text-primary-800 font-medium"
                      >
                        View
                      </Link>
                    </Table.Cell>
                  </Table.Row>
                ))}
              </Table.Body>
            </Table>
          </div>
        )}
      </Card>

      {showModal && (
        <JobApplicationModal
          onClose={() => setShowModal(false)}
          onSave={handleApplicationAdded}
        />
      )}
    </div>
  );
};

export default JobApplicationsPage;

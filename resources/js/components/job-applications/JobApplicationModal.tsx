import React, { useState } from 'react';
import { Modal, Button, Input, Select, Textarea } from '../../ui';
import { JobApplication, JobApplicationFormData } from '../../types/job-applications';
import { axiosClient } from '../../lib/axios';

interface JobApplicationModalProps {
  application?: JobApplication;
  onClose: () => void;
  onSave: () => void;
}

const JobApplicationModal: React.FC<JobApplicationModalProps> = ({
  application,
  onClose,
  onSave,
}) => {
  const isEditing = !!application;
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [formData, setFormData] = useState<JobApplicationFormData>({
    company_name: application?.company_name || '',
    position: application?.position || '',
    application_date: application?.application_date || new Date().toISOString().split('T')[0],
    job_description: application?.job_description || '',
    application_url: application?.application_url || '',
    salary_range: application?.salary_range || '',
    contact_person: application?.contact_person || '',
    contact_email: application?.contact_email || '',
    status: application?.status || 'applied',
    notes: application?.notes || '',
  });

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      if (isEditing) {
        await axiosClient.put(`/api/job-applications/${application.id}`, formData);
      } else {
        await axiosClient.post('/api/job-applications', formData);
      }
      onSave();
    } catch (err: any) {
      console.error('Error saving job application:', err);
      setError(err.response?.data?.message || 'Failed to save job application');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal
      title={isEditing ? 'Edit Job Application' : 'Add Job Application'}
      onClose={onClose}
      size="lg"
    >
      <form onSubmit={handleSubmit}>
        <div className="space-y-4 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input
              label="Company Name"
              name="company_name"
              value={formData.company_name}
              onChange={handleInputChange}
              required
            />
            <Input
              label="Position"
              name="position"
              value={formData.position}
              onChange={handleInputChange}
              required
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input
              label="Application Date"
              name="application_date"
              type="date"
              value={formData.application_date}
              onChange={handleInputChange}
              required
            />
            <Select
              label="Status"
              name="status"
              value={formData.status}
              onChange={handleInputChange}
              required
            >
              <option value="applied">Applied</option>
              <option value="interviewing">Interviewing</option>
              <option value="offered">Offered</option>
              <option value="rejected">Rejected</option>
              <option value="withdrawn">Withdrawn</option>
            </Select>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input
              label="Salary Range (optional)"
              name="salary_range"
              value={formData.salary_range || ''}
              onChange={handleInputChange}
            />
            <Input
              label="Application URL (optional)"
              name="application_url"
              type="url"
              value={formData.application_url || ''}
              onChange={handleInputChange}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input
              label="Contact Person (optional)"
              name="contact_person"
              value={formData.contact_person || ''}
              onChange={handleInputChange}
            />
            <Input
              label="Contact Email (optional)"
              name="contact_email"
              type="email"
              value={formData.contact_email || ''}
              onChange={handleInputChange}
            />
          </div>

          <Textarea
            label="Job Description (optional)"
            name="job_description"
            value={formData.job_description || ''}
            onChange={handleInputChange}
            rows={3}
          />

          <Textarea
            label="Notes (optional)"
            name="notes"
            value={formData.notes || ''}
            onChange={handleInputChange}
            rows={3}
          />
        </div>

        {error && <p className="text-red-500 mb-4">{error}</p>}

        <div className="flex justify-end gap-2">
          <Button variant="outline" onClick={onClose} type="button">
            Cancel
          </Button>
          <Button type="submit" disabled={loading}>
            {loading ? 'Saving...' : isEditing ? 'Update' : 'Save'}
          </Button>
        </div>
      </form>
    </Modal>
  );
};

export default JobApplicationModal;

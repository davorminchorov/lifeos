import React, { useState } from 'react';
import { Button } from '../../ui';
import { JobApplication, JobApplicationFormData } from '../../types/job-applications';
import { axiosClient } from '../../lib/axios';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '../../ui/Dialog';
import { Input } from '../../ui/Input';
import { Textarea } from '../../ui/Textarea';
import { Label } from '../../ui/Label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../ui/Select';
import { Building, Briefcase, Calendar, DollarSign, Link, User, Mail, FileText, MessageSquare } from 'lucide-react';

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

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleStatusChange = (value: string) => {
    setFormData((prev) => ({ ...prev, status: value as JobApplicationFormData['status'] }));
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
    <Dialog open={true} onOpenChange={(open) => !open && onClose()}>
      <DialogContent className="sm:max-w-[600px]">
        <form onSubmit={handleSubmit}>
          <DialogHeader>
            <DialogTitle>{isEditing ? 'Edit Job Application' : 'Add Job Application'}</DialogTitle>
            <DialogDescription>
              {isEditing ? 'Update the details of your job application.' : 'Enter the details of your job application.'}
            </DialogDescription>
          </DialogHeader>

          <div className="grid gap-6 py-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="company_name" className="flex items-center">
                  <Building className="h-4 w-4 mr-2" />
                  Company Name
                </Label>
                <Input
                  id="company_name"
                  name="company_name"
                  value={formData.company_name}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="position" className="flex items-center">
                  <Briefcase className="h-4 w-4 mr-2" />
                  Position
                </Label>
                <Input
                  id="position"
                  name="position"
                  value={formData.position}
                  onChange={handleInputChange}
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="application_date" className="flex items-center">
                  <Calendar className="h-4 w-4 mr-2" />
                  Application Date
                </Label>
                <Input
                  id="application_date"
                  name="application_date"
                  type="date"
                  value={formData.application_date}
                  onChange={handleInputChange}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="status" className="flex items-center">
                  Status
                </Label>
                <Select
                  value={formData.status}
                  onValueChange={handleStatusChange}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="applied">Applied</SelectItem>
                    <SelectItem value="interviewing">Interviewing</SelectItem>
                    <SelectItem value="offered">Offered</SelectItem>
                    <SelectItem value="rejected">Rejected</SelectItem>
                    <SelectItem value="withdrawn">Withdrawn</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="salary_range" className="flex items-center">
                  <DollarSign className="h-4 w-4 mr-2" />
                  Salary Range (optional)
                </Label>
                <Input
                  id="salary_range"
                  name="salary_range"
                  value={formData.salary_range || ''}
                  onChange={handleInputChange}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="application_url" className="flex items-center">
                  <Link className="h-4 w-4 mr-2" />
                  Application URL (optional)
                </Label>
                <Input
                  id="application_url"
                  name="application_url"
                  type="url"
                  value={formData.application_url || ''}
                  onChange={handleInputChange}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="contact_person" className="flex items-center">
                  <User className="h-4 w-4 mr-2" />
                  Contact Person (optional)
                </Label>
                <Input
                  id="contact_person"
                  name="contact_person"
                  value={formData.contact_person || ''}
                  onChange={handleInputChange}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="contact_email" className="flex items-center">
                  <Mail className="h-4 w-4 mr-2" />
                  Contact Email (optional)
                </Label>
                <Input
                  id="contact_email"
                  name="contact_email"
                  type="email"
                  value={formData.contact_email || ''}
                  onChange={handleInputChange}
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="job_description" className="flex items-center">
                <FileText className="h-4 w-4 mr-2" />
                Job Description (optional)
              </Label>
              <Textarea
                id="job_description"
                name="job_description"
                value={formData.job_description || ''}
                onChange={handleInputChange}
                rows={3}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="notes" className="flex items-center">
                <MessageSquare className="h-4 w-4 mr-2" />
                Notes (optional)
              </Label>
              <Textarea
                id="notes"
                name="notes"
                value={formData.notes || ''}
                onChange={handleInputChange}
                rows={3}
              />
            </div>

            {error && (
              <div className="bg-error/10 text-error p-4 rounded-lg">
                {error}
              </div>
            )}
          </div>

          <DialogFooter>
            <Button variant="outlined" onClick={onClose} type="button">
              Cancel
            </Button>
            <Button variant="filled" type="submit" disabled={loading}>
              {loading ? 'Saving...' : isEditing ? 'Update' : 'Save'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default JobApplicationModal;

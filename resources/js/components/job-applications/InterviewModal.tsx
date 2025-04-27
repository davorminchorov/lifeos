import React, { useState } from 'react';
import { Modal, Button, Input, Select, Textarea } from '../../ui';
import { InterviewFormData } from '../../types/job-applications';
import { axiosClient } from '../../lib/axios';

interface InterviewModalProps {
  applicationId: string;
  onClose: () => void;
  onSuccess: () => void;
  onSave?: () => void;
}

const InterviewModal: React.FC<InterviewModalProps> = ({
  applicationId,
  onClose,
  onSuccess,
  onSave
}) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [formData, setFormData] = useState<InterviewFormData>({
    interview_date: new Date().toISOString().split('T')[0],
    interview_time: '10:00',
    interview_type: 'video',
    with_person: '',
    location: '',
    notes: ''
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
      await axiosClient.post(`/api/job-applications/${applicationId}/interviews`, formData);
      onSuccess();
      if (onSave) onSave();
    } catch (err: any) {
      console.error('Error scheduling interview:', err);
      setError(err.response?.data?.message || 'Failed to schedule interview');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal
      title="Schedule Interview"
      onClose={onClose}
    >
      <form onSubmit={handleSubmit}>
        <div className="space-y-4 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Input
              label="Interview Date"
              name="interview_date"
              type="date"
              value={formData.interview_date}
              onChange={handleInputChange}
              required
            />
            <Input
              label="Interview Time"
              name="interview_time"
              type="time"
              value={formData.interview_time}
              onChange={handleInputChange}
              required
            />
          </div>

          <Select
            label="Interview Type"
            name="interview_type"
            value={formData.interview_type}
            onChange={handleInputChange}
            required
          >
            <option value="phone">Phone</option>
            <option value="video">Video</option>
            <option value="in-person">In-person</option>
          </Select>

          <Input
            label="With Person"
            name="with_person"
            value={formData.with_person}
            onChange={handleInputChange}
            placeholder="e.g. Hiring Manager, HR, Team Lead"
            required
          />

          <Input
            label="Location (optional)"
            name="location"
            value={formData.location || ''}
            onChange={handleInputChange}
            placeholder="Address, Zoom link, or phone number"
          />

          <Textarea
            label="Notes (optional)"
            name="notes"
            value={formData.notes || ''}
            onChange={handleInputChange}
            placeholder="Preparation notes, questions to ask, etc."
            rows={3}
          />
        </div>

        {error && <p className="text-red-500 mb-4">{error}</p>}

        <div className="flex justify-end gap-2">
          <Button variant="outline" onClick={onClose} type="button">
            Cancel
          </Button>
          <Button type="submit" disabled={loading}>
            {loading ? 'Scheduling...' : 'Schedule Interview'}
          </Button>
        </div>
      </form>
    </Modal>
  );
};

export default InterviewModal;

import React, { useState } from 'react';
import { Modal, Button, Input, Select, Textarea } from '../../ui';
import { OutcomeFormData } from '../../types/job-applications';
import { axiosClient } from '../../lib/axios';

interface OutcomeModalProps {
  applicationId: string;
  onClose: () => void;
  onSuccess: () => void;
  onSave?: () => void;
}

const OutcomeModal: React.FC<OutcomeModalProps> = ({
  applicationId,
  onClose,
  onSuccess,
  onSave
}) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [formData, setFormData] = useState<OutcomeFormData>({
    outcome: 'offered',
    outcome_date: new Date().toISOString().split('T')[0],
    salary_offered: '',
    feedback: '',
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
      await axiosClient.post(`/api/job-applications/${applicationId}/outcome`, formData);
      onSuccess();
      if (onSave) onSave();
    } catch (err: any) {
      console.error('Error recording outcome:', err);
      setError(err.response?.data?.message || 'Failed to record outcome');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Modal
      title="Record Application Outcome"
      onClose={onClose}
    >
      <form onSubmit={handleSubmit}>
        <div className="space-y-4 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Select
              label="Outcome"
              name="outcome"
              value={formData.outcome}
              onChange={handleInputChange}
              required
            >
              <option value="offered">Offered</option>
              <option value="rejected">Rejected</option>
              <option value="withdrawn">Withdrawn</option>
            </Select>

            <Input
              label="Outcome Date"
              name="outcome_date"
              type="date"
              value={formData.outcome_date}
              onChange={handleInputChange}
              required
            />
          </div>

          {formData.outcome === 'offered' && (
            <Input
              label="Salary Offered (optional)"
              name="salary_offered"
              value={formData.salary_offered || ''}
              onChange={handleInputChange}
              placeholder="e.g. $75,000 per year"
            />
          )}

          <Textarea
            label="Feedback (optional)"
            name="feedback"
            value={formData.feedback || ''}
            onChange={handleInputChange}
            placeholder="Any feedback received about your application"
            rows={3}
          />

          <Textarea
            label="Notes (optional)"
            name="notes"
            value={formData.notes || ''}
            onChange={handleInputChange}
            placeholder="Additional notes about the outcome"
            rows={3}
          />
        </div>

        {error && <p className="text-red-500 mb-4">{error}</p>}

        <div className="flex justify-end gap-2">
          <Button variant="outline" onClick={onClose} type="button">
            Cancel
          </Button>
          <Button
            type="submit"
            disabled={loading}
            variant={formData.outcome === 'offered' ? 'default' : 'outline'}
          >
            {loading ? 'Saving...' : 'Record Outcome'}
          </Button>
        </div>
      </form>
    </Modal>
  );
};

export default OutcomeModal;

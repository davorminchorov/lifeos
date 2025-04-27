import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import axios from 'axios';

interface InvestmentFormProps {
  isEditing: boolean;
}

interface InvestmentFormData {
  name: string;
  type: string;
  description: string;
  initial_value: string;
  current_value: string;
}

const InvestmentForm: React.FC<InvestmentFormProps> = ({ isEditing }) => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [formData, setFormData] = useState<InvestmentFormData>({
    name: '',
    type: 'stock', // Default type
    description: '',
    initial_value: '',
    current_value: ''
  });

  const [loading, setLoading] = useState<boolean>(false);
  const [saving, setSaving] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  // Investment types available for selection
  const investmentTypes = [
    { value: 'stock', label: 'Stock' },
    { value: 'bond', label: 'Bond' },
    { value: 'etf', label: 'ETF' },
    { value: 'mutual_fund', label: 'Mutual Fund' },
    { value: 'crypto', label: 'Cryptocurrency' },
    { value: 'real_estate', label: 'Real Estate' },
    { value: 'other', label: 'Other' }
  ];

  useEffect(() => {
    if (isEditing && id) {
      fetchInvestment();
    }
  }, [isEditing, id]);

  const fetchInvestment = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`/api/investments/${id}`);
      const investment = response.data.data;

      setFormData({
        name: investment.name,
        type: investment.type,
        description: investment.description || '',
        initial_value: investment.initial_value?.toString() || '',
        current_value: investment.current_value?.toString() || ''
      });

      setError(null);
    } catch (err) {
      setError('Failed to load investment data. Please try again later.');
      console.error('Error fetching investment:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));

    // Clear validation error when field is edited
    if (validationErrors[name]) {
      setValidationErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[name];
        return newErrors;
      });
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      setSaving(true);
      setValidationErrors({});

      // Convert string values to numbers for API
      const apiData = {
        ...formData,
        initial_value: formData.initial_value ? parseFloat(formData.initial_value) : null,
        current_value: formData.current_value ? parseFloat(formData.current_value) : null
      };

      let response;

      if (isEditing) {
        response = await axios.put(`/api/investments/${id}`, apiData);
      } else {
        response = await axios.post('/api/investments', apiData);
      }

      // Navigate back to investments list with success message
      navigate('/investments', {
        state: {
          message: isEditing
            ? 'Investment updated successfully'
            : 'Investment created successfully'
        }
      });
    } catch (err: any) {
      console.error('Error saving investment:', err);

      // Handle validation errors from the API
      if (err.response?.status === 422 && err.response?.data?.errors) {
        const apiErrors = err.response.data.errors;
        const formattedErrors: Record<string, string> = {};

        // Format errors to match our state structure
        Object.keys(apiErrors).forEach(field => {
          formattedErrors[field] = apiErrors[field][0];
        });

        setValidationErrors(formattedErrors);
      } else {
        setError('Failed to save investment. Please try again later.');
      }
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="text-center p-4">Loading investment data...</div>;
  }

  return (
    <div className="container">
      <div className="card">
        <div className="card-header">
          <h1 className="card-title">
            {isEditing ? 'Edit Investment' : 'Create New Investment'}
          </h1>
        </div>
        <div className="card-body">
          {error && (
            <div className="alert alert-danger mb-4">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit}>
            <div className="mb-3">
              <label htmlFor="name" className="form-label">Name</label>
              <input
                type="text"
                className={`form-control ${validationErrors.name ? 'is-invalid' : ''}`}
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
              />
              {validationErrors.name && (
                <div className="invalid-feedback">{validationErrors.name}</div>
              )}
            </div>

            <div className="mb-3">
              <label htmlFor="type" className="form-label">Type</label>
              <select
                className={`form-select ${validationErrors.type ? 'is-invalid' : ''}`}
                id="type"
                name="type"
                value={formData.type}
                onChange={handleChange}
                required
              >
                {investmentTypes.map(type => (
                  <option key={type.value} value={type.value}>
                    {type.label}
                  </option>
                ))}
              </select>
              {validationErrors.type && (
                <div className="invalid-feedback">{validationErrors.type}</div>
              )}
            </div>

            <div className="mb-3">
              <label htmlFor="description" className="form-label">Description</label>
              <textarea
                className={`form-control ${validationErrors.description ? 'is-invalid' : ''}`}
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows={3}
              />
              {validationErrors.description && (
                <div className="invalid-feedback">{validationErrors.description}</div>
              )}
            </div>

            <div className="row">
              <div className="col-md-6 mb-3">
                <label htmlFor="initial_value" className="form-label">Initial Value ($)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  className={`form-control ${validationErrors.initial_value ? 'is-invalid' : ''}`}
                  id="initial_value"
                  name="initial_value"
                  value={formData.initial_value}
                  onChange={handleChange}
                  required
                />
                {validationErrors.initial_value && (
                  <div className="invalid-feedback">{validationErrors.initial_value}</div>
                )}
              </div>

              <div className="col-md-6 mb-3">
                <label htmlFor="current_value" className="form-label">Current Value ($)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  className={`form-control ${validationErrors.current_value ? 'is-invalid' : ''}`}
                  id="current_value"
                  name="current_value"
                  value={formData.current_value}
                  onChange={handleChange}
                  required
                />
                {validationErrors.current_value && (
                  <div className="invalid-feedback">{validationErrors.current_value}</div>
                )}
              </div>
            </div>

            <div className="d-flex justify-content-between mt-4">
              <Link to="/investments" className="btn btn-outline-secondary">
                Cancel
              </Link>
              <button
                type="submit"
                className="btn btn-primary"
                disabled={saving}
              >
                {saving ? (
                  <>
                    <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                  </>
                ) : (
                  isEditing ? 'Update Investment' : 'Create Investment'
                )}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default InvestmentForm;

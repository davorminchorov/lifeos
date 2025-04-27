import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

interface Investment {
  id: number;
  name: string;
  type: string;
  description: string;
  initial_value: number;
  current_value: number;
  created_at: string;
  updated_at: string;
}

const InvestmentDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [investment, setInvestment] = useState<Investment | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchInvestment = async () => {
      try {
        setLoading(true);
        const response = await axios.get(`/api/investments/${id}`);
        setInvestment(response.data.data);
        setError(null);
      } catch (err) {
        setError('Failed to fetch investment details. Please try again later.');
        console.error('Error fetching investment:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchInvestment();
  }, [id]);

  const handleDelete = async () => {
    if (!confirm('Are you sure you want to delete this investment?')) {
      return;
    }

    try {
      await axios.delete(`/api/investments/${id}`);
      navigate('/investments', { state: { message: 'Investment deleted successfully' } });
    } catch (err) {
      setError('Failed to delete investment. Please try again later.');
      console.error('Error deleting investment:', err);
    }
  };

  if (loading) {
    return <div className="text-center p-4">Loading investment details...</div>;
  }

  if (error) {
    return <div className="alert alert-danger">{error}</div>;
  }

  if (!investment) {
    return <div className="alert alert-warning">Investment not found</div>;
  }

  const roi = ((investment.current_value - investment.initial_value) / investment.initial_value) * 100;

  return (
    <div className="container">
      <div className="card mb-4">
        <div className="card-header d-flex justify-content-between align-items-center">
          <h1 className="card-title mb-0">{investment.name}</h1>
          <div>
            <Link to="/investments" className="btn btn-outline-secondary me-2">
              Back to List
            </Link>
            <Link to={`/investments/${id}/edit`} className="btn btn-outline-primary me-2">
              Edit
            </Link>
            <button onClick={handleDelete} className="btn btn-outline-danger">
              Delete
            </button>
          </div>
        </div>
        <div className="card-body">
          <div className="row mb-4">
            <div className="col-md-6">
              <h5>Investment Details</h5>
              <table className="table">
                <tbody>
                  <tr>
                    <th>Type</th>
                    <td>{investment.type}</td>
                  </tr>
                  <tr>
                    <th>Description</th>
                    <td>{investment.description || 'No description provided'}</td>
                  </tr>
                  <tr>
                    <th>Created</th>
                    <td>{new Date(investment.created_at).toLocaleDateString()}</td>
                  </tr>
                  <tr>
                    <th>Last Updated</th>
                    <td>{new Date(investment.updated_at).toLocaleDateString()}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div className="col-md-6">
              <h5>Performance</h5>
              <div className="card bg-light">
                <div className="card-body">
                  <div className="row">
                    <div className="col-6">
                      <div className="mb-3">
                        <div className="text-muted small">Initial Value</div>
                        <div className="fs-4">${investment.initial_value.toFixed(2)}</div>
                      </div>
                    </div>
                    <div className="col-6">
                      <div className="mb-3">
                        <div className="text-muted small">Current Value</div>
                        <div className="fs-4">${investment.current_value.toFixed(2)}</div>
                      </div>
                    </div>
                  </div>
                  <div className="mb-2">
                    <div className="text-muted small">Return on Investment</div>
                    <div className={`fs-4 ${roi >= 0 ? 'text-success' : 'text-danger'}`}>
                      {roi.toFixed(2)}%
                    </div>
                  </div>
                  <div className="progress">
                    <div
                      className={`progress-bar ${roi >= 0 ? 'bg-success' : 'bg-danger'}`}
                      role="progressbar"
                      style={{ width: `${Math.min(Math.abs(roi), 100)}%` }}
                      aria-valuenow={Math.abs(roi)}
                      aria-valuemin={0}
                      aria-valuemax={100}
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default InvestmentDetail;

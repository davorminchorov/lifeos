import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
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

const InvestmentList: React.FC = () => {
  const [investments, setInvestments] = useState<Investment[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchInvestments = async () => {
      try {
        setLoading(true);
        const response = await axios.get('/api/investments');
        setInvestments(response.data.data);
        setError(null);
      } catch (err) {
        setError('Failed to fetch investments. Please try again later.');
        console.error('Error fetching investments:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchInvestments();
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this investment?')) {
      return;
    }

    try {
      await axios.delete(`/api/investments/${id}`);
      setInvestments(investments.filter(investment => investment.id !== id));
    } catch (err) {
      setError('Failed to delete investment. Please try again later.');
      console.error('Error deleting investment:', err);
    }
  };

  if (loading) {
    return <div className="text-center p-4">Loading investments...</div>;
  }

  if (error) {
    return <div className="alert alert-danger">{error}</div>;
  }

  return (
    <div className="container">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1>Investments</h1>
        <Link to="/investments/create" className="btn btn-primary">Add Investment</Link>
      </div>

      {investments.length === 0 ? (
        <div className="alert alert-info">
          No investments found. Click the "Add Investment" button to create one.
        </div>
      ) : (
        <div className="table-responsive">
          <table className="table table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Initial Value</th>
                <th>Current Value</th>
                <th>ROI</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {investments.map((investment) => {
                const roi = ((investment.current_value - investment.initial_value) / investment.initial_value) * 100;

                return (
                  <tr key={investment.id}>
                    <td>
                      <Link to={`/investments/${investment.id}`}>
                        {investment.name}
                      </Link>
                    </td>
                    <td>{investment.type}</td>
                    <td>${investment.initial_value.toFixed(2)}</td>
                    <td>${investment.current_value.toFixed(2)}</td>
                    <td className={roi >= 0 ? 'text-success' : 'text-danger'}>
                      {roi.toFixed(2)}%
                    </td>
                    <td>
                      <div className="btn-group">
                        <Link
                          to={`/investments/${investment.id}/edit`}
                          className="btn btn-sm btn-outline-primary"
                        >
                          Edit
                        </Link>
                        <button
                          onClick={() => handleDelete(investment.id)}
                          className="btn btn-sm btn-outline-danger ms-1"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default InvestmentList;

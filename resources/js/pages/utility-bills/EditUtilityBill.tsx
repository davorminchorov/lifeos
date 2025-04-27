import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import UtilityBillForm from '../../components/utility-bills/UtilityBillForm';
import { Card } from '../../ui/Card';
import { useToast } from '../../ui/Toast';

const EditUtilityBill: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [utilityBill, setUtilityBill] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchUtilityBill = async () => {
      setLoading(true);
      try {
        const response = await axios.get(`/api/utility-bills/${id}`);
        setUtilityBill(response.data);
        setError(null);
      } catch (err) {
        setError('Failed to load utility bill data');
        console.error(err);
        toast({
          title: "Error",
          description: "Failed to load utility bill data",
          variant: "destructive",
        });
      } finally {
        setLoading(false);
      }
    };

    fetchUtilityBill();
  }, [id]);

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="animate-pulse space-y-4">
          <div className="h-12 bg-gray-200 rounded w-1/3"></div>
          <div className="h-64 bg-gray-200 rounded w-full"></div>
        </div>
      </div>
    );
  }

  if (error || !utilityBill) {
    return (
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <Card className="p-6 border border-red-200 bg-red-50 text-red-700">
          <p className="font-medium text-lg mb-2">Error Loading Utility Bill</p>
          <p>{error || 'The utility bill could not be found'}</p>
          <button
            onClick={() => navigate('/utility-bills')}
            className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
          >
            Back to Utility Bills
          </button>
        </Card>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8 max-w-4xl">
      <h1 className="text-3xl font-bold mb-6">Edit Utility Bill</h1>
      <UtilityBillForm
        initialData={utilityBill}
        isEditing={true}
      />
    </div>
  );
};

export default EditUtilityBill;

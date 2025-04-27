import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui/Card';
import InvestmentForm from '../../components/investments/InvestmentForm';
import { Button } from '../../ui/Button';
import { ArrowLeft } from 'lucide-react';

const InvestmentEditPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  return (
    <div className="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div className="flex items-center justify-between mb-8">
        <h1 className="text-3xl font-bold tracking-tight text-gray-900">Edit Investment</h1>
        <Button onClick={() => navigate(`/investments/${id}`)} variant="outline" className="flex items-center gap-2">
          <ArrowLeft className="h-4 w-4" />
          Back to Details
        </Button>
      </div>

      <Card className="bg-white shadow-md rounded-xl overflow-hidden">
        <CardHeader className="bg-gray-50 border-b border-gray-100 px-6 py-4">
          <CardTitle className="text-xl font-semibold text-gray-800">Edit Investment</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <InvestmentForm isEditing={true} />
        </CardContent>
      </Card>
    </div>
  );
};

export default InvestmentEditPage;

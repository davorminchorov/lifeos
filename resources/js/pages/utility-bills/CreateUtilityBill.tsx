import React from 'react';
import { useNavigate } from 'react-router-dom';
import UtilityBillForm from '../../components/utility-bills/UtilityBillForm';
import { useToast } from '../../ui/Toast';

const CreateUtilityBill: React.FC = () => {
  const navigate = useNavigate();
  const { toast } = useToast();

  return (
    <div className="container mx-auto px-4 py-8 max-w-4xl">
      <h1 className="text-3xl font-bold mb-6">Create New Utility Bill</h1>
      <UtilityBillForm />
    </div>
  );
};

export default CreateUtilityBill;

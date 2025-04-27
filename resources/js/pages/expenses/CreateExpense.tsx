import React from 'react';
import { useNavigate } from 'react-router-dom';
import ExpenseForm from '../../components/expenses/ExpenseForm';
import { useToast } from '../../ui/Toast';

const CreateExpense: React.FC = () => {
  const navigate = useNavigate();
  const { toast } = useToast();

  const handleSuccess = () => {
    toast({
      title: "Success",
      description: "Expense created successfully",
      variant: "success",
    });
    navigate('/expenses');
  };

  return (
    <div className="container mx-auto px-4 py-8 max-w-4xl">
      <h1 className="text-3xl font-bold mb-6">Create New Expense</h1>
      <ExpenseForm onSuccess={handleSuccess} />
    </div>
  );
};

export default CreateExpense;

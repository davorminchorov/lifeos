import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useNavigate } from 'react-router-dom';
import ExpenseForm from '../../components/expenses/ExpenseForm';
import { useToast } from '../../ui/Toast';
const CreateExpense = () => {
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
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: [_jsx("h1", { className: "text-3xl font-bold mb-6", children: "Create New Expense" }), _jsx(ExpenseForm, { onSuccess: handleSuccess })] }));
};
export default CreateExpense;

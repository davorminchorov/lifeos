import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { formatCurrency, formatDate } from '../../utils/format';
import { Button } from '../../ui/Button/Button';
import { Card } from '../../ui/Card';
import { useToast } from '../../ui/Toast';
import { useExpenseDetail, useDeleteExpense } from '../../queries/expenseQueries';
import { Edit, Trash, ArrowLeft } from 'lucide-react';
const ExpenseDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { toast } = useToast();
    // Use our React Query hook for fetching expense
    const { data: expense, isLoading, error } = useExpenseDetail(id);
    // Use mutation for deleting expense
    const deleteExpenseMutation = useDeleteExpense();
    const [isDeleting, setIsDeleting] = useState(false);
    // Handle navigation to edit page
    const handleEdit = () => {
        navigate(`/expenses/${id}/edit`);
    };
    // Handle deletion with confirmation
    const handleDelete = async () => {
        if (!window.confirm('Are you sure you want to delete this expense?')) {
            return;
        }
        setIsDeleting(true);
        deleteExpenseMutation.mutate(id, {
            onSuccess: () => {
                toast({
                    title: "Success",
                    description: "Expense deleted successfully",
                });
                navigate('/expenses');
            },
            onError: (err) => {
                toast({
                    title: "Error",
                    description: "Failed to delete expense",
                    variant: "destructive",
                });
                console.error(err);
                setIsDeleting(false);
            }
        });
    };
    const formatPaymentMethod = (method) => {
        if (!method)
            return '-';
        return method.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    };
    const getCategoryStyle = (category) => {
        if (!category) {
            return {
                backgroundColor: '#e5e7eb',
                color: '#374151'
            };
        }
        return {
            backgroundColor: category.color || '#e5e7eb',
            color: getContrastColor(category.color || '#e5e7eb')
        };
    };
    // Helper function to determine text color based on background color
    const getContrastColor = (hexColor) => {
        // Convert hex to RGB
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);
        // Calculate luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        // Return black or white based on luminance
        return luminance > 0.5 ? '#000000' : '#ffffff';
    };
    if (isLoading) {
        return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: [_jsx("div", { className: "flex justify-between items-center mb-6", children: _jsx("h1", { className: "text-3xl font-bold animate-pulse bg-gray-200 h-10 w-64 rounded" }) }), _jsx(Card, { className: "border border-gray-200 shadow-sm", children: _jsxs("div", { className: "animate-pulse p-6 space-y-4", children: [_jsx("div", { className: "h-8 bg-gray-200 rounded w-1/4 mb-4" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-1/2" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-1/3" }), _jsx("div", { className: "h-24 bg-gray-200 rounded w-full" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-1/4" }), _jsx("div", { className: "h-8 bg-gray-200 rounded w-1/2" })] }) })] }));
    }
    if (error || !expense) {
        return (_jsx("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: _jsx(Card, { className: "border border-gray-200 shadow-sm p-6", children: _jsxs("div", { className: "text-center py-10", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-16 w-16 text-red-500 mx-auto mb-4", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" }) }), _jsx("p", { className: "text-xl font-medium text-gray-900 mb-2", children: "Failed to load expense" }), _jsx("p", { className: "text-gray-600 mb-6", children: (error === null || error === void 0 ? void 0 : error.message) || 'The expense could not be found' }), _jsxs("div", { className: "flex justify-center space-x-4", children: [_jsx(Button, { onClick: () => navigate('/expenses'), variant: "outlined", children: "Back to Expenses" }), _jsx(Button, { onClick: () => window.location.reload(), children: "Try Again" })] })] }) }) }));
    }
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: [_jsxs("div", { className: "flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4", children: [_jsxs("div", { children: [_jsxs(Link, { to: "/expenses", className: "text-sm text-indigo-600 hover:text-indigo-800 flex items-center mb-2", children: [_jsx(ArrowLeft, { className: "h-4 w-4 mr-1" }), "Back to expenses"] }), _jsx("h1", { className: "text-3xl font-bold", children: expense.title })] }), _jsxs("div", { className: "flex space-x-3", children: [_jsx(Button, { variant: "outlined", onClick: handleEdit, icon: _jsx(Edit, { className: "h-4 w-4 mr-2" }), children: "Edit Expense" }), _jsx(Button, { variant: "outlined", className: "text-error border-error hover:bg-error-container/10", onClick: handleDelete, disabled: isDeleting, icon: _jsx(Trash, { className: "h-4 w-4 mr-2" }), children: isDeleting ? 'Deleting...' : 'Delete Expense' })] })] }), _jsx(Card, { className: "border border-gray-200 shadow-sm mb-6", children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6 p-6", children: [_jsxs("div", { children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Amount" }), _jsx("p", { className: "text-2xl font-bold text-gray-900", children: formatCurrency(expense.amount, expense.currency) })] }), _jsxs("div", { children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Date" }), _jsx("p", { className: "text-gray-900", children: formatDate(expense.date) })] }), _jsxs("div", { children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Category" }), expense.category ? (_jsx("span", { className: "inline-flex px-2.5 py-1 text-sm font-medium rounded-full", style: getCategoryStyle(expense.category), children: expense.category.name })) : (_jsx("span", { className: "inline-flex px-2.5 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800", children: "Uncategorized" }))] }), _jsxs("div", { children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Payment Method" }), _jsx("p", { className: "text-gray-900", children: formatPaymentMethod(expense.payment_method) })] }), expense.description && (_jsxs("div", { className: "col-span-1 md:col-span-2", children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Description" }), _jsx("p", { className: "text-gray-900 whitespace-pre-line", children: expense.description })] })), expense.receipt_url && (_jsxs("div", { className: "col-span-1 md:col-span-2", children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Receipt" }), _jsxs("a", { href: expense.receipt_url, target: "_blank", rel: "noopener noreferrer", className: "inline-flex items-center text-indigo-600 hover:text-indigo-800", children: [_jsx("svg", { xmlns: "http://www.w3.org/2000/svg", className: "h-5 w-5 mr-1", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: _jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: 2, d: "M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" }) }), "View Receipt"] })] }))] }) }), _jsx(Card, { className: "border border-gray-200 shadow-sm", children: _jsxs("div", { className: "grid grid-cols-1 md:grid-cols-2 gap-6 p-6", children: [_jsxs("div", { children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Created At" }), _jsx("p", { className: "text-gray-900", children: formatDate(expense.created_at) })] }), _jsxs("div", { children: [_jsx("h2", { className: "font-medium text-gray-500 text-sm mb-1", children: "Last Updated" }), _jsx("p", { className: "text-gray-900", children: formatDate(expense.updated_at) })] })] }) })] }));
};
export default ExpenseDetail;

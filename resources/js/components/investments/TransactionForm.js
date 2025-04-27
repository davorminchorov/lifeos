import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Button } from '../../ui/Button';
import { Card, CardContent, CardFooter } from '../../ui/Card';
import { ArrowDownCircle, ArrowUpCircle, PiggyBank, Banknote, AlertCircle } from 'lucide-react';
const transactionTypes = [
    {
        value: 'deposit',
        label: 'Deposit',
        description: 'Add funds to the investment',
        icon: ArrowDownCircle,
        color: 'text-green-600 bg-green-50'
    },
    {
        value: 'withdrawal',
        label: 'Withdrawal',
        description: 'Remove funds from the investment',
        icon: ArrowUpCircle,
        color: 'text-red-600 bg-red-50'
    },
    {
        value: 'dividend',
        label: 'Dividend',
        description: 'Record a dividend payment',
        icon: PiggyBank,
        color: 'text-blue-600 bg-blue-50'
    },
    {
        value: 'interest',
        label: 'Interest',
        description: 'Record interest earned',
        icon: Banknote,
        color: 'text-purple-600 bg-purple-50'
    },
    {
        value: 'fee',
        label: 'Fee',
        description: 'Record fees or expenses',
        icon: AlertCircle,
        color: 'text-orange-600 bg-orange-50'
    }
];
const TransactionForm = ({ onSubmit, onCancel, onSuccess, initialData, investmentId }) => {
    const [selectedType, setSelectedType] = useState((initialData === null || initialData === void 0 ? void 0 : initialData.type) || 'deposit');
    const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
        defaultValues: {
            type: (initialData === null || initialData === void 0 ? void 0 : initialData.type) || 'deposit',
            amount: (initialData === null || initialData === void 0 ? void 0 : initialData.amount) || undefined,
            date: (initialData === null || initialData === void 0 ? void 0 : initialData.date) || new Date().toISOString().split('T')[0],
            notes: (initialData === null || initialData === void 0 ? void 0 : initialData.notes) || ''
        }
    });
    const submitHandler = (data) => {
        onSubmit(Object.assign(Object.assign({}, data), { type: data.type, amount: Number(data.amount) }));
        if (onSuccess) {
            onSuccess();
        }
    };
    return (_jsx(Card, { className: "bg-white shadow-md rounded-xl overflow-hidden border border-gray-200", children: _jsxs("form", { onSubmit: handleSubmit(submitHandler), children: [_jsx(CardContent, { className: "p-6", children: _jsxs("div", { className: "space-y-6", children: [_jsxs("div", { children: [_jsx("label", { className: "block text-sm font-medium text-gray-700 mb-2", children: "Transaction Type" }), _jsx("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-3", children: transactionTypes.map((type) => {
                                            const Icon = type.icon;
                                            return (_jsxs("div", { className: "relative", children: [_jsx("input", Object.assign({ type: "radio", id: `type-${type.value}`, value: type.value, className: "sr-only" }, register('type'), { onChange: () => setSelectedType(type.value), checked: selectedType === type.value })), _jsx("label", { htmlFor: `type-${type.value}`, className: `block p-4 rounded-lg border-2 ${selectedType === type.value
                                                            ? 'border-indigo-600 ring-2 ring-indigo-200'
                                                            : 'border-gray-200 hover:border-gray-300'} cursor-pointer transition-all`, children: _jsxs("div", { className: "flex items-center", children: [_jsx("div", { className: `p-2 rounded-full ${type.color.split(' ')[1]} mr-3`, children: _jsx(Icon, { className: `h-5 w-5 ${type.color.split(' ')[0]}` }) }), _jsxs("div", { children: [_jsx("span", { className: "block text-sm font-medium text-gray-900", children: type.label }), _jsx("span", { className: "block text-xs text-gray-500 mt-0.5", children: type.description })] })] }) })] }, type.value));
                                        }) }), errors.type && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: errors.type.message || 'Please select a transaction type' }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "amount", className: "block text-sm font-medium text-gray-700 mb-1", children: "Amount" }), _jsxs("div", { className: "relative rounded-md shadow-sm", children: [_jsx("div", { className: "absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none", children: _jsx("span", { className: "text-gray-500 sm:text-sm", children: "$" }) }), _jsx("input", Object.assign({ type: "number", id: "amount", step: "0.01", min: "0.01", placeholder: "0.00", className: `block w-full pl-8 pr-12 py-3 border ${errors.amount ? 'border-red-300' : 'border-gray-300'} rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm` }, register('amount', {
                                                required: 'Amount is required',
                                                min: { value: 0.01, message: 'Amount must be greater than 0' }
                                            }))), _jsx("div", { className: "absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none", children: _jsx("span", { className: "text-gray-500 sm:text-sm", children: "USD" }) })] }), errors.amount && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: errors.amount.message || 'Please enter a valid amount' }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "date", className: "block text-sm font-medium text-gray-700 mb-1", children: "Date" }), _jsx("input", Object.assign({ type: "date", id: "date", className: `block w-full py-3 px-4 border ${errors.date ? 'border-red-300' : 'border-gray-300'} rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm` }, register('date', { required: 'Date is required' }))), errors.date && (_jsx("p", { className: "mt-1 text-sm text-red-600", children: errors.date.message || 'Please select a date' }))] }), _jsxs("div", { children: [_jsx("label", { htmlFor: "notes", className: "block text-sm font-medium text-gray-700 mb-1", children: "Notes (Optional)" }), _jsx("textarea", Object.assign({ id: "notes", rows: 3, className: "block w-full py-3 px-4 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm", placeholder: "Add any additional information about this transaction" }, register('notes')))] })] }) }), _jsxs(CardFooter, { className: "bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100", children: [_jsx(Button, { type: "button", variant: "outlined", onClick: onCancel, disabled: isSubmitting, children: "Cancel" }), _jsx(Button, { type: "submit", disabled: isSubmitting, className: "bg-indigo-600 hover:bg-indigo-700 text-white", children: isSubmitting ? 'Saving...' : 'Save Transaction' })] })] }) }));
};
export default TransactionForm;

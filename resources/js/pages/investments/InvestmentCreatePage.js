import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useNavigate } from 'react-router-dom';
import { Card, CardHeader, CardTitle, CardContent } from '../../ui/Card';
import InvestmentForm from '../../components/investments/InvestmentForm';
import { Button } from '../../ui/Button';
import { ArrowLeft } from 'lucide-react';
const InvestmentCreatePage = () => {
    const navigate = useNavigate();
    return (_jsxs("div", { className: "max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8", children: [_jsxs("div", { className: "flex items-center justify-between mb-8", children: [_jsx("h1", { className: "text-3xl font-bold tracking-tight text-gray-900", children: "Add New Investment" }), _jsxs(Button, { onClick: () => navigate('/investments'), variant: "outline", className: "flex items-center gap-2", children: [_jsx(ArrowLeft, { className: "h-4 w-4" }), "Back to Investments"] })] }), _jsxs(Card, { className: "bg-white shadow-md rounded-xl overflow-hidden", children: [_jsx(CardHeader, { className: "bg-gray-50 border-b border-gray-100 px-6 py-4", children: _jsx(CardTitle, { className: "text-xl font-semibold text-gray-800", children: "Investment Details" }) }), _jsx(CardContent, { className: "p-6", children: _jsx(InvestmentForm, { isEditing: false }) })] })] }));
};
export default InvestmentCreatePage;

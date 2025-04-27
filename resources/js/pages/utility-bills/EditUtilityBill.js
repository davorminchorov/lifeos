import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import UtilityBillForm from '../../components/utility-bills/UtilityBillForm';
import { Card } from '../../ui/Card';
import { useToast } from '../../ui/Toast';
const EditUtilityBill = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { toast } = useToast();
    const [utilityBill, setUtilityBill] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    useEffect(() => {
        const fetchUtilityBill = async () => {
            setLoading(true);
            try {
                const response = await axios.get(`/api/utility-bills/${id}`);
                setUtilityBill(response.data);
                setError(null);
            }
            catch (err) {
                setError('Failed to load utility bill data');
                console.error(err);
                toast({
                    title: "Error",
                    description: "Failed to load utility bill data",
                    variant: "destructive",
                });
            }
            finally {
                setLoading(false);
            }
        };
        fetchUtilityBill();
    }, [id]);
    if (loading) {
        return (_jsx("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: _jsxs("div", { className: "animate-pulse space-y-4", children: [_jsx("div", { className: "h-12 bg-gray-200 rounded w-1/3" }), _jsx("div", { className: "h-64 bg-gray-200 rounded w-full" })] }) }));
    }
    if (error || !utilityBill) {
        return (_jsx("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: _jsxs(Card, { className: "p-6 border border-red-200 bg-red-50 text-red-700", children: [_jsx("p", { className: "font-medium text-lg mb-2", children: "Error Loading Utility Bill" }), _jsx("p", { children: error || 'The utility bill could not be found' }), _jsx("button", { onClick: () => navigate('/utility-bills'), className: "mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700", children: "Back to Utility Bills" })] }) }));
    }
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: [_jsx("h1", { className: "text-3xl font-bold mb-6", children: "Edit Utility Bill" }), _jsx(UtilityBillForm, { initialData: utilityBill, isEditing: true })] }));
};
export default EditUtilityBill;

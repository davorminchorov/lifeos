import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useNavigate } from 'react-router-dom';
import UtilityBillForm from '../../components/utility-bills/UtilityBillForm';
import { useToast } from '../../ui/Toast';
const CreateUtilityBill = () => {
    const navigate = useNavigate();
    const { toast } = useToast();
    return (_jsxs("div", { className: "container mx-auto px-4 py-8 max-w-4xl", children: [_jsx("h1", { className: "text-3xl font-bold mb-6", children: "Create New Utility Bill" }), _jsx(UtilityBillForm, {})] }));
};
export default CreateUtilityBill;

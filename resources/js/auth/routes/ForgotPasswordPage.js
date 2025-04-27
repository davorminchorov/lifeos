import { jsx as _jsx } from "react/jsx-runtime";
import { ForgotPasswordForm } from '../components/ForgotPasswordForm';
import { useNavigate } from 'react-router-dom';
export function ForgotPasswordPage() {
    const navigate = useNavigate();
    const handleSuccess = () => {
        // No need to navigate away as the component handles its own success state
    };
    return _jsx(ForgotPasswordForm, { onSuccess: handleSuccess });
}

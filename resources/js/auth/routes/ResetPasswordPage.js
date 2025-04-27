import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { ResetPasswordForm } from '../components/ResetPasswordForm';
import { useNavigate, useSearchParams } from 'react-router-dom';
export function ResetPasswordPage() {
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const token = searchParams.get('token') || '';
    const email = searchParams.get('email') || '';
    const handleSuccess = () => {
        // No need to navigate away as the component handles its own success state
    };
    if (!token || !email) {
        return (_jsxs("div", { className: "p-6 bg-error-container text-on-error-container rounded-md", children: [_jsx("h2", { className: "text-title-large font-bold mb-4", children: "Invalid password reset link" }), _jsx("p", { className: "mb-4", children: "The password reset link is invalid or has expired." }), _jsx("button", { onClick: () => navigate('/auth/login'), className: "px-4 py-2 bg-primary text-on-primary rounded-md", children: "Return to login" })] }));
    }
    return _jsx(ResetPasswordForm, { token: token, email: email, onSuccess: handleSuccess });
}

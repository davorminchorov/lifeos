import { jsx as _jsx } from "react/jsx-runtime";
import { LoginForm } from '../components/LoginForm';
import { useNavigate } from 'react-router-dom';
export function LoginPage() {
    const navigate = useNavigate();
    const handleLoginSuccess = () => {
        navigate('/dashboard');
    };
    return _jsx(LoginForm, { onSuccess: handleLoginSuccess });
}

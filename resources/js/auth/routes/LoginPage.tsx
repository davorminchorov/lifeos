import React from 'react';
import { LoginForm } from '../components/LoginForm';
import { useNavigate } from 'react-router-dom';

export function LoginPage() {
  const navigate = useNavigate();

  const handleLoginSuccess = () => {
    navigate('/dashboard');
  };

  return <LoginForm onSuccess={handleLoginSuccess} />;
}

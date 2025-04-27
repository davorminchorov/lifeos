import React from 'react';
import { LoginForm } from '../components/LoginForm';
import { useNavigate } from 'react-router-dom';

export function LoginPage() {
  const navigate = useNavigate();

  const handleLoginSuccess = () => {
    navigate('/dashboard');
  };

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-surface-variant">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <h1 className="text-display-small text-primary font-brand mb-2">LifeOS</h1>
          <p className="text-on-surface-variant">Manage your life in one place</p>
        </div>

        <LoginForm onSuccess={handleLoginSuccess} />
      </div>
    </div>
  );
}

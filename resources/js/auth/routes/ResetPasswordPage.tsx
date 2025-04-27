import React from 'react';
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
    return (
      <div className="p-6 bg-error-container text-on-error-container rounded-md">
        <h2 className="text-title-large font-bold mb-4">Invalid password reset link</h2>
        <p className="mb-4">The password reset link is invalid or has expired.</p>
        <button
          onClick={() => navigate('/auth/login')}
          className="px-4 py-2 bg-primary text-on-primary rounded-md"
        >
          Return to login
        </button>
      </div>
    );
  }

  return <ResetPasswordForm token={token} email={email} onSuccess={handleSuccess} />;
}

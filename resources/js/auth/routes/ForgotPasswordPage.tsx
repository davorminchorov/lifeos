import React from 'react';
import { ForgotPasswordForm } from '../components/ForgotPasswordForm';
import { useNavigate } from 'react-router-dom';
import { useTheme } from '../../ui/ThemeProvider';

export function ForgotPasswordPage() {
  const navigate = useNavigate();
  const { isDark } = useTheme();

  const handleSuccess = () => {
    // No need to navigate away as the component handles its own success state
  };

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-background">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="flex justify-center mb-4">
            <div className="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-2xl font-bold text-on-primary">
              L
            </div>
          </div>
          <h1 className="text-display-small text-primary font-brand mb-2">LifeOS</h1>
          <p className="text-on-surface-variant">Manage your life in one place</p>
        </div>

        <ForgotPasswordForm onSuccess={handleSuccess} />

        <div className="mt-8 text-center text-sm text-on-surface-variant">
          Trouble signing in? Contact your administrator
        </div>

        <div className="mt-4 text-center text-xs text-on-surface-variant">
          © {new Date().getFullYear()} LifeOS. All rights reserved.
        </div>
      </div>
    </div>
  );
}

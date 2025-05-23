import React, { useState, useEffect } from 'react';
import { LoginForm } from '../../components/auth/LoginForm';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../store/authContext';
import { useTheme } from '../../ui/ThemeProvider';

export function Login() {
  const { login, isAuthenticated, isLoading: authLoading, error: authError } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | undefined>();
  const navigate = useNavigate();
  const location = useLocation();
  const { isDark } = useTheme();

  // Get the intended destination from location state, or default to dashboard
  const from = (location.state as { from?: { pathname: string } })?.from?.pathname || '/dashboard';

  // Redirect to intended destination if already authenticated
  useEffect(() => {
    if (isAuthenticated && !authLoading) {
      navigate(from, { replace: true });
    }
  }, [isAuthenticated, authLoading, navigate, from]);

  // Set error from auth store if available
  useEffect(() => {
    if (authError) {
      setError(authError);
    }
  }, [authError]);

  const handleLogin = async (data: { email: string; password: string; remember: boolean }) => {
    setIsLoading(true);
    setError(undefined);

    try {
      // Use the login function from our auth hook
      await login(data.email, data.password, data.remember);
      // Navigation will happen in the useEffect above when auth state changes
    } catch (err: any) {
      setError(
        err.response?.data?.message ||
        err.response?.data?.error ||
        "We couldn't sign you in. Please check your credentials and try again."
      );
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-background py-12 px-4 sm:px-6 lg:px-8">
      {/* Material Design App Bar for Login */}
      <div className="fixed top-0 left-0 right-0 bg-surface shadow-elevation-1 h-16 flex items-center px-4 sm:px-6 lg:px-8 z-10">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-on-primary text-lg font-medium">
            L
          </div>
          <h1 className="text-xl font-medium text-on-surface">LifeOS</h1>
        </div>
      </div>

      <div className="w-full max-w-md">
        {/* Logo and Headline */}
        <div className="flex flex-col items-center mb-12">
          <div className="w-20 h-20 rounded-full bg-primary flex items-center justify-center text-on-primary text-3xl font-medium mb-4 shadow-elevation-2">
            L
          </div>
          <h1 className="text-3xl font-medium text-on-surface mb-2">LifeOS</h1>
          <p className="text-on-surface-variant text-center">
            Manage your life in one place
          </p>
        </div>

        {/* Login Card with Material Design elevation */}
        <div className="mb-8 bg-surface rounded-xl shadow-elevation-2 transition-all hover:shadow-elevation-3">
          <LoginForm
            onSubmit={handleLogin}
            isLoading={isLoading}
            error={error}
          />
        </div>

        {/* Footer */}
        <div className="mt-8 text-center text-sm text-on-surface-variant">
          <p>
            Trouble signing in? Contact your administrator
          </p>
          <p className="mt-4 text-on-surface-variant/70">
            © {new Date().getFullYear()} LifeOS. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  );
}

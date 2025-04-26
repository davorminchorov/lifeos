import React, { useState } from 'react';
import { LoginForm } from '../../components/auth/LoginForm';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

export function Login() {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | undefined>();
  const navigate = useNavigate();

  const handleLogin = async (data: { email: string; password: string; remember: boolean }) => {
    setIsLoading(true);
    setError(undefined);

    try {
      // You would use your actual authentication endpoint here
      const response = await axios.post('/api/login', {
        email: data.email,
        password: data.password,
        remember: data.remember
      });

      // Store token or session data
      localStorage.setItem('token', response.data.token);

      // Redirect to dashboard
      navigate('/dashboard');
    } catch (err: any) {
      setError(
        err.response?.data?.message ||
        "We couldn't sign you in. Please check your credentials and try again."
      );
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      {/* Material Design App Bar for Login */}
      <div className="fixed top-0 left-0 right-0 bg-white shadow-elevation-1 h-16 flex items-center px-4 sm:px-6 lg:px-8 z-10">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white text-lg font-medium">
            L
          </div>
          <h1 className="text-xl font-medium text-slate-800">LifeOS</h1>
        </div>
      </div>

      <div className="w-full max-w-md">
        {/* Logo and Headline */}
        <div className="flex flex-col items-center mb-12">
          <div className="w-20 h-20 rounded-full bg-teal-600 flex items-center justify-center text-white text-3xl font-medium mb-4">
            L
          </div>
          <h1 className="text-3xl font-medium text-slate-800 mb-2">LifeOS</h1>
          <p className="text-slate-600 text-center">
            Manage your life in one place
          </p>
        </div>

        {/* Login Card with Material Design elevation */}
        <div className="mb-8 transition-all hover:shadow-elevation-2">
          <LoginForm
            onSubmit={handleLogin}
            isLoading={isLoading}
            error={error}
          />
        </div>

        {/* Footer */}
        <div className="mt-8 text-center text-sm text-slate-500">
          <p>
            Trouble signing in? Contact your administrator
          </p>
          <p className="mt-4 text-slate-400">
            © {new Date().getFullYear()} LifeOS. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  );
}

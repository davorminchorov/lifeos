import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import ThemeToggle from '../../ui/ThemeToggle';
import { useTheme } from '../../ui/ThemeProvider';

interface ForgotPasswordFormProps {
  onSuccess?: () => void;
}

export function ForgotPasswordForm({ onSuccess }: ForgotPasswordFormProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [email, setEmail] = useState('');
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const { isDark } = useTheme();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    // Validate email
    if (!email) {
      setError('Email is required');
      setIsLoading(false);
      return;
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      setError('Email is invalid');
      setIsLoading(false);
      return;
    }

    try {
      // TODO: Replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      setSuccess(true);
      if (onSuccess) {
        onSuccess();
      }
    } catch (err: any) {
      setError('Password reset request failed. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  // Custom input style classes based on theme
  const containerClass = isDark ? "bg-gray-900 text-white" : "bg-surface";
  const inputClass = isDark
    ? "h-14 bg-blue-50/10 border border-transparent rounded-md text-white"
    : "h-14 bg-blue-50 border border-transparent rounded-md";
  const labelClass = isDark ? "text-gray-300" : "text-on-surface-variant";
  const buttonClass = isDark
    ? "bg-teal-500 hover:bg-teal-600 text-white"
    : "bg-primary hover:bg-primary-dark text-on-primary";
  const textClass = isDark ? "text-gray-300" : "text-on-surface-variant";

  if (success) {
    return (
      <div className={`w-full max-w-md p-6 ${containerClass} rounded-xl shadow-elevation-2`}>
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-title-large font-bold">Check your email</h2>
          <ThemeToggle />
        </div>

        <div className="mb-6 p-3 rounded-md bg-success-container text-on-success-container text-sm">
          We've sent a password reset link to <strong>{email}</strong>. Please check your inbox.
        </div>

        <p className={`text-sm mb-6 ${textClass}`}>
          If you don't see it, check your spam folder. The link will expire in 60 minutes.
        </p>

        <Link to="/auth/login">
          <button
            type="button"
            className={`w-full flex justify-center items-center h-12 px-4 border border-transparent rounded-md shadow-sm text-body-medium font-medium ${buttonClass} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition-colors`}
          >
            Return to login
          </button>
        </Link>
      </div>
    );
  }

  return (
    <div className={`w-full max-w-md p-6 ${containerClass} rounded-xl shadow-elevation-2`}>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-title-large font-bold">Forgot password?</h2>
        <ThemeToggle />
      </div>

      <p className={`mb-6 ${textClass}`}>
        Enter your email address and we'll send you a link to reset your password.
      </p>

      {error && (
        <div className="mb-6 p-3 rounded-md bg-error-container text-on-error-container text-sm">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="space-y-1">
          <label htmlFor="email" className={`block text-sm font-medium ${labelClass}`}>
            Email
          </label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className={`${inputClass} w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary`}
            placeholder="example@example.com"
          />
        </div>

        <button
          type="submit"
          className={`w-full flex justify-center items-center h-12 px-4 border border-transparent rounded-md shadow-sm text-body-medium font-medium ${buttonClass} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition-colors`}
          disabled={isLoading}
        >
          {isLoading ? (
            <svg
              className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
              ></circle>
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
          ) : null}
          Send reset link
        </button>

        <div className="text-center mt-6">
          <Link
            to="/auth/login"
            className={`font-medium ${isDark ? 'text-teal-400 hover:text-teal-300' : 'text-primary hover:text-primary-dark'} text-sm`}
          >
            Back to login
          </Link>
        </div>
      </form>
    </div>
  );
}

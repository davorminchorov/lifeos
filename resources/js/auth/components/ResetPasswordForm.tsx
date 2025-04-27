import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import ThemeToggle from '../../ui/ThemeToggle';
import { useTheme } from '../../ui/ThemeProvider';

interface ResetPasswordFormProps {
  token: string;
  email: string;
  onSuccess?: () => void;
}

export function ResetPasswordForm({ token, email, onSuccess }: ResetPasswordFormProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const navigate = useNavigate();
  const { isDark } = useTheme();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    // Validate password
    if (!password) {
      setError('Password is required');
      setIsLoading(false);
      return;
    }

    if (password.length < 8) {
      setError('Password must be at least 8 characters');
      setIsLoading(false);
      return;
    }

    if (password !== passwordConfirmation) {
      setError('Passwords do not match');
      setIsLoading(false);
      return;
    }

    try {
      // TODO: Replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000));

      if (onSuccess) {
        onSuccess();
      } else {
        navigate('/auth/login', { replace: true });
      }
    } catch (err: any) {
      setError('Password reset failed. Please try again.');
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

  return (
    <div className={`w-full max-w-md p-6 ${containerClass} rounded-xl shadow-elevation-2`}>
      <div className="flex justify-between items-center mb-4">
        <h2 className="text-title-large font-bold">Reset your password</h2>
        <ThemeToggle />
      </div>

      <p className={`mb-6 ${textClass}`}>
        Create a new password for <strong>{email}</strong>
      </p>

      {error && (
        <div className="mb-6 p-3 rounded-md bg-error-container text-on-error-container text-sm">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="space-y-1">
          <label htmlFor="password" className={`block text-sm font-medium ${labelClass}`}>
            New Password
          </label>
          <div className="relative">
            <input
              id="password"
              type={showPassword ? "text" : "password"}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className={`${inputClass} w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary`}
              placeholder="Enter your new password"
            />
            <button
              type="button"
              className="absolute inset-y-0 right-0 pr-3 flex items-center"
              onClick={() => setShowPassword(!showPassword)}
            >
              {showPassword ? (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
              ) : (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              )}
            </button>
          </div>
        </div>

        <div className="space-y-1">
          <label htmlFor="password_confirmation" className={`block text-sm font-medium ${labelClass}`}>
            Confirm Password
          </label>
          <div className="relative">
            <input
              id="password_confirmation"
              type={showConfirmPassword ? "text" : "password"}
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              className={`${inputClass} w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary`}
              placeholder="Confirm your new password"
            />
            <button
              type="button"
              className="absolute inset-y-0 right-0 pr-3 flex items-center"
              onClick={() => setShowConfirmPassword(!showConfirmPassword)}
            >
              {showConfirmPassword ? (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
              ) : (
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" className="w-5 h-5">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              )}
            </button>
          </div>
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
          Reset Password
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

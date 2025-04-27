import React from 'react';
import { Outlet, Link } from 'react-router-dom';
import { useTheme } from '../../ui/ThemeProvider';

export function AuthLayout() {
  const { isDark } = useTheme();

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

        <Outlet />

        <div className="mt-8 text-center text-sm text-on-surface-variant">
          <div className="flex justify-center space-x-4">
            <Link to="/auth/login" className="text-primary hover:underline">Login</Link>
            <Link to="/auth/forgot-password" className="text-primary hover:underline">Forgot Password</Link>
          </div>
        </div>

        <div className="mt-4 text-center text-xs text-on-surface-variant">
          © {new Date().getFullYear()} LifeOS. All rights reserved.
        </div>
      </div>
    </div>
  );
}

import React, { useState } from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { Eye, EyeOff, Lock, Mail } from 'lucide-react';
import { Button } from '../ui/Button';

// Utility to merge tailwind classes
const cn = (...inputs: (string | undefined | null | false)[]) => {
  return twMerge(clsx(inputs));
};

interface LoginFormProps {
  onSubmit: (data: { email: string; password: string; remember: boolean }) => void;
  isLoading?: boolean;
  error?: string;
}

export function LoginForm({ onSubmit, isLoading = false, error }: LoginFormProps) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  // Material Design states for input fields
  const [emailFocused, setEmailFocused] = useState(false);
  const [passwordFocused, setPasswordFocused] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ email, password, remember });
  };

  return (
    <div className="w-full max-w-md mx-auto">
      <div className="bg-white p-6 sm:p-8 rounded-lg shadow-elevation-1">
        <div className="mb-8 text-center">
          <h1 className="text-2xl font-medium text-slate-800 mb-2">Welcome back</h1>
          <p className="text-slate-600">Sign in to your LifeOS account</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-100 rounded-md text-red-600 text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Material Design Outlined Text Field - Email */}
          <div className="relative">
            <div
              className={cn(
                "border rounded-md transition-colors relative",
                emailFocused || email ? "border-teal-600" : "border-slate-300",
                "h-14"
              )}
            >
              <label
                htmlFor="email"
                className={cn(
                  "absolute left-9 transition-all duration-150 pointer-events-none",
                  emailFocused || email
                    ? "text-xs text-teal-600 top-2"
                    : "text-slate-500 text-base top-1/2 -translate-y-1/2"
                )}
              >
                Email address
              </label>
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center">
                <Mail
                  size={18}
                  className={cn(
                    "text-slate-400",
                    emailFocused || email ? "text-teal-600" : ""
                  )}
                />
              </div>
              <input
                id="email"
                name="email"
                type="email"
                autoComplete="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                onFocus={() => setEmailFocused(true)}
                onBlur={() => setEmailFocused(false)}
                className={cn(
                  "block w-full h-full pl-9 pr-3 pt-6 pb-2 rounded-md",
                  "text-slate-800 bg-transparent",
                  "focus:outline-none placeholder:text-transparent"
                )}
                placeholder="Email address"
                disabled={isLoading}
              />
            </div>
          </div>

          {/* Material Design Outlined Text Field - Password */}
          <div className="relative">
            <div
              className={cn(
                "border rounded-md transition-colors relative",
                passwordFocused || password ? "border-teal-600" : "border-slate-300",
                "h-14"
              )}
            >
              <label
                htmlFor="password"
                className={cn(
                  "absolute left-9 transition-all duration-150 pointer-events-none",
                  passwordFocused || password
                    ? "text-xs text-teal-600 top-2"
                    : "text-slate-500 text-base top-1/2 -translate-y-1/2"
                )}
              >
                Password
              </label>
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center">
                <Lock
                  size={18}
                  className={cn(
                    "text-slate-400",
                    passwordFocused || password ? "text-teal-600" : ""
                  )}
                />
              </div>
              <input
                id="password"
                name="password"
                type={showPassword ? "text" : "password"}
                autoComplete="current-password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                onFocus={() => setPasswordFocused(true)}
                onBlur={() => setPasswordFocused(false)}
                className={cn(
                  "block w-full h-full pl-9 pr-10 pt-6 pb-2 rounded-md",
                  "text-slate-800 bg-transparent",
                  "focus:outline-none placeholder:text-transparent"
                )}
                placeholder="Password"
                disabled={isLoading}
              />
              <button
                type="button"
                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                onClick={() => setShowPassword(!showPassword)}
                aria-label={showPassword ? "Hide password" : "Show password"}
              >
                {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            </div>
          </div>

          {/* Material Design Checkbox */}
          <div className="flex items-center">
            <div className="relative inline-flex items-center">
              <input
                id="remember"
                name="remember"
                type="checkbox"
                className="peer sr-only"
                checked={remember}
                onChange={(e) => setRemember(e.target.checked)}
                disabled={isLoading}
              />
              <label
                htmlFor="remember"
                className={cn(
                  "relative cursor-pointer flex items-center justify-center w-5 h-5 border rounded-sm mr-2 transition-colors",
                  remember ? "bg-teal-600 border-teal-600" : "border-slate-400 hover:border-teal-600",
                  "after:content-[''] after:absolute after:hidden peer-checked:after:block",
                  "after:w-1.5 after:h-3 after:border-r-2 after:border-b-2 after:border-white",
                  "after:rotate-45 after:-translate-y-[2px]"
                )}
              ></label>
              <span className="text-sm text-slate-600">
                Remember me
              </span>
            </div>
          </div>

          <div className="pt-4">
            <Button
              type="submit"
              variant="contained"
              isLoading={isLoading}
              fullWidth
            >
              Sign in
            </Button>
          </div>

          {/* Additional help text */}
          <div className="text-center pt-2">
            <button
              type="button"
              className="text-sm text-teal-600 hover:text-teal-700 font-medium"
            >
              Forgot password?
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

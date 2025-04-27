import React, { useState } from 'react';
import { Input } from '../../ui/Input/Input';
import { Button } from '../../ui/Button/Button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '../../ui/Card/Card';

interface LoginFormProps {
  onSuccess?: () => void;
}

export function LoginForm({ onSuccess }: LoginFormProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState<{
    email?: string;
    password?: string;
    general?: string;
  }>({});

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    // Clear previous errors
    setErrors({});

    // Validate form
    let hasErrors = false;
    const newErrors: {
      email?: string;
      password?: string;
      general?: string;
    } = {};

    if (!email) {
      newErrors.email = 'Email is required';
      hasErrors = true;
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Email is invalid';
      hasErrors = true;
    }

    if (!password) {
      newErrors.password = 'Password is required';
      hasErrors = true;
    }

    if (hasErrors) {
      setErrors(newErrors);
      return;
    }

    // Submit form
    try {
      setIsLoading(true);

      // TODO: Replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 1000));

      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      setErrors({
        general: 'Login failed. Please check your credentials and try again.'
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Card className="w-full max-w-md mx-auto">
      <form onSubmit={handleSubmit}>
        <CardHeader>
          <CardTitle>Login to LifeOS</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {errors.general && (
            <div className="p-3 rounded-md bg-error-container text-on-error-container text-sm">
              {errors.general}
            </div>
          )}

          <div className="space-y-2">
            <label htmlFor="email" className="block text-sm font-medium text-on-surface">
              Email
            </label>
            <Input
              id="email"
              type="email"
              placeholder="you@example.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              error={errors.email}
              disabled={isLoading}
            />
          </div>

          <div className="space-y-2">
            <label htmlFor="password" className="block text-sm font-medium text-on-surface">
              Password
            </label>
            <Input
              id="password"
              type="password"
              placeholder="••••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              error={errors.password}
              disabled={isLoading}
            />
          </div>
        </CardContent>

        <CardFooter className="flex justify-between">
          <Button type="submit" isLoading={isLoading}>
            Sign In
          </Button>
        </CardFooter>
      </form>
    </Card>
  );
}

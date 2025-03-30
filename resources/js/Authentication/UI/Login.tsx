import React from 'react';
import { useForm } from '@inertiajs/react';
import Layout from './Layout';
import { Button } from '../../components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../../components/ui/card';
import { Input, Label, FormGroup } from '../../components/ui/input';

// Let's use a simpler approach that avoids TypeScript errors
export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/login');
  }

  return (
    <Layout>
      <div className="max-w-md mx-auto">
        <Card>
          <CardHeader className="space-y-1">
            <CardTitle className="text-2xl text-center">Login</CardTitle>
            <CardDescription className="text-center">
              Enter your credentials to access your account
            </CardDescription>
          </CardHeader>

          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <FormGroup error={errors.email}>
                <Label htmlFor="email" required>
                  Email
                </Label>
                <Input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={e => setData('email', e.target.value)}
                  error={!!errors.email}
                  required
                />
              </FormGroup>

              <FormGroup error={errors.password}>
                <Label htmlFor="password" required>
                  Password
                </Label>
                <Input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={e => setData('password', e.target.value)}
                  error={!!errors.password}
                  required
                />
              </FormGroup>

              <div className="flex items-center">
                <input
                  id="remember"
                  type="checkbox"
                  checked={data.remember}
                  onChange={e => {
                    // @ts-ignore: Inertia's type is too restrictive for this boolean toggle
                    setData('remember', e.target.checked);
                  }}
                  className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                />
                <label htmlFor="remember" className="ml-2 text-sm text-muted-foreground">
                  Remember me
                </label>
              </div>

              <Button
                type="submit"
                className="w-full mt-6"
                disabled={processing}
              >
                {processing ? 'Signing in...' : 'Sign in'}
              </Button>
            </form>
          </CardContent>

          <CardFooter className="flex flex-col">
            <Button variant="link" className="text-sm">
              Forgot your password?
            </Button>
          </CardFooter>
        </Card>
      </div>
    </Layout>
  );
}

import React from 'react';
import { useForm } from '@inertiajs/react';
import Layout from './Layout';
import { Button } from '../../components/ui/button';

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
      <div className="max-w-md mx-auto mt-8 p-6 bg-card rounded-lg shadow-sm border">
        <h2 className="text-2xl font-semibold mb-6 text-center">Login</h2>

        <form onSubmit={submit}>
          <div className="mb-4">
            <label htmlFor="email" className="block text-sm font-medium mb-1">
              Email
            </label>
            <input
              id="email"
              type="email"
              value={data.email}
              onChange={e => setData('email', e.target.value)}
              className="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
              required
            />
            {errors.email && (
              <div className="text-destructive text-sm mt-1">{errors.email}</div>
            )}
          </div>

          <div className="mb-4">
            <label htmlFor="password" className="block text-sm font-medium mb-1">
              Password
            </label>
            <input
              id="password"
              type="password"
              value={data.password}
              onChange={e => setData('password', e.target.value)}
              className="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
              required
            />
            {errors.password && (
              <div className="text-destructive text-sm mt-1">{errors.password}</div>
            )}
          </div>

          <div className="mb-6">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={data.remember}
                onChange={e => setData('remember', e.target.checked)}
                className="mr-2"
              />
              <span className="text-sm">Remember me</span>
            </label>
          </div>

          <Button
            type="submit"
            className="w-full"
            disabled={processing}
          >
            Login
          </Button>
        </form>
      </div>
    </Layout>
  );
}

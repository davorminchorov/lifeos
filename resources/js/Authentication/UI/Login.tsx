import React from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '../../components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../../components/ui/card';
import { Input, Label, FormGroup } from '../../components/ui/input';

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
    <div className="min-h-screen bg-background flex flex-col">
      {/* Header */}
      <header className="border-b border-border py-4 bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center">
            <div className="flex items-center space-x-2">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="text-primary"
              >
                <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/>
                <circle cx="16.5" cy="7.5" r=".5"/>
              </svg>
              <h1 className="text-xl font-bold text-primary">LifeOS</h1>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="flex-1 flex items-center justify-center p-6 bg-slate-50">
        <div className="w-full max-w-md">
          {/* Welcome Section */}
          <div className="text-center mb-8">
            <div className="inline-block p-4 rounded-full bg-primary/10 mb-4">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="32"
                height="32"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="text-primary"
              >
                <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/>
                <circle cx="16.5" cy="7.5" r=".5"/>
              </svg>
            </div>
            <h1 className="text-3xl font-bold text-gray-800 mb-2">Welcome to LifeOS</h1>
            <p className="text-gray-600">Sign in to your account to continue</p>
          </div>

          {/* Login Card */}
          <Card className="border border-gray-200 shadow-lg overflow-hidden">
            <div className="h-2 bg-primary w-full"></div>
            <CardHeader className="pb-2 pt-6">
              <CardTitle className="text-xl text-center font-bold">Sign in</CardTitle>
              <CardDescription className="text-center text-gray-500">
                Enter your credentials to access your dashboard
              </CardDescription>
            </CardHeader>

            <CardContent className="pt-4">
              <form onSubmit={submit} className="space-y-4">
                <FormGroup error={errors.email}>
                  <Label htmlFor="email" required className="text-gray-700 font-medium">
                    Email
                  </Label>
                  <Input
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={e => setData('email', e.target.value)}
                    error={!!errors.email}
                    placeholder="your.email@example.com"
                    required
                    autoFocus
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  />
                  {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                </FormGroup>

                <FormGroup error={errors.password}>
                  <div className="flex justify-between items-center">
                    <Label htmlFor="password" required className="text-gray-700 font-medium">
                      Password
                    </Label>
                    <a href="#" className="text-primary hover:text-primary-dark text-xs">
                      Forgot password?
                    </a>
                  </div>
                  <Input
                    id="password"
                    type="password"
                    value={data.password}
                    onChange={e => setData('password', e.target.value)}
                    error={!!errors.password}
                    required
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  />
                  {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password}</p>}
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
                    className="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                  />
                  <label htmlFor="remember" className="ml-2 text-sm text-gray-600">
                    Remember me
                  </label>
                </div>

                <Button
                  type="submit"
                  className="w-full py-2 px-4 mt-6 bg-primary hover:bg-primary-dark text-white font-bold rounded-md"
                  disabled={processing}
                >
                  {processing ? (
                    <span className="flex items-center justify-center">
                      <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Signing in...
                    </span>
                  ) : 'Sign in'}
                </Button>
              </form>
            </CardContent>
          </Card>

          <p className="text-center text-sm text-gray-600 mt-6">
            Don't have an account? <a href="#" className="text-primary hover:text-primary-dark font-medium">Contact administrator</a>
          </p>
        </div>
      </main>

      {/* Footer */}
      <footer className="py-6 bg-white border-t border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-sm text-gray-500">
              &copy; {new Date().getFullYear()} Davor Minchorov
            </p>
            <div className="flex gap-6 mt-4 md:mt-0">
              <a href="#" className="text-sm text-gray-500 hover:text-primary">Terms</a>
              <a href="#" className="text-sm text-gray-500 hover:text-primary">Privacy</a>
              <a href="#" className="text-sm text-gray-500 hover:text-primary">Help</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}

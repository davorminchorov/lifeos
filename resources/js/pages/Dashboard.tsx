import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import PageContainer, { PageSection, PageGrid } from '../ui/PageContainer';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../ui/Card';
import { Button } from '../ui/Button';

interface DashboardSummary {
  totalSubscriptions: number;
  activeSubscriptions: number;
  upcomingPayments: number;
  monthlyCost: number;
  pendingBills: number;
  upcomingReminders: number;
}

const Dashboard: React.FC = () => {
  const [summary, setSummary] = useState<DashboardSummary>({
    totalSubscriptions: 0,
    activeSubscriptions: 0,
    upcomingPayments: 0,
    monthlyCost: 0,
    pendingBills: 0,
    upcomingReminders: 0
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setLoading(true);
        const response = await axios.get('/api/dashboard/summary');
        setSummary(response.data);
        setError('');
      } catch (err) {
        console.error('Failed to fetch dashboard data', err);
        setError('Failed to load dashboard data. Please try again later.');
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (loading) {
    return (
      <PageContainer title="Dashboard">
        <div className="animate-pulse space-y-4">
          <div className="h-4 bg-surface-variant rounded w-3/4"></div>
          <div className="space-y-2">
            <div className="h-4 bg-surface-variant rounded"></div>
            <div className="h-4 bg-surface-variant rounded w-5/6"></div>
          </div>
        </div>
      </PageContainer>
    );
  }

  return (
    <PageContainer title="Dashboard" subtitle="Welcome to your personal finance dashboard">
      {error && (
        <div className="mb-6 p-4 rounded-md bg-error-container text-on-error-container" role="alert">
          {error}
        </div>
      )}

      <PageSection title="Overview">
        <PageGrid columns={4}>
          {/* Total Subscriptions */}
          <Card>
            <CardHeader>
              <CardTitle>Total Subscriptions</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-primary-container text-on-primary-container shadow-elevation-1">
                  <span className="font-bold text-lg">{summary.totalSubscriptions || '0'}</span>
                </div>
                <div className="ml-4">
                  <span className="text-2xl font-medium">{summary.totalSubscriptions || '0'}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter className="justify-end">
              <Button variant="text" size="sm" asChild>
                <Link to="/subscriptions">View all</Link>
              </Button>
            </CardFooter>
          </Card>

          {/* Active Subscriptions */}
          <Card>
            <CardHeader>
              <CardTitle>Active Subscriptions</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-tertiary-container text-on-tertiary-container shadow-elevation-1">
                  <span className="font-bold text-lg">{summary.activeSubscriptions || '0'}</span>
                </div>
                <div className="ml-4">
                  <span className="text-2xl font-medium">{summary.activeSubscriptions || '0'}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter className="justify-end">
              <Button variant="text" size="sm" asChild>
                <Link to="/subscriptions">View active</Link>
              </Button>
            </CardFooter>
          </Card>

          {/* Pending Bills */}
          <Card>
            <CardHeader>
              <CardTitle>Pending Bills</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-secondary-container text-on-secondary-container shadow-elevation-1">
                  <span className="font-bold text-lg">{summary.pendingBills || '0'}</span>
                </div>
                <div className="ml-4">
                  <span className="text-2xl font-medium">{summary.pendingBills || '0'}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter className="justify-end">
              <Button variant="text" size="sm" asChild>
                <Link to="/utility-bills">View bills</Link>
              </Button>
            </CardFooter>
          </Card>

          {/* Monthly Cost */}
          <Card>
            <CardHeader>
              <CardTitle>Monthly Cost</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center">
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-error-container text-on-error-container shadow-elevation-1">
                  <span className="font-bold text-sm">$</span>
                </div>
                <div className="ml-4">
                  <span className="text-2xl font-medium">${(summary.monthlyCost || 0).toFixed(2)}</span>
                </div>
              </div>
            </CardContent>
            <CardFooter className="justify-end">
              <Button variant="text" size="sm" asChild>
                <Link to="/subscriptions">View breakdown</Link>
              </Button>
            </CardFooter>
          </Card>
        </PageGrid>
      </PageSection>

      <PageSection title="Quick Actions">
        <PageGrid columns={3}>
          <Card>
            <CardHeader>
              <CardTitle>Manage Subscriptions</CardTitle>
              <CardDescription>Keep track of your recurring subscriptions and payments.</CardDescription>
            </CardHeader>
            <CardFooter>
              <Button variant="tonal" asChild>
                <Link to="/subscriptions">Go to Subscriptions</Link>
              </Button>
            </CardFooter>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Utility Bills</CardTitle>
              <CardDescription>Manage your utility bills and set up payment reminders.</CardDescription>
            </CardHeader>
            <CardFooter>
              <Button variant="tonal" asChild>
                <Link to="/utility-bills">Go to Utility Bills</Link>
              </Button>
            </CardFooter>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Track Expenses</CardTitle>
              <CardDescription>Record and categorize your daily expenses.</CardDescription>
            </CardHeader>
            <CardFooter>
              <Button variant="tonal" asChild>
                <Link to="/expenses">Go to Expenses</Link>
              </Button>
            </CardFooter>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Investments</CardTitle>
              <CardDescription>Monitor your investment portfolio and track performance.</CardDescription>
            </CardHeader>
            <CardFooter>
              <Button variant="tonal" asChild>
                <Link to="/investments">Go to Investments</Link>
              </Button>
            </CardFooter>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Job Applications</CardTitle>
              <CardDescription>Track your job applications and interview processes.</CardDescription>
            </CardHeader>
            <CardFooter>
              <Button variant="tonal" asChild>
                <Link to="/job-applications">Go to Job Applications</Link>
              </Button>
            </CardFooter>
          </Card>
        </PageGrid>
      </PageSection>
    </PageContainer>
  );
};

export default Dashboard;

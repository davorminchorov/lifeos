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
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-primary-container text-on-primary-container">
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                  </svg>
                </div>
                <div className="ml-4">
                  <span className="text-display-small font-medium">{summary.totalSubscriptions}</span>
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
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-tertiary-container text-on-tertiary-container">
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div className="ml-4">
                  <span className="text-display-small font-medium">{summary.activeSubscriptions}</span>
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
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-secondary-container text-on-secondary-container">
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div className="ml-4">
                  <span className="text-display-small font-medium">{summary.pendingBills}</span>
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
                <div className="w-12 h-12 flex items-center justify-center rounded-full bg-error-container text-on-error-container">
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div className="ml-4">
                  <span className="text-display-small font-medium">${(summary.monthlyCost || 0).toFixed(2)}</span>
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

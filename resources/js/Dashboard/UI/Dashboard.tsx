import React from 'react';
import { Head } from '@inertiajs/react';
import Layout from '../../Authentication/UI/Layout';
import { Button } from '../../components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../../components/ui/card';

export default function Dashboard() {
  return (
    <>
      <Head title="Dashboard" />
      <Layout>
        <div className="space-y-8">
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl">Welcome to LifeOS</CardTitle>
              <CardDescription>
                Your personal management system. Track your subscriptions, bills, investments, job applications, and expenses all in one place.
              </CardDescription>
            </CardHeader>
          </Card>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Subscriptions Card */}
            <Card variant="module-subscriptions">
              <CardHeader>
                <CardTitle>Subscriptions</CardTitle>
                <CardDescription>Manage your recurring subscriptions</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="h-24 flex items-center justify-center bg-muted rounded-md">
                  <p className="text-muted-foreground">No active subscriptions</p>
                </div>
              </CardContent>
              <CardFooter>
                <Button variant="subscriptions" className="w-full">View Subscriptions</Button>
              </CardFooter>
            </Card>

            {/* Utility Bills Card */}
            <Card variant="module-bills">
              <CardHeader>
                <CardTitle>Utility Bills</CardTitle>
                <CardDescription>Track your recurring bills and payments</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="h-24 flex items-center justify-center bg-muted rounded-md">
                  <p className="text-muted-foreground">No upcoming bills</p>
                </div>
              </CardContent>
              <CardFooter>
                <Button variant="bills" className="w-full">View Bills</Button>
              </CardFooter>
            </Card>

            {/* Investments Card */}
            <Card variant="module-investments">
              <CardHeader>
                <CardTitle>Investments</CardTitle>
                <CardDescription>Monitor your investment portfolio</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="h-24 flex items-center justify-center bg-muted rounded-md">
                  <p className="text-muted-foreground">No active investments</p>
                </div>
              </CardContent>
              <CardFooter>
                <Button variant="investments" className="w-full">View Investments</Button>
              </CardFooter>
            </Card>

            {/* Job Applications Card */}
            <Card variant="module-jobs">
              <CardHeader>
                <CardTitle>Job Applications</CardTitle>
                <CardDescription>Track your job application status</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="h-24 flex items-center justify-center bg-muted rounded-md">
                  <p className="text-muted-foreground">No active applications</p>
                </div>
              </CardContent>
              <CardFooter>
                <Button variant="jobs" className="w-full">View Applications</Button>
              </CardFooter>
            </Card>

            {/* Expenses Card */}
            <Card variant="module-expenses">
              <CardHeader>
                <CardTitle>Expenses</CardTitle>
                <CardDescription>Monitor your spending habits</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="h-24 flex items-center justify-center bg-muted rounded-md">
                  <p className="text-muted-foreground">No recent expenses</p>
                </div>
              </CardContent>
              <CardFooter>
                <Button variant="expenses" className="w-full">View Expenses</Button>
              </CardFooter>
            </Card>
          </div>
        </div>
      </Layout>
    </>
  );
}

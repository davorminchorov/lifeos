import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/Card';

export default function DashboardPage() {
  return (
    <div>
      <h1 className="headline-large mb-6 text-on-surface">Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Summary Cards */}
        <Card>
          <CardHeader>
            <CardTitle>Subscriptions</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-headline-medium text-primary mb-2">$128.45</div>
            <p className="text-on-surface-variant">Monthly spend</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Utility Bills</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-headline-medium text-primary mb-2">$215.30</div>
            <p className="text-on-surface-variant">Monthly average</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Investments</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-headline-medium text-tertiary mb-2">$12,435.86</div>
            <p className="text-on-surface-variant">Total portfolio value</p>
          </CardContent>
        </Card>

        {/* Recent Activity */}
        <Card className="md:col-span-2">
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex justify-between items-center border-b pb-2">
                <div>
                  <div className="font-medium">Netflix</div>
                  <div className="text-sm text-on-surface-variant">Subscription payment</div>
                </div>
                <div className="text-error font-medium">-$15.99</div>
              </div>

              <div className="flex justify-between items-center border-b pb-2">
                <div>
                  <div className="font-medium">Electric Bill</div>
                  <div className="text-sm text-on-surface-variant">Monthly payment</div>
                </div>
                <div className="text-error font-medium">-$89.75</div>
              </div>

              <div className="flex justify-between items-center">
                <div>
                  <div className="font-medium">Vanguard ETF</div>
                  <div className="text-sm text-on-surface-variant">Investment purchase</div>
                </div>
                <div className="text-primary font-medium">+$500.00</div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Job Applications */}
        <Card>
          <CardHeader>
            <CardTitle>Job Applications</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <div className="text-sm font-medium">Active</div>
                <div className="text-sm">3</div>
              </div>
              <div className="flex justify-between items-center">
                <div className="text-sm font-medium">Interviews</div>
                <div className="text-sm">1</div>
              </div>
              <div className="flex justify-between items-center">
                <div className="text-sm font-medium">Offers</div>
                <div className="text-sm">0</div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

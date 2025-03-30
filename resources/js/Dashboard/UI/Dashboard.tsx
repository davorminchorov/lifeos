import React from 'react';
import { Head } from '@inertiajs/react';
import Layout from '../../Authentication/UI/Layout';
import { Button } from '../../components/ui/button';

export default function Dashboard() {
  return (
    <>
      <Head title="Dashboard" />
      <Layout>
        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-2xl font-semibold mb-4">Welcome to LifeOS</h2>
            <p className="text-gray-500">
              Your personal management system. Track your subscriptions, bills, investments, job applications, and expenses all in one place.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Subscriptions Card */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-xl font-medium mb-2">Subscriptions</h3>
              <p className="text-gray-500 mb-4">Manage your recurring subscriptions</p>
              <Button variant="outline" className="w-full">View Subscriptions</Button>
            </div>

            {/* Utility Bills Card */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-xl font-medium mb-2">Utility Bills</h3>
              <p className="text-gray-500 mb-4">Track your recurring bills and payments</p>
              <Button variant="outline" className="w-full">View Bills</Button>
            </div>

            {/* Investments Card */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-xl font-medium mb-2">Investments</h3>
              <p className="text-gray-500 mb-4">Monitor your investment portfolio</p>
              <Button variant="outline" className="w-full">View Investments</Button>
            </div>

            {/* Job Applications Card */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-xl font-medium mb-2">Job Applications</h3>
              <p className="text-gray-500 mb-4">Track your job application status</p>
              <Button variant="outline" className="w-full">View Applications</Button>
            </div>

            {/* Expenses Card */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-xl font-medium mb-2">Expenses</h3>
              <p className="text-gray-500 mb-4">Monitor your spending habits</p>
              <Button variant="outline" className="w-full">View Expenses</Button>
            </div>
          </div>
        </div>
      </Layout>
    </>
  );
}

import React from 'react';
import { Head } from '@inertiajs/react';
import { Button } from '../../components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '../../components/ui/card';

export default function Dashboard() {
  return (
    <>
      <Head title="Dashboard" />

      <div className="min-h-screen bg-slate-50 flex flex-col">
        {/* Header */}
        <header className="border-b border-gray-200 py-4 bg-white shadow-sm">
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
                  className="text-blue-700"
                >
                  <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/>
                  <circle cx="16.5" cy="7.5" r=".5"/>
                </svg>
                <h1 className="text-xl font-bold text-blue-700">LifeOS</h1>
              </div>

              <div className="flex items-center space-x-4">
                <button
                  className="rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
                  aria-label="Toggle theme"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                  </svg>
                </button>

                <a href="/dashboard" className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">
                  Dashboard
                </a>
              </div>
            </div>
          </div>
        </header>

        {/* Main Content */}
        <main className="flex-1 py-8">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="space-y-8">
              {/* Welcome Section */}
              <div className="flex flex-col lg:flex-row gap-6 items-start">
                <Card className="flex-1 bg-white rounded-lg border border-gray-200 shadow-md overflow-hidden">
                  <CardHeader className="border-b border-gray-100 bg-white">
                    <div className="flex items-center gap-3 mb-2">
                      <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="20"
                          height="20"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          className="text-blue-700"
                        >
                          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                          <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                      </div>
                      <CardTitle className="text-2xl font-bold text-gray-800">Welcome to LifeOS</CardTitle>
                    </div>
                    <CardDescription className="text-base text-gray-600">
                      Your personal management system. Track your subscriptions, bills, investments, job applications, and expenses all in one place.
                    </CardDescription>
                  </CardHeader>
                  <CardContent className="bg-white">
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-2">
                      <div className="bg-gray-50 p-3 rounded-lg text-center">
                        <div className="text-2xl font-bold text-blue-700">0</div>
                        <div className="text-xs text-gray-500">Active Subscriptions</div>
                      </div>
                      <div className="bg-gray-50 p-3 rounded-lg text-center">
                        <div className="text-2xl font-bold text-cyan-600">0</div>
                        <div className="text-xs text-gray-500">Upcoming Bills</div>
                      </div>
                      <div className="bg-gray-50 p-3 rounded-lg text-center">
                        <div className="text-2xl font-bold text-purple-600">0</div>
                        <div className="text-xs text-gray-500">Investments</div>
                      </div>
                      <div className="bg-gray-50 p-3 rounded-lg text-center">
                        <div className="text-2xl font-bold text-indigo-600">0</div>
                        <div className="text-xs text-gray-500">Applications</div>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                {/* Quick Actions */}
                <Card className="w-full lg:w-80 bg-white rounded-lg border border-gray-200 shadow-md overflow-hidden">
                  <CardHeader className="border-b border-gray-100">
                    <CardTitle className="text-lg font-semibold text-gray-800">Quick Actions</CardTitle>
                    <CardDescription className="text-gray-500">Frequently used actions</CardDescription>
                  </CardHeader>
                  <CardContent className="flex flex-col gap-2 pt-4">
                    <Button className="justify-start bg-blue-700 hover:bg-blue-800 text-white py-2 px-4 rounded-md text-sm flex items-center">
                      <svg className="mr-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                      </svg>
                      Add New Subscription
                    </Button>
                    <Button className="justify-start bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-md text-sm flex items-center">
                      <svg className="mr-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                      </svg>
                      Record New Expense
                    </Button>
                    <Button className="justify-start border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 py-2 px-4 rounded-md text-sm flex items-center">
                      <svg className="mr-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                      </svg>
                      Add Bill Payment
                    </Button>
                  </CardContent>
                </Card>
              </div>

              {/* Module Cards Section */}
              <div>
                <h2 className="text-xl font-semibold mb-4 text-gray-800">Modules</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {/* Subscriptions Card */}
                  <div className="bg-white border border-gray-200 border-l-4 border-l-teal-500 rounded-lg shadow-md overflow-hidden transform transition-all duration-200 hover:-translate-y-1 hover:shadow-lg">
                    <div className="p-5">
                      <div className="flex items-center justify-between mb-2">
                        <h3 className="text-lg font-semibold text-gray-800">Subscriptions</h3>
                        <div className="p-2 rounded-full bg-teal-100">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="text-teal-600"
                          >
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                          </svg>
                        </div>
                      </div>
                      <p className="text-sm text-gray-600 mb-4">Manage your recurring subscriptions</p>
                      <div className="h-24 flex items-center justify-center bg-gray-50 rounded-md">
                        <p className="text-gray-500">No active subscriptions</p>
                      </div>
                      <div className="mt-4">
                        <a href="#" className="w-full flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 group">
                          <span>View Subscriptions</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="ml-2 transition-transform group-hover:translate-x-1"
                          >
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  {/* Utility Bills Card */}
                  <div className="bg-white border border-gray-200 border-l-4 border-l-cyan-500 rounded-lg shadow-md overflow-hidden transform transition-all duration-200 hover:-translate-y-1 hover:shadow-lg">
                    <div className="p-5">
                      <div className="flex items-center justify-between mb-2">
                        <h3 className="text-lg font-semibold text-gray-800">Utility Bills</h3>
                        <div className="p-2 rounded-full bg-cyan-100">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="text-cyan-600"
                          >
                            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                            <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                          </svg>
                        </div>
                      </div>
                      <p className="text-sm text-gray-600 mb-4">Track your recurring bills and payments</p>
                      <div className="h-24 flex items-center justify-center bg-gray-50 rounded-md">
                        <p className="text-gray-500">No upcoming bills</p>
                      </div>
                      <div className="mt-4">
                        <a href="#" className="w-full flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 group">
                          <span>View Bills</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="ml-2 transition-transform group-hover:translate-x-1"
                          >
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  {/* Investments Card */}
                  <div className="bg-white border border-gray-200 border-l-4 border-l-purple-500 rounded-lg shadow-md overflow-hidden transform transition-all duration-200 hover:-translate-y-1 hover:shadow-lg">
                    <div className="p-5">
                      <div className="flex items-center justify-between mb-2">
                        <h3 className="text-lg font-semibold text-gray-800">Investments</h3>
                        <div className="p-2 rounded-full bg-purple-100">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="text-purple-600"
                          >
                            <path d="M2 3h20"></path>
                            <path d="M2 8h3m3 0h3m3 0h3m3 0h2"></path>
                            <path d="M2 13h2m3 0h2m3 0h2m3 0h5"></path>
                            <path d="M2 18h4m3 0h3m3 0h9"></path>
                          </svg>
                        </div>
                      </div>
                      <p className="text-sm text-gray-600 mb-4">Monitor your investment portfolio</p>
                      <div className="h-24 flex items-center justify-center bg-gray-50 rounded-md">
                        <p className="text-gray-500">No active investments</p>
                      </div>
                      <div className="mt-4">
                        <a href="#" className="w-full flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 group">
                          <span>View Investments</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            className="ml-2 transition-transform group-hover:translate-x-1"
                          >
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>

        {/* Footer */}
        <footer className="bg-white border-t border-gray-200 py-6">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex flex-col md:flex-row justify-between items-center">
              <div className="flex items-center mb-4 md:mb-0">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  className="text-blue-700 mr-2"
                >
                  <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/>
                  <circle cx="16.5" cy="7.5" r=".5"/>
                </svg>
                <p className="text-sm text-gray-500">
                  &copy; {new Date().getFullYear()} Davor Minchorov
                </p>
              </div>
              <div className="flex gap-6">
                <a href="#" className="text-sm text-gray-500 hover:text-blue-700">Terms</a>
                <a href="#" className="text-sm text-gray-500 hover:text-blue-700">Privacy</a>
                <a href="#" className="text-sm text-gray-500 hover:text-blue-700">Help</a>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </>
  );
}

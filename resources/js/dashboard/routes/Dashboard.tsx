import React from 'react';
import { useAuth } from '../../store/authContext';

function Dashboard() {
  const { user, logout } = useAuth();

  return (
    <div className="min-h-screen bg-gray-50">
      {/* App Bar */}
      <div className="bg-white shadow-elevation-1">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <div className="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white text-lg font-medium">
                L
              </div>
              <h1 className="ml-3 text-xl font-medium text-slate-800">LifeOS</h1>
            </div>
            <div className="flex items-center">
              <span className="mr-4 text-slate-600">
                {user?.name}
              </span>
              <button
                onClick={logout}
                className="px-4 py-2 rounded text-sm font-medium text-slate-600 hover:bg-slate-100"
              >
                Log out
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="bg-white shadow-elevation-1 rounded-lg p-6">
          <h2 className="text-xl font-medium text-slate-800 mb-4">Welcome to your dashboard</h2>
          <p className="text-slate-600">
            Your account is now set up and ready to use. You can start managing your tasks, projects, and more.
          </p>

          <div className="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Dashboard Cards */}
            <div className="bg-white border border-slate-200 rounded-lg shadow-elevation-1 p-6 hover:shadow-elevation-2 transition-shadow">
              <h3 className="font-medium text-slate-800 mb-2">Tasks</h3>
              <p className="text-slate-600">Manage your daily tasks and to-dos</p>
            </div>

            <div className="bg-white border border-slate-200 rounded-lg shadow-elevation-1 p-6 hover:shadow-elevation-2 transition-shadow">
              <h3 className="font-medium text-slate-800 mb-2">Projects</h3>
              <p className="text-slate-600">Organize and track your projects</p>
            </div>

            <div className="bg-white border border-slate-200 rounded-lg shadow-elevation-1 p-6 hover:shadow-elevation-2 transition-shadow">
              <h3 className="font-medium text-slate-800 mb-2">Calendar</h3>
              <p className="text-slate-600">Schedule and manage your events</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;

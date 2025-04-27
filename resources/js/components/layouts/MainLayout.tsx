import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';

interface MainLayoutProps {
  children: React.ReactNode;
}

export default function MainLayout({ children }: MainLayoutProps) {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const location = useLocation();

  const navItems = [
    { name: 'Dashboard', path: '/dashboard', icon: '📊' },
    { name: 'Subscriptions', path: '/subscriptions', icon: '🔄' },
    { name: 'Bills', path: '/bills', icon: '📝' },
    { name: 'Investments', path: '/investments', icon: '📈' },
    { name: 'Expenses', path: '/expenses', icon: '💰' },
    { name: 'Job Applications', path: '/jobs', icon: '👔' },
  ];

  const isActive = (path: string) => {
    return location.pathname.startsWith(path);
  };

  return (
    <div className="min-h-screen bg-surface-variant">
      {/* Top app bar */}
      <header className="bg-surface text-on-surface shadow-elevation-2 relative z-10">
        <div className="container mx-auto px-4 flex items-center justify-between h-16">
          <div className="flex items-center">
            {/* Mobile menu button */}
            <button
              className="md:hidden p-2 rounded-full hover:bg-surface-variant"
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            >
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            {/* Logo */}
            <Link to="/dashboard" className="text-primary text-title-large font-brand ml-2">
              LifeOS
            </Link>
          </div>

          {/* User menu */}
          <div className="flex items-center">
            <button className="p-2 rounded-full hover:bg-surface-variant">
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
              </svg>
            </button>

            <button className="ml-2 p-2 rounded-full hover:bg-surface-variant">
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </button>
          </div>
        </div>
      </header>

      <div className="flex">
        {/* Sidebar navigation - desktop */}
        <aside className="hidden md:block w-64 bg-surface shadow-elevation-1 min-h-[calc(100vh-4rem)]">
          <nav className="p-4">
            <ul className="space-y-1">
              {navItems.map((item) => (
                <li key={item.path}>
                  <Link
                    to={item.path}
                    className={`flex items-center px-4 py-3 rounded-md transition-colors ${
                      isActive(item.path)
                        ? 'bg-primary-container text-on-primary-container font-medium'
                        : 'text-on-surface hover:bg-surface-variant'
                    }`}
                  >
                    <span className="mr-3">{item.icon}</span>
                    {item.name}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
        </aside>

        {/* Mobile menu */}
        {isMobileMenuOpen && (
          <div className="md:hidden fixed inset-0 z-20 bg-black bg-opacity-50" onClick={() => setIsMobileMenuOpen(false)}>
            <aside className="w-64 bg-surface h-full shadow-elevation-3 overflow-y-auto">
              <nav className="p-4">
                <ul className="space-y-1">
                  {navItems.map((item) => (
                    <li key={item.path}>
                      <Link
                        to={item.path}
                        className={`flex items-center px-4 py-3 rounded-md transition-colors ${
                          isActive(item.path)
                            ? 'bg-primary-container text-on-primary-container font-medium'
                            : 'text-on-surface hover:bg-surface-variant'
                        }`}
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        <span className="mr-3">{item.icon}</span>
                        {item.name}
                      </Link>
                    </li>
                  ))}
                </ul>
              </nav>
            </aside>
          </div>
        )}

        {/* Main content */}
        <main className="flex-1">
          {children}
        </main>
      </div>
    </div>
  );
}

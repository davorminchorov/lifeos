import React, { useState } from 'react';
import { Button } from '../../components/ui/button';
import { Container } from '../../components/ui/container';

interface LayoutProps {
  children: React.ReactNode;
}

export default function Layout({ children }: LayoutProps) {
  const [isDarkMode, setIsDarkMode] = useState(false);

  // Toggle dark mode class on document element
  const toggleDarkMode = () => {
    if (isDarkMode) {
      document.documentElement.classList.remove('dark');
    } else {
      document.documentElement.classList.add('dark');
    }
    setIsDarkMode(!isDarkMode);
  };

  return (
    <div className="min-h-screen flex flex-col bg-background text-foreground">
      <header className="border-b border-border py-4 shadow-sm">
        <Container>
          <div className="flex justify-between items-center">
            <div className="flex items-center space-x-1">
              {/* Logo SVG */}
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
              <h1 className="text-xl font-bold text-primary ml-2">LifeOS</h1>
            </div>

            <div className="flex items-center space-x-4">
              {/* Dark mode toggle */}
              <button
                onClick={toggleDarkMode}
                className="rounded-full p-2 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                aria-label={isDarkMode ? "Switch to light mode" : "Switch to dark mode"}
              >
                {isDarkMode ? (
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                  </svg>
                ) : (
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                  </svg>
                )}
              </button>

              <Button variant="ghost" size="sm" className="font-medium">
                Dashboard
              </Button>
            </div>
          </div>
        </Container>
      </header>

      <main className="flex-1 py-8">
        <Container>
          {children}
        </Container>
      </main>

      <footer className="border-t border-border py-6 mt-auto bg-muted/30">
        <Container>
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
                className="text-primary mr-2"
              >
                <path d="M2 18v3c0 .6.4 1 1 1h4v-3h3v-3h2l1.4-1.4a6.5 6.5 0 1 0-4-4Z"/>
                <circle cx="16.5" cy="7.5" r=".5"/>
              </svg>
              <p className="text-sm text-muted-foreground">
                &copy; {new Date().getFullYear()} Davor Minchorov
              </p>
            </div>
            <div className="flex gap-6">
              <Button variant="link" size="sm" className="text-muted-foreground hover:text-primary">
                Terms
              </Button>
              <Button variant="link" size="sm" className="text-muted-foreground hover:text-primary">
                Privacy
              </Button>
              <Button variant="link" size="sm" className="text-muted-foreground hover:text-primary">
                Help
              </Button>
            </div>
          </div>
        </Container>
      </footer>
    </div>
  );
}

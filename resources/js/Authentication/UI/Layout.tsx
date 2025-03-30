import React from 'react';
import { Button } from '../../components/ui/button';

interface LayoutProps {
  children: React.ReactNode;
}

export default function Layout({ children }: LayoutProps) {
  return (
    <div className="min-h-screen bg-background">
      <header className="border-b border-border">
        <div className="container mx-auto py-4 px-4 flex justify-between items-center">
          <h1 className="text-xl font-semibold">LifeOS</h1>
          <nav>
            <Button variant="ghost">Dashboard</Button>
          </nav>
        </div>
      </header>
      <main className="container mx-auto py-6 px-4">
        {children}
      </main>
      <footer className="border-t border-border mt-auto">
        <div className="container mx-auto py-4 px-4">
          <p className="text-sm text-muted-foreground">
            &copy; {new Date().getFullYear()} Davor Minchorov
          </p>
        </div>
      </footer>
    </div>
  );
}

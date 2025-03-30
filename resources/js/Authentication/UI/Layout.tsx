import React from 'react';
import { Button } from '../../components/ui/button';
import { Container } from '../../components/ui/container';

interface LayoutProps {
  children: React.ReactNode;
}

export default function Layout({ children }: LayoutProps) {
  return (
    <div className="min-h-screen flex flex-col bg-background text-foreground">
      <header className="border-b border-border py-4">
        <Container>
          <div className="flex justify-between items-center">
            <div className="flex items-center">
              <h1 className="text-2xl font-bold text-primary">LifeOS</h1>
            </div>
            <nav>
              <Button variant="ghost" className="font-medium">
                Dashboard
              </Button>
            </nav>
          </div>
        </Container>
      </header>

      <main className="flex-1 py-8">
        <Container>
          {children}
        </Container>
      </main>

      <footer className="border-t border-border py-6 mt-auto">
        <Container>
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-sm text-muted-foreground">
              &copy; {new Date().getFullYear()} Davor Minchorov
            </p>
            <div className="flex gap-4 mt-4 md:mt-0">
              <Button variant="link" size="sm" className="text-muted-foreground">
                Terms
              </Button>
              <Button variant="link" size="sm" className="text-muted-foreground">
                Privacy
              </Button>
            </div>
          </div>
        </Container>
      </footer>
    </div>
  );
}

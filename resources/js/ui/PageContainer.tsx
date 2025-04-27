import React from 'react';

interface PageContainerProps {
  children: React.ReactNode;
  title: string;
  subtitle?: string;
  actions?: React.ReactNode;
}

/**
 * PageContainer component that provides consistent page layout structure
 * following Material Design 3 principles with:
 * - Page title and optional subtitle
 * - Optional actions slot for buttons/controls
 * - Properly spaced content container
 */
export function PageContainer({
  children,
  title,
  subtitle,
  actions
}: PageContainerProps) {
  return (
    <div className="w-full max-w-screen-xl mx-auto">
      {/* Page header */}
      <div className="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-headline-medium font-bold text-on-surface mb-1">{title}</h1>
          {subtitle && (
            <p className="text-body-large text-on-surface-variant">{subtitle}</p>
          )}
        </div>
        {actions && (
          <div className="mt-4 md:mt-0 flex flex-wrap gap-2">
            {actions}
          </div>
        )}
      </div>

      {/* Page content */}
      <div className="bg-surface rounded-lg shadow-elevation-1 p-4 md:p-6 border border-outline border-opacity-10">
        {children}
      </div>
    </div>
  );
}

/**
 * PageSection component for dividing page content into logical sections
 */
export function PageSection({
  children,
  title,
  subtitle,
  className = ''
}: {
  children: React.ReactNode;
  title?: string;
  subtitle?: string;
  className?: string;
}) {
  return (
    <section className={`mb-8 ${className}`}>
      {(title || subtitle) && (
        <div className="mb-4 border-b border-outline border-opacity-10 pb-2">
          {title && <h2 className="text-title-large font-medium text-on-surface mb-1">{title}</h2>}
          {subtitle && <p className="text-body-medium text-on-surface-variant">{subtitle}</p>}
        </div>
      )}
      {children}
    </section>
  );
}

/**
 * PageGrid component for creating responsive grid layouts
 */
export function PageGrid({
  children,
  columns = 1
}: {
  children: React.ReactNode;
  columns?: 1 | 2 | 3 | 4;
}) {
  const gridCols = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 md:grid-cols-2',
    3: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
  };

  return (
    <div className={`grid ${gridCols[columns]} gap-4 md:gap-6`}>
      {children}
    </div>
  );
}

export default PageContainer;

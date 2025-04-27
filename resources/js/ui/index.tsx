import React from 'react';

// Export UI components from their respective directories
export { default as Button } from './Button';
export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from './Card';
export { Badge } from './Badge';
export { Tabs } from './Tabs';
export { Dialog } from './Dialog';
export { Input } from './Input';
export { Label } from './Label';
export { Select } from './Select';
export { Separator } from './Separator';
export { Textarea } from './Textarea';
export { Table } from './Table';
export { Modal } from './Modal';

// Add any missing components
export const Spinner = ({ size = 'md' }: { size?: 'sm' | 'md' | 'lg' }) => {
  const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
  };

  return (
    <div className="flex justify-center items-center">
      <div className={`animate-spin rounded-full border-t-2 border-primary-500 ${sizeClasses[size]}`}></div>
    </div>
  );
};

export const Heading = ({
  as: Component = 'h2',
  children,
  className = '',
  ...props
}: {
  as?: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
  children: React.ReactNode;
  className?: string;
  [key: string]: any;
}) => {
  const baseClasses = 'font-bold tracking-tight text-gray-900';
  const sizeClasses = {
    h1: 'text-2xl sm:text-3xl',
    h2: 'text-xl sm:text-2xl',
    h3: 'text-lg sm:text-xl',
    h4: 'text-base sm:text-lg',
    h5: 'text-sm sm:text-base',
    h6: 'text-xs sm:text-sm',
  };

  return (
    <Component
      className={`${baseClasses} ${sizeClasses[Component]} ${className}`}
      {...props}
    >
      {children}
    </Component>
  );
};

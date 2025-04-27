import React from 'react';
import { cn } from '../../utils/cn';

/**
 * Card component following Material Design 3 guidelines
 */

interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: 'filled' | 'outlined' | 'elevated';
  clickable?: boolean;
  fullWidth?: boolean;
}

function Card({
  className,
  variant = 'elevated',
  clickable = false,
  fullWidth = false,
  ...props
}: CardProps) {
  const variantClasses = {
    elevated: 'bg-surface shadow-elevation-1 hover:shadow-elevation-2',
    filled: 'bg-surface-variant',
    outlined: 'border border-outline bg-surface',
  };

  return (
    <div
      className={cn(
        'rounded-lg overflow-hidden',
        variantClasses[variant],
        clickable && 'cursor-pointer transition-shadow',
        fullWidth ? 'w-full' : '',
        className
      )}
      {...props}
    />
  );
}

interface CardHeaderProps extends React.HTMLAttributes<HTMLDivElement> {
  withBorder?: boolean;
}

function CardHeader({
  className,
  withBorder = false,
  ...props
}: CardHeaderProps) {
  return (
    <div
      className={cn(
        'px-6 py-4',
        withBorder && 'border-b border-outline/20',
        className
      )}
      {...props}
    />
  );
}

function CardTitle({
  className,
  ...props
}: React.HTMLAttributes<HTMLHeadingElement>) {
  return (
    <h3
      className={cn(
        'text-title-medium font-medium text-on-surface leading-none',
        className
      )}
      {...props}
    />
  );
}

function CardDescription({
  className,
  ...props
}: React.HTMLAttributes<HTMLParagraphElement>) {
  return (
    <p
      className={cn(
        'text-body-medium text-on-surface-variant mt-1',
        className
      )}
      {...props}
    />
  );
}

function CardContent({
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn('px-6 py-4', className)}
      {...props}
    />
  );
}

function CardFooter({
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        'px-6 py-4 flex items-center border-t border-outline/20',
        className
      )}
      {...props}
    />
  );
}

function CardActions({
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        'flex items-center justify-end space-x-2 px-6 py-4',
        className
      )}
      {...props}
    />
  );
}

// Export all components
export {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
  CardActions
};

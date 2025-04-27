import React from 'react';
import { cn } from '../../utils/cn';

/**
 * Card component following Material Design guidelines
 *
 * Supports the following variants:
 * - elevated: Default card with shadow (elevation level 1)
 * - filled: Solid background without shadow
 * - outlined: Border with no shadow
 *
 * The Card component follows Material Design's 8px border radius for containers
 * and implements proper spacing patterns.
 */

export interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: 'elevated' | 'filled' | 'outlined';
  padding?: 'none' | 'sm' | 'md' | 'lg';
}

const Card = React.forwardRef<HTMLDivElement, CardProps>(
  ({ className, variant = 'elevated', padding = 'md', ...props }, ref) => {
    // Base styles for all cards
    const baseStyles = 'rounded-sm w-full'; // 8px border radius

    // Variant-specific styles
    const variantStyles = {
      elevated: 'bg-surface shadow-elevation-1 hover:shadow-elevation-2 text-surface-on border border-transparent',
      filled: 'bg-surface-variant text-surface-on-variant border border-transparent',
      outlined: 'bg-surface text-surface-on border border-slate-200',
    };

    // Padding options
    const paddingStyles = {
      none: '',
      sm: 'p-3',
      md: 'p-4',
      lg: 'p-6',
    };

    return (
      <div
        ref={ref}
        className={cn(
          baseStyles,
          variantStyles[variant],
          paddingStyles[padding],
          className
        )}
        {...props}
      />
    );
  }
);

Card.displayName = "Card";

/**
 * Card header component with proper spacing according to Material Design
 */
const CardHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex flex-col space-y-1.5 p-4", className)}
    {...props}
  />
));
CardHeader.displayName = "CardHeader";

/**
 * Card title using the Material Design typography system
 */
const CardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn("text-headline-small font-medium", className)}
    {...props}
  />
));
CardTitle.displayName = "CardTitle";

/**
 * Card description with proper text styling
 */
const CardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p
    ref={ref}
    className={cn("text-body-medium text-surface-on-variant", className)}
    {...props}
  />
));
CardDescription.displayName = "CardDescription";

/**
 * Card content area with proper spacing
 */
const CardContent = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div ref={ref} className={cn("p-4 pt-0", className)} {...props} />
));
CardContent.displayName = "CardContent";

/**
 * Card footer with actions aligned to the end
 */
const CardFooter = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => (
  <div
    ref={ref}
    className={cn("flex items-center justify-end gap-2 p-4 pt-0", className)}
    {...props}
  />
));
CardFooter.displayName = "CardFooter";

// Create a compound component for easier usage
const CardComponent = Object.assign(Card, {
  Header: CardHeader,
  Title: CardTitle,
  Description: CardDescription,
  Content: CardContent,
  Footer: CardFooter
});

// Export both individually and as a compound component
export {
  CardComponent as Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter
};

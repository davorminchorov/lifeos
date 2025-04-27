import React from 'react';
import { cn } from '../../utils/cn';

type BadgeVariant = 'default' | 'secondary' | 'outline' | 'success' | 'warning' | 'danger';

interface BadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: BadgeVariant;
}

const Badge = React.forwardRef<HTMLDivElement, BadgeProps>(
  ({ className, variant = 'default', ...props }, ref) => {
    const variantStyles: Record<BadgeVariant, string> = {
      default: 'bg-primary hover:bg-primary/80 border-transparent text-primary-foreground',
      secondary: 'bg-secondary hover:bg-secondary/80 border-transparent text-secondary-foreground',
      outline: 'text-foreground border-border',
      success: 'bg-green-100 text-green-800 hover:bg-green-200 border-transparent',
      warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 border-transparent',
      danger: 'bg-red-100 text-red-800 hover:bg-red-200 border-transparent',
    };

    return (
      <div
        ref={ref}
        className={cn(
          "inline-flex items-center border px-2.5 py-0.5 text-xs font-semibold transition-colors rounded-full",
          variantStyles[variant],
          className
        )}
        {...props}
      />
    );
  }
);

Badge.displayName = 'Badge';

export { Badge };
export type { BadgeProps, BadgeVariant };

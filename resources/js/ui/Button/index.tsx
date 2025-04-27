import React, { forwardRef, ReactElement, JSXElementConstructor } from 'react';
import { cn } from '../../utils/cn';

/**
 * Button component following Material Design guidelines
 *
 * Supports the following features:
 * - Multiple variants (contained, outlined, text, elevated, tonal)
 * - Size variations (sm, md, lg)
 * - Loading state with spinner
 * - Start and end icons
 * - Full width option
 * - Material Design ripple effect
 *
 * Also maintains backward compatibility with legacy variant names:
 * - 'outline' maps to 'outlined'
 * - 'default' maps to 'contained'
 * - 'primary' maps to 'contained'
 */

// Legacy variant types for backward compatibility
type LegacyVariant = 'default' | 'primary' | 'secondary' | 'outline' | 'ghost' | 'link' | 'destructive';

// Material Design variant types
type MaterialVariant = 'contained' | 'outlined' | 'text' | 'elevated' | 'tonal';

// Combined variant type for better developer experience
export type ButtonVariant = MaterialVariant | LegacyVariant;

// Size options
export type ButtonSize = 'sm' | 'md' | 'lg' | 'default';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'filled' | 'tonal' | 'outlined' | 'text';
  size?: 'sm' | 'md' | 'lg';
  icon?: React.ReactNode;
  iconPosition?: 'left' | 'right';
  fullWidth?: boolean;
  asChild?: boolean;
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({
    className,
    variant = 'filled',
    size = 'md',
    icon,
    iconPosition = 'left',
    fullWidth = false,
    asChild = false,
    children,
    ...props
  }, ref) => {
    const variantStyles = {
      filled: 'bg-primary text-on-primary hover:bg-primary/90 active:bg-primary/80 shadow-elevation-1',
      tonal: 'bg-primary-container text-on-primary-container hover:bg-primary-container/90 active:bg-primary-container/80 shadow-elevation-1',
      outlined: 'border-2 border-primary bg-transparent text-primary hover:bg-primary/10 active:bg-primary/20',
      text: 'bg-transparent text-primary hover:bg-primary/10 active:bg-primary/20',
    };

    const sizeStyles = {
      sm: 'px-3 py-1.5 text-sm rounded-full',
      md: 'px-4 py-2 rounded-full',
      lg: 'px-6 py-3 text-lg rounded-full',
    };

    // Handle asChild rendering for React Router Link wrapping
    if (asChild && React.Children.count(children) === 1) {
      const child = React.Children.only(children) as ReactElement<any, any>;

      return React.cloneElement(child, {
        ...child.props,
        className: cn(
          'inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary/25 disabled:opacity-50 disabled:pointer-events-none',
          variantStyles[variant],
          sizeStyles[size],
          fullWidth && 'w-full',
          className,
          child.props.className
        )
      });
    }

    return (
      <button
        ref={ref}
        className={cn(
          'inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary/25 disabled:opacity-50 disabled:pointer-events-none',
          variantStyles[variant],
          sizeStyles[size],
          fullWidth && 'w-full',
          className
        )}
        {...props}
      >
        {icon && iconPosition === 'left' && <span className="mr-2">{icon}</span>}
        {children}
        {icon && iconPosition === 'right' && <span className="ml-2">{icon}</span>}
      </button>
    );
  }
);

Button.displayName = 'Button';

export { Button };
export type { ButtonProps };

// For compatibility with existing code
export default Button;

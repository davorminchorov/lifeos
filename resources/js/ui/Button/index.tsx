import React, { useRef, useState } from 'react';
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

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  size?: ButtonSize;
  isLoading?: boolean;
  fullWidth?: boolean;
  startIcon?: React.ReactNode;
  endIcon?: React.ReactNode;
  asChild?: boolean; // For compatibility with wrapping Link components
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({
    className,
    variant = 'contained',
    size = 'md',
    isLoading = false,
    fullWidth = false,
    startIcon,
    endIcon,
    children,
    disabled,
    onClick,
    asChild, // Ignored for now, but allows for Link compatibility
    ...props
  }, ref) => {
    // For ripple effect
    const buttonRef = useRef<HTMLButtonElement>(null);
    const [rippleStyle, setRippleStyle] = useState<React.CSSProperties>({});
    const [isRippling, setIsRippling] = useState(false);

    // Map legacy variants to Material Design variants
    const normalizedVariant = mapVariant(variant);

    // Maps legacy sizes to Material Design sizes
    const normalizedSize = size === 'default' ? 'md' : size;

    // Material Design uses more pronounced border radius and specific elevations
    const baseStyles = 'relative inline-flex items-center justify-center rounded-full font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none overflow-hidden';

    // Variant styles based on Material Design Button variants
    const variantStyles = {
      contained: 'bg-primary text-on-primary hover:shadow-elevation-2 active:bg-primary/90 focus:ring-primary/50 disabled:bg-primary/40',
      outlined: 'border border-outline text-primary hover:bg-primary/5 active:bg-primary/10 focus:ring-primary/50 disabled:border-outline/40 disabled:text-primary/40',
      text: 'text-primary hover:bg-primary/5 active:bg-primary/10 focus:ring-primary/50 disabled:text-primary/40',
      elevated: 'bg-surface text-primary shadow-elevation-1 hover:shadow-elevation-2 active:shadow-elevation-1 focus:ring-primary/50 disabled:text-primary/40',
      tonal: 'bg-primary-container text-on-primary-container hover:bg-primary-container/90 active:bg-primary-container/80 focus:ring-primary/50 disabled:bg-primary-container/40 disabled:text-on-primary-container/40',

      // Special legacy variants that don't map directly
      secondary: 'bg-slate-600 text-white hover:bg-slate-700 active:bg-slate-800 shadow-sm focus:ring-slate-500 disabled:bg-slate-600/40',
      ghost: 'text-slate-600 hover:bg-slate-50 active:bg-slate-100 focus:ring-slate-500 disabled:text-slate-600/40',
      link: 'text-teal-600 underline hover:text-teal-700 focus:ring-teal-500 disabled:text-teal-600/40',
      destructive: 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 shadow-sm focus:ring-red-500 disabled:bg-red-600/40',
    };

    // Size styles with Material Design touch target sizing
    const sizeStyles = {
      sm: 'h-9 text-sm px-3 py-2 min-w-[64px]',
      md: 'h-10 px-6 py-2.5 min-w-[80px]',
      lg: 'h-12 px-8 py-3 text-base min-w-[96px]',
    };

    // Handle ripple effect
    const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
      if (disabled || isLoading) return;

      const button = buttonRef.current;
      if (!button) return;

      const rect = button.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height) * 2;
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;

      setRippleStyle({
        width: `${size}px`,
        height: `${size}px`,
        top: `${y}px`,
        left: `${x}px`,
      });

      setIsRippling(true);
      setTimeout(() => setIsRippling(false), 600);

      if (onClick) onClick(e);
    };

    return (
      <button
        ref={(node) => {
          // Handle both our internal ref and the forwarded ref
          buttonRef.current = node;
          if (typeof ref === 'function') ref(node);
          else if (ref) ref.current = node;
        }}
        className={cn(
          baseStyles,
          variantStyles[normalizedVariant as keyof typeof variantStyles],
          sizeStyles[normalizedSize as keyof typeof sizeStyles],
          fullWidth && 'w-full',
          "leading-none",
          className
        )}
        disabled={isLoading || disabled}
        onClick={handleClick}
        {...props}
      >
        {/* Material Design ripple effect */}
        {isRippling && (
          <span
            className="absolute block rounded-full bg-current opacity-25 animate-ripple"
            style={rippleStyle}
          />
        )}

        <span className="flex items-center justify-center relative z-10">
          {isLoading && (
            <svg
              className="animate-spin -ml-1 mr-2 h-5 w-5 text-current"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
              />
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
          )}
          {!isLoading && startIcon && <span className="mr-2 -ml-1">{startIcon}</span>}
          <span>{children}</span>
          {!isLoading && endIcon && <span className="ml-2 -mr-1">{endIcon}</span>}
        </span>
      </button>
    );
  }
);

// Map legacy variant names to Material Design variant names
function mapVariant(variant: ButtonVariant): MaterialVariant | 'secondary' | 'ghost' | 'link' | 'destructive' {
  switch (variant) {
    case 'default':
    case 'primary':
      return 'contained';
    case 'outline':
      return 'outlined';
    case 'contained':
    case 'outlined':
    case 'text':
    case 'elevated':
    case 'tonal':
    case 'secondary':
    case 'ghost':
    case 'link':
    case 'destructive':
      return variant;
    default:
      return 'contained';
  }
}

Button.displayName = "Button";

export default Button;

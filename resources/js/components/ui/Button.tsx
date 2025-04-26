import React, { useRef, useState } from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

// Utility to merge tailwind classes
const cn = (...inputs: (string | undefined | null | false)[]) => {
  return twMerge(clsx(inputs));
};

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'contained' | 'outlined' | 'text' | 'elevated' | 'tonal';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
  fullWidth?: boolean;
  startIcon?: React.ReactNode;
  endIcon?: React.ReactNode;
}

export function Button({
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
  ...props
}: ButtonProps) {
  // For ripple effect
  const buttonRef = useRef<HTMLButtonElement>(null);
  const [rippleStyle, setRippleStyle] = useState<React.CSSProperties>({});
  const [isRippling, setIsRippling] = useState(false);

  // Material Design uses more pronounced border radius and specific elevations
  const baseStyles = 'relative inline-flex items-center justify-center rounded-full font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none overflow-hidden';

  // Variant styles based on Material Design Button variants
  const variantStyles = {
    contained: 'bg-teal-600 text-white hover:bg-teal-700 active:bg-teal-800 shadow-sm focus:ring-teal-500 disabled:bg-teal-600/40',
    outlined: 'border border-teal-600 text-teal-600 hover:bg-teal-50 active:bg-teal-100 focus:ring-teal-500 disabled:border-teal-600/40 disabled:text-teal-600/40',
    text: 'text-teal-600 hover:bg-teal-50 active:bg-teal-100 focus:ring-teal-500 disabled:text-teal-600/40',
    elevated: 'bg-white text-teal-600 shadow hover:shadow-md active:shadow-inner focus:ring-teal-500 disabled:text-teal-600/40',
    tonal: 'bg-teal-100 text-teal-800 hover:bg-teal-200 active:bg-teal-300 focus:ring-teal-500 disabled:bg-teal-100/40 disabled:text-teal-800/40',
  };

  // Size styles with Material Design touch target sizing
  const sizeStyles = {
    sm: 'h-9 text-sm px-4 min-w-[64px]',
    md: 'h-10 px-6 min-w-[80px]',
    lg: 'h-12 px-8 text-base min-w-[96px]',
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
      ref={buttonRef}
      className={cn(
        baseStyles,
        variantStyles[variant],
        sizeStyles[size],
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
          className="absolute block rounded-full bg-white bg-opacity-30 animate-ripple"
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

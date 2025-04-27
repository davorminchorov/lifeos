import React, { useState } from 'react';
import { cn } from '../../utils/cn';
import { ChevronDown } from 'lucide-react';

/**
 * Select component following Material Design guidelines
 *
 * Supports the following features:
 * - Multiple variants (outlined, filled)
 * - Size variations (sm, md, lg)
 * - Error state with message
 * - Helper text
 * - Floating label that moves above the field on focus/input
 */

export interface SelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  variant?: 'outlined' | 'filled';
  inputSize?: 'sm' | 'md' | 'lg';
  error?: string;
  helperText?: string;
  label?: string;
  fullWidth?: boolean;
}

const Select = React.forwardRef<HTMLSelectElement, SelectProps>(
  ({
    className,
    variant = 'outlined',
    inputSize = 'md',
    error,
    helperText,
    label,
    fullWidth = true,
    id,
    value,
    children,
    defaultValue,
    onChange,
    ...props
  }, ref) => {
    // Track focused state for label animation
    const [isFocused, setIsFocused] = useState(false);

    // Track if select has a value for label animation
    const [hasValue, setHasValue] = useState(
      Boolean(value || defaultValue)
    );

    // Generate a unique ID for the select if not provided
    const selectId = id || `select-${Math.random().toString(36).substring(2, 9)}`;

    // Handle select focus
    const handleFocus = (e: React.FocusEvent<HTMLSelectElement>) => {
      setIsFocused(true);
      props.onFocus?.(e);
    };

    // Handle select blur
    const handleBlur = (e: React.FocusEvent<HTMLSelectElement>) => {
      setIsFocused(false);
      props.onBlur?.(e);
    };

    // Handle select changes
    const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
      setHasValue(e.target.value !== '');
      onChange?.(e);
    };

    // Base styles for all selects
    const baseStyles = 'block appearance-none transition-colors duration-200 ease-in-out';

    // Container styles
    const containerStyles = cn(
      'relative',
      fullWidth ? 'w-full' : 'inline-block',
    );

    // Size styles
    const sizeStyles = {
      sm: 'h-8 text-xs px-3 py-1',
      md: 'h-10 text-sm px-4 py-2',
      lg: 'h-12 text-base px-4 py-3',
    };

    // Variant styles
    const variantStyles = {
      outlined: cn(
        'bg-transparent border rounded-sm focus:border-primary focus:ring-1 focus:ring-primary',
        error ? 'border-error' : 'border-surface-variant'
      ),
      filled: cn(
        'border-b border-t-0 border-l-0 border-r-0 rounded-t-sm bg-surface-variant/40 focus:bg-surface-variant/60',
        error ? 'border-error' : 'border-surface-variant'
      ),
    };

    // Label styles
    const labelBaseStyles = 'absolute pointer-events-none transition-all duration-200 ease-in-out';
    const labelActiveStyles = 'text-xs -translate-y-6';
    const labelInactiveStyles = 'text-surface-on-variant';

    const labelStyles = cn(
      labelBaseStyles,
      (isFocused || hasValue) ? labelActiveStyles : labelInactiveStyles,
      variant === 'outlined' ? 'px-1 left-3' : 'px-0 left-4',
      error ? 'text-error' : (isFocused ? 'text-primary' : 'text-surface-on-variant/70')
    );

    // Helper & error text styles
    const helperTextStyles = cn(
      'text-xs mt-1',
      error ? 'text-error' : 'text-surface-on-variant/70'
    );

    return (
      <div className={containerStyles}>
        {/* Select field */}
        <div className="relative">
          <select
            id={selectId}
            ref={ref}
            className={cn(
              baseStyles,
              sizeStyles[inputSize],
              variantStyles[variant],
              label ? 'pt-2' : '',
              'pr-10', // Extra padding for the chevron icon
              className
            )}
            onFocus={handleFocus}
            onBlur={handleBlur}
            onChange={handleChange}
            value={value}
            defaultValue={defaultValue}
            {...props}
          >
            {children}
          </select>

          {/* Custom select arrow */}
          <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-surface-on-variant">
            <ChevronDown className="h-4 w-4" />
          </div>

          {/* Floating label */}
          {label && (
            <label
              htmlFor={selectId}
              className={labelStyles}
              style={{
                backgroundColor: variant === 'outlined' ? 'white' : 'transparent',
                transform: `translateY(${(isFocused || hasValue) ? '-1.5rem' : '0.25rem'})`
              }}
            >
              {label}
            </label>
          )}
        </div>

        {/* Helper text or error message */}
        {(error || helperText) && (
          <div className={helperTextStyles}>
            {error || helperText}
          </div>
        )}
      </div>
    );
  }
);

Select.displayName = "Select";

export { Select };

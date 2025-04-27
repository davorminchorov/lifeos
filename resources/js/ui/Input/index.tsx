import React, { useState } from 'react';
import { cn } from '../../utils/cn';

/**
 * Input component following Material Design guidelines
 *
 * Supports the following features:
 * - Multiple variants (outlined, filled)
 * - Size variations (sm, md, lg)
 * - Error state with message
 * - Helper text
 * - Floating label that moves above the field on focus/input
 */

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  variant?: 'outlined' | 'filled';
  inputSize?: 'sm' | 'md' | 'lg';
  error?: string;
  helperText?: string;
  label?: string;
  fullWidth?: boolean;
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({
    className,
    type,
    variant = 'outlined',
    inputSize = 'md',
    error,
    helperText,
    label,
    fullWidth = true,
    id,
    value,
    placeholder,
    defaultValue,
    onChange,
    ...props
  }, ref) => {
    // Track focused state for label animation
    const [isFocused, setIsFocused] = useState(false);

    // Track if input has a value for label animation
    const [hasValue, setHasValue] = useState(
      Boolean(value || defaultValue || placeholder)
    );

    // Generate a unique ID for the input if not provided
    const inputId = id || `input-${Math.random().toString(36).substring(2, 9)}`;

    // Handle input focus
    const handleFocus = (e: React.FocusEvent<HTMLInputElement>) => {
      setIsFocused(true);
      props.onFocus?.(e);
    };

    // Handle input blur
    const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
      setIsFocused(false);
      props.onBlur?.(e);
    };

    // Handle input changes
    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      setHasValue(e.target.value !== '');
      onChange?.(e);
    };

    // Base styles for all inputs
    const baseStyles = 'block transition-colors duration-200 ease-in-out';

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
        {/* Input field */}
        <div className="relative">
          <input
            id={inputId}
            type={type}
            ref={ref}
            className={cn(
              baseStyles,
              sizeStyles[inputSize],
              variantStyles[variant],
              label ? 'pt-2' : '',
              className
            )}
            placeholder={placeholder}
            onFocus={handleFocus}
            onBlur={handleBlur}
            onChange={handleChange}
            value={value}
            defaultValue={defaultValue}
            {...props}
          />

          {/* Floating label */}
          {label && (
            <label
              htmlFor={inputId}
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

Input.displayName = "Input";

export { Input };

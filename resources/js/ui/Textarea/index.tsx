import React, { useState } from 'react';
import { cn } from '../../utils/cn';

/**
 * Textarea component following Material Design guidelines
 *
 * Supports the following features:
 * - Multiple variants (outlined, filled)
 * - Error state with message
 * - Helper text
 * - Floating label that moves above the field on focus/input
 * - Auto-resizing based on content
 */

export interface TextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  variant?: 'outlined' | 'filled';
  error?: string;
  helperText?: string;
  label?: string;
  fullWidth?: boolean;
  resize?: 'none' | 'vertical' | 'horizontal' | 'both';
}

const Textarea = React.forwardRef<HTMLTextAreaElement, TextareaProps>(
  ({
    className,
    variant = 'outlined',
    error,
    helperText,
    label,
    fullWidth = true,
    id,
    value,
    placeholder,
    defaultValue,
    resize = 'vertical',
    onChange,
    ...props
  }, ref) => {
    // Track focused state for label animation
    const [isFocused, setIsFocused] = useState(false);

    // Track if textarea has a value for label animation
    const [hasValue, setHasValue] = useState(
      Boolean(value || defaultValue || placeholder)
    );

    // Generate a unique ID for the textarea if not provided
    const textareaId = id || `textarea-${Math.random().toString(36).substring(2, 9)}`;

    // Handle textarea focus
    const handleFocus = (e: React.FocusEvent<HTMLTextAreaElement>) => {
      setIsFocused(true);
      props.onFocus?.(e);
    };

    // Handle textarea blur
    const handleBlur = (e: React.FocusEvent<HTMLTextAreaElement>) => {
      setIsFocused(false);
      props.onBlur?.(e);
    };

    // Handle textarea changes
    const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
      setHasValue(e.target.value !== '');
      onChange?.(e);
    };

    // Base styles for all textareas
    const baseStyles = 'block transition-colors duration-200 ease-in-out';

    // Container styles
    const containerStyles = cn(
      'relative',
      fullWidth ? 'w-full' : 'inline-block',
    );

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

    // Resize styles
    const resizeStyles = {
      none: 'resize-none',
      vertical: 'resize-y',
      horizontal: 'resize-x',
      both: 'resize',
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
        {/* Textarea field */}
        <div className="relative">
          <textarea
            id={textareaId}
            ref={ref}
            className={cn(
              baseStyles,
              'min-h-[80px] w-full p-4',
              variantStyles[variant],
              resizeStyles[resize],
              label ? 'pt-6' : '',
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
              htmlFor={textareaId}
              className={labelStyles}
              style={{
                backgroundColor: variant === 'outlined' ? 'white' : 'transparent',
                transform: `translateY(${(isFocused || hasValue) ? '-1.5rem' : '0.75rem'})`
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

Textarea.displayName = "Textarea";

export { Textarea };

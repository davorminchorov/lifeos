import React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '../../lib/utils';

const inputVariants = cva(
  'flex w-full rounded-md border border-surface-variant bg-surface px-3 py-2 text-sm ring-offset-surface file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-on-surface-variant/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
  {
    variants: {
      variant: {
        default: 'border-surface-variant',
        filled: 'border-transparent bg-surface-variant/40',
        error: 'border-error text-error placeholder:text-error/60 focus-visible:ring-error',
      },
      inputSize: {
        default: 'h-10 py-2',
        sm: 'h-8 px-2.5 text-xs',
        lg: 'h-12 px-4',
      },
    },
    defaultVariants: {
      variant: 'default',
      inputSize: 'default',
    },
  }
);

export interface InputProps
  extends React.InputHTMLAttributes<HTMLInputElement>,
    Omit<VariantProps<typeof inputVariants>, 'size'> {
  inputSize?: 'default' | 'sm' | 'lg';
  error?: string;
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, variant, inputSize, error, ...props }, ref) => {
    // If there's an error, use error variant
    const inputVariant = error ? 'error' : variant;

    return (
      <div className="w-full space-y-1.5">
        <input
          className={cn(inputVariants({ variant: inputVariant, inputSize, className }))}
          ref={ref}
          {...props}
        />
        {error && (
          <p className="text-xs text-error">{error}</p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export { Input, inputVariants };

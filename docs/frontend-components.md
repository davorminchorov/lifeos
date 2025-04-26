# LifeOS UI Component Development

## UI Component Guidelines

### Component Structure

UI components should be structured with:

1. **Props interface** - Clearly defined with TypeScript
2. **Default values** - Sensible defaults where appropriate 
3. **Variants** - Support multiple variants through props
4. **Composition** - Use composition over inheritance
5. **Accessibility** - Built-in accessibility features

### Example Component

```tsx
import React from 'react'
import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

// Utility to merge tailwind classes
const cn = (...inputs: (string | undefined | null | false)[]) => {
  return twMerge(clsx(inputs));
};

// Type definitions
export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'default' | 'secondary' | 'outline' | 'destructive' | 'success'
  size?: 'sm' | 'md' | 'lg'
  isLoading?: boolean
  fullWidth?: boolean
}

// Component implementation
export function Button({
  className,
  variant = 'default',
  size = 'md',
  isLoading = false,
  fullWidth = false,
  children,
  ...props
}: ButtonProps) {
  // Base styles
  const baseStyles = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';
  
  // Variant styles
  const variantStyles = {
    default: 'bg-primary text-white hover:bg-primary-dark',
    secondary: 'bg-secondary text-white hover:bg-secondary-dark',
    outline: 'border border-input bg-transparent hover:bg-accent hover:text-accent-foreground',
    destructive: 'bg-destructive text-white hover:bg-destructive/90',
    success: 'bg-success text-white hover:bg-success/90',
  };
  
  // Size styles
  const sizeStyles = {
    sm: 'h-8 px-3 text-xs',
    md: 'h-10 px-4 py-2',
    lg: 'h-12 px-6 py-3 text-lg',
  };

  return (
    <button
      className={cn(
        baseStyles,
        variantStyles[variant],
        sizeStyles[size],
        fullWidth && 'w-full',
        className
      )}
      disabled={isLoading || props.disabled}
      {...props}
    >
      {isLoading && (
        <svg 
          className="animate-spin -ml-1 mr-2 h-4 w-4 text-current" 
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
      {children}
    </button>
  )
}
```

## Theming

### Design Token Implementation

- Use CSS variables for design tokens
- Follow naming conventions based on purpose, not value
- Implementation in Tailwind config:

```js
// tailwind.config.js
const colors = require('tailwindcss/colors')

module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          dark: 'hsl(var(--primary-dark))',
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary))',
          dark: 'hsl(var(--secondary-dark))',
        },
        // Other colors...
      }
    }
  }
}
```

```css
/* app.css */
:root {
  --primary: 221 83% 40%;
  --primary-dark: 221 83% 35%;
  --secondary: 174 87% 31%;
  --secondary-dark: 174 87% 26%;
  /* Other design tokens... */
}

.dark {
  --primary: 221 76% 50%;
  --primary-dark: 221 76% 45%;
  /* Dark mode overrides... */
}
```

## Layout Components

### Responsive Design

- Always implement mobile-first design
- Use consistent breakpoints:
  - sm: 640px
  - md: 768px
  - lg: 1024px
  - xl: 1280px
  - 2xl: 1536px

### Example Layout Component

```tsx
// Flexible grid layout component
import React from 'react'
import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

// Utility to merge tailwind classes
const cn = (...inputs: (string | undefined | null | false)[]) => {
  return twMerge(clsx(inputs));
};

interface GridProps {
  children: React.ReactNode
  className?: string
  gap?: 'none' | 'sm' | 'md' | 'lg'
  cols?: {
    xs?: 1 | 2 | 3 | 4 | 6 | 12
    sm?: 1 | 2 | 3 | 4 | 6 | 12
    md?: 1 | 2 | 3 | 4 | 6 | 12
    lg?: 1 | 2 | 3 | 4 | 6 | 12
    xl?: 1 | 2 | 3 | 4 | 6 | 12
  }
}

export function Grid({
  children,
  className,
  gap = 'md',
  cols = { xs: 1, md: 2, lg: 3 },
}: GridProps) {
  const gapClass = {
    none: 'gap-0',
    sm: 'gap-2',
    md: 'gap-4',
    lg: 'gap-6',
  }[gap]
  
  const getColsClass = (breakpoint: string, cols?: 1 | 2 | 3 | 4 | 6 | 12) => {
    if (!cols) return ''
    const prefix = breakpoint === 'xs' ? '' : `${breakpoint}:`
    return `${prefix}grid-cols-${cols}`
  }
  
  return (
    <div
      className={cn(
        'grid w-full',
        getColsClass('xs', cols.xs),
        getColsClass('sm', cols.sm),
        getColsClass('md', cols.md),
        getColsClass('lg', cols.lg),
        getColsClass('xl', cols.xl),
        gapClass,
        className
      )}
    >
      {children}
    </div>
  )
}
``` 

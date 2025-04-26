# LifeOS UI Component Development

## Material Design Adoption

LifeOS is now implementing Material Design principles while maintaining our custom brand identity. This transition moves us away from the Shadcn component library to a more standardized, accessible design system with proven patterns.

### Material Design Benefits

1. **Consistency** - Well-established patterns that users already understand
2. **Accessibility** - Built with accessibility as a core principle
3. **Depth & Motion** - Uses elevation, shadows, and animation to create intuitive interfaces
4. **Responsive Patterns** - Adaptable components that work across all device sizes
5. **Comprehensive Guidelines** - Clear rules for implementing components

## UI Component Guidelines

### Component Structure

UI components should follow Material Design principles while maintaining our custom branding:

1. **Props interface** - Clearly defined with TypeScript
2. **Default values** - Sensible defaults that follow Material Design specs
3. **Variants** - Support multiple variants through props (following Material elevation and emphasis patterns)
4. **Composition** - Use composition over inheritance
5. **Accessibility** - Adhere to WCAG 2.1 AA standards at minimum
6. **Motion** - Implement Material motion patterns for interactions

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
  variant?: 'contained' | 'outlined' | 'text' | 'elevated' | 'tonal'
  size?: 'sm' | 'md' | 'lg'
  isLoading?: boolean
  fullWidth?: boolean
  startIcon?: React.ReactNode
  endIcon?: React.ReactNode
}

// Component implementation
export function Button({
  className,
  variant = 'contained',
  size = 'md',
  isLoading = false,
  fullWidth = false,
  startIcon,
  endIcon,
  children,
  ...props
}: ButtonProps) {
  // Base styles - Material Design uses more pronounced border radius and specific elevations
  const baseStyles = 'relative inline-flex items-center justify-center rounded-full font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';
  
  // Variant styles based on Material Design Button variants
  const variantStyles = {
    contained: 'bg-teal-600 text-white hover:bg-teal-700 shadow-sm active:shadow-inner',
    outlined: 'border border-teal-600 text-teal-600 hover:bg-teal-50 active:bg-teal-100',
    text: 'text-teal-600 hover:bg-teal-50 active:bg-teal-100',
    elevated: 'bg-white text-teal-600 shadow hover:shadow-md active:shadow-inner',
    tonal: 'bg-teal-100 text-teal-800 hover:bg-teal-200 active:bg-teal-300',
  };
  
  // Size styles with Material Design touch target sizing
  const sizeStyles = {
    sm: 'h-9 text-sm px-4 min-w-[64px]',
    md: 'h-10 px-6 min-w-[80px]',
    lg: 'h-12 px-8 text-base min-w-[96px]',
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
      {startIcon && <span className="mr-2">{startIcon}</span>}
      {children}
      {endIcon && <span className="ml-2">{endIcon}</span>}
    </button>
  )
}
```

## Theming

### Material Design Token Implementation

Material Design uses a token-based system for theming. We're implementing this with CSS variables for flexibility:

```js
// tailwind.config.js
const colors = require('tailwindcss/colors')

module.exports = {
  theme: {
    extend: {
      colors: {
        // Material primary and secondary colors
        primary: {
          DEFAULT: '#0F766E', // Teal 600
          light: '#14B8A6', // Teal 500
          dark: '#0D9488', // Teal 700
          container: '#99F6E4', // Teal 200
        },
        secondary: {
          DEFAULT: '#475569', // Slate 600
          light: '#64748B', // Slate 500
          dark: '#334155', // Slate 700
          container: '#E2E8F0', // Slate 200
        },
        surface: {
          DEFAULT: '#FFFFFF',
          variant: '#F8FAFC', // Slate 50
        },
        error: {
          DEFAULT: '#EF4444', // Red 500
          container: '#FECACA', // Red 200
        },
        // Other Material color roles...
      },
      boxShadow: {
        // Material elevation levels
        'elevation-1': '0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.14)',
        'elevation-2': '0 3px 6px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.10)',
        'elevation-3': '0 10px 20px rgba(0,0,0,0.12), 0 3px 6px rgba(0,0,0,0.10)',
        'elevation-4': '0 15px 25px rgba(0,0,0,0.12), 0 5px 10px rgba(0,0,0,0.10)',
      }
    }
  }
}
```

```css
/* app.css */
:root {
  /* Material Design 3 system colors */
  --md-sys-color-primary: #0F766E;
  --md-sys-color-primary-container: #99F6E4;
  --md-sys-color-on-primary: #FFFFFF;
  --md-sys-color-on-primary-container: #022C26;
  --md-sys-color-secondary: #475569;
  --md-sys-color-secondary-container: #E2E8F0;
  --md-sys-color-on-secondary: #FFFFFF;
  --md-sys-color-on-secondary-container: #1E293B;
  --md-sys-color-surface: #FFFFFF;
  --md-sys-color-surface-variant: #F8FAFC;
  --md-sys-color-on-surface: #1E293B;
  --md-sys-color-on-surface-variant: #475569;
  --md-sys-color-error: #EF4444;
  --md-sys-color-error-container: #FECACA;
  --md-sys-color-on-error: #FFFFFF;
  --md-sys-color-on-error-container: #7F1D1D;
  
  /* Material Design shape scales */
  --md-sys-shape-corner-small: 4px;
  --md-sys-shape-corner-medium: 8px;
  --md-sys-shape-corner-large: 16px;
  --md-sys-shape-corner-extra-large: 28px;
}

.dark {
  --md-sys-color-primary: #14B8A6;
  --md-sys-color-primary-container: #134E48;
  --md-sys-color-on-primary: #FFFFFF;
  --md-sys-color-on-primary-container: #99F6E4;
  --md-sys-color-secondary: #94A3B8;
  --md-sys-color-secondary-container: #334155;
  --md-sys-color-on-secondary: #FFFFFF;
  --md-sys-color-on-secondary-container: #E2E8F0;
  --md-sys-color-surface: #121212;
  --md-sys-color-surface-variant: #1E293B;
  --md-sys-color-on-surface: #F8FAFC;
  --md-sys-color-on-surface-variant: #E2E8F0;
  --md-sys-color-error: #F87171;
  --md-sys-color-error-container: #991B1B;
  --md-sys-color-on-error: #FFFFFF;
  --md-sys-color-on-error-container: #FCA5A5;
}
```

## Layout Components

### Material Design Layout Principles

- Use a responsive 4px baseline grid
- Apply 8dp grid for component placement
- Use standard Material breakpoints:
  - xs: 0-599px (mobile)
  - sm: 600-904px (tablet)
  - md: 905-1239px (laptop)
  - lg: 1240-1439px (desktop)
  - xl: 1440px+ (large desktop)

### Example Layout Component

```tsx
// Material Design Grid layout component
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
  spacing?: 0 | 1 | 2 | 3 | 4 | 5
  container?: boolean
  justifyContent?: 'start' | 'center' | 'end' | 'between' | 'around' | 'evenly'
  alignItems?: 'start' | 'center' | 'end' | 'stretch' | 'baseline'
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
  spacing = 2,
  container = false,
  justifyContent = 'start',
  alignItems = 'start',
  cols = { xs: 1, sm: 2, md: 3, lg: 4 },
}: GridProps) {
  // Material Design uses 8dp grid spacing (using Tailwind's equivalent)
  const spacingClasses = {
    0: 'gap-0',
    1: 'gap-1', // 4px
    2: 'gap-2', // 8px
    3: 'gap-4', // 16px
    4: 'gap-6', // 24px
    5: 'gap-8', // 32px
  };
  
  const justifyClasses = {
    'start': 'justify-start',
    'center': 'justify-center',
    'end': 'justify-end',
    'between': 'justify-between',
    'around': 'justify-around',
    'evenly': 'justify-evenly',
  };
  
  const alignClasses = {
    'start': 'items-start',
    'center': 'items-center',
    'end': 'items-end',
    'stretch': 'items-stretch',
    'baseline': 'items-baseline',
  };
  
  const getColsClass = (breakpoint: string, cols?: 1 | 2 | 3 | 4 | 6 | 12) => {
    if (!cols) return ''
    const prefix = breakpoint === 'xs' ? '' : `${breakpoint}:`
    return `${prefix}grid-cols-${cols}`
  }
  
  return (
    <div
      className={cn(
        'grid w-full',
        container && 'container mx-auto px-4',
        justifyClasses[justifyContent],
        alignClasses[alignItems],
        getColsClass('xs', cols.xs),
        getColsClass('sm', cols.sm),
        getColsClass('md', cols.md),
        getColsClass('lg', cols.lg),
        getColsClass('xl', cols.xl),
        spacingClasses[spacing],
        className
      )}
    >
      {children}
    </div>
  )
}
```

## Migration Plan

The transition from Shadcn to Material Design will be implemented in the following phases:

### Phase 1: Design Tokens & Foundations
- Update color system to Material color roles
- Implement typography scale based on Material guidelines
- Set up elevation system (shadows)
- Define shape system (border radius)

### Phase 2: Core Components
- Buttons and form controls
- Cards and surfaces
- Navigation components
- Feedback components (alerts, snackbars)

### Phase 3: Page Templates
- Apply Material layout principles
- Implement responsive patterns
- Ensure consistent spacing

### Phase 4: Motion & Interactions
- Add Material motion patterns
- Implement state transitions
- Fine-tune micro-interactions

import * as React from "react"
import { cn } from "../../lib/utils"

// Button variants using the design system colors
export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'link' | 'danger'
  size?: 'default' | 'sm' | 'lg' | 'icon'
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'primary', size = 'default', ...props }, ref) => {
    return (
      <button
        className={cn(
          "inline-flex items-center justify-center whitespace-nowrap rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",

          // Variants
          variant === 'primary' && "bg-[#0F766E] text-white hover:bg-[#0e6661] active:bg-[#0d5a56]",
          variant === 'secondary' && "bg-[#3B82F6] text-white hover:bg-[#2563eb] active:bg-[#1d4ed8]",
          variant === 'outline' && "border border-[#e2e8f0] bg-transparent hover:bg-[#f8fafc] active:bg-[#f1f5f9]",
          variant === 'ghost' && "bg-transparent hover:bg-[#f8fafc] active:bg-[#f1f5f9]",
          variant === 'link' && "bg-transparent underline-offset-4 hover:underline text-[#0F766E]",
          variant === 'danger' && "bg-[#ef4444] text-white hover:bg-[#dc2626] active:bg-[#b91c1c]",

          // Sizes
          size === 'default' && "h-10 px-4 py-2",
          size === 'sm' && "h-8 px-3 text-sm",
          size === 'lg' && "h-12 px-8 text-lg",
          size === 'icon' && "h-10 w-10",

          className
        )}
        ref={ref}
        {...props}
      />
    )
  }
)
Button.displayName = "Button"

export { Button }

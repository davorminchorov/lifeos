import React, { ButtonHTMLAttributes, forwardRef } from "react"
import { cn } from "../../lib/utils"

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link" |
             "success" | "warning" | "investments" | "expenses" | "subscriptions" | "bills" | "jobs"
  size?: "default" | "sm" | "lg" | "xl" | "icon"
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, children, variant = "default", size = "default", ...props }, ref) => {
    return (
      <button
        className={cn(
          "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50",
          {
            // Base variants
            "bg-primary text-primary-foreground hover:bg-primary/90": variant === "default",
            "bg-destructive text-destructive-foreground hover:bg-destructive/90": variant === "destructive",
            "border border-input bg-background hover:bg-accent hover:text-accent-foreground": variant === "outline",
            "bg-secondary text-secondary-foreground hover:bg-secondary/80": variant === "secondary",
            "hover:bg-accent hover:text-accent-foreground": variant === "ghost",
            "text-primary underline-offset-4 hover:underline": variant === "link",

            // State variants
            "bg-success text-success-foreground hover:bg-success/90": variant === "success",
            "bg-warning text-warning-foreground hover:bg-warning/90": variant === "warning",

            // Module-specific variants
            "bg-investments text-white hover:bg-investments/90": variant === "investments",
            "bg-expenses text-white hover:bg-expenses/90": variant === "expenses",
            "bg-subscriptions text-white hover:bg-subscriptions/90": variant === "subscriptions",
            "bg-bills text-white hover:bg-bills/90": variant === "bills",
            "bg-jobs text-white hover:bg-jobs/90": variant === "jobs",

            // Sizes
            "h-10 px-4 py-2": size === "default",
            "h-9 rounded-md px-3 text-xs": size === "sm",
            "h-11 rounded-md px-8": size === "lg",
            "h-12 rounded-md px-8 text-base": size === "xl",
            "h-10 w-10": size === "icon",
          },
          className
        )}
        ref={ref}
        {...props}
      >
        {children}
      </button>
    )
  }
)

Button.displayName = "Button"

export { Button }

import * as React from "react"
import { cn } from "../../lib/utils"

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  error?: string;
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, type, error, ...props }, ref) => {
    return (
      <div className="relative">
        <input
          type={type}
          className={cn(
            "flex h-10 w-full rounded-md border border-[#e2e8f0] bg-white px-3 py-2 text-sm placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-[#0F766E] focus:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50",
            error && "border-[#ef4444] focus:ring-[#ef4444]",
            className
          )}
          ref={ref}
          {...props}
        />
        {error && (
          <p className="mt-1 text-sm text-[#ef4444]">{error}</p>
        )}
      </div>
    )
  }
)
Input.displayName = "Input"

export { Input }

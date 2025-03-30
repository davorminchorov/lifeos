import React from "react"
import { cn } from "../../lib/utils"

export interface ContainerProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: "sm" | "default" | "lg" | "xl" | "full";
}

const Container = React.forwardRef<HTMLDivElement, ContainerProps>(
  ({ className, size = "default", ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          "mx-auto px-4 w-full",
          {
            "max-w-screen-sm": size === "sm",
            "max-w-screen-md": size === "default",
            "max-w-screen-lg": size === "lg",
            "max-w-screen-xl": size === "xl",
            "max-w-none": size === "full",
          },
          className
        )}
        {...props}
      />
    )
  }
)
Container.displayName = "Container"

export { Container }

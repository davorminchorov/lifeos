import React, { forwardRef, createContext, useContext, useState } from 'react';
import { cn } from '../../utils/cn';
import { X } from 'lucide-react';

/**
 * Dialog component following Material Design guidelines
 *
 * A Dialog is a type of modal window that appears in front of app content to provide critical
 * information or ask for a decision. Dialogs disable all app functionality when they appear,
 * and remain on screen until confirmed, dismissed, or a required action has been taken.
 *
 * This component builds on Radix UI's Dialog primitive for accessibility and adds
 * Material Design styling and animation patterns.
 */

// Dialog context for state management
type DialogContextType = {
  open: boolean;
  setOpen: (open: boolean) => void;
};

const DialogContext = createContext<DialogContextType | undefined>(undefined);

function useDialogContext() {
  const context = useContext(DialogContext);
  if (!context) {
    throw new Error('Dialog components must be used within a Dialog component');
  }
  return context;
}

// Root Dialog component
interface DialogProps {
  children: React.ReactNode;
  open?: boolean;
  onOpenChange?: (open: boolean) => void;
  defaultOpen?: boolean;
}

function Dialog({
  children,
  open: controlledOpen,
  onOpenChange,
  defaultOpen = false,
}: DialogProps) {
  const [uncontrolledOpen, setUncontrolledOpen] = useState(defaultOpen);

  const isControlled = controlledOpen !== undefined;
  const open = isControlled ? controlledOpen : uncontrolledOpen;

  const setOpen = (newOpen: boolean) => {
    if (!isControlled) {
      setUncontrolledOpen(newOpen);
    }
    onOpenChange?.(newOpen);
  };

  return (
    <DialogContext.Provider value={{ open, setOpen }}>
      {children}
    </DialogContext.Provider>
  );
}

// Dialog trigger button
interface DialogTriggerProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  asChild?: boolean;
}

const DialogTrigger = forwardRef<HTMLButtonElement, DialogTriggerProps>(
  ({ onClick, asChild = false, children, ...props }, ref) => {
    const { setOpen } = useDialogContext();

    const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
      setOpen(true);
      onClick?.(e);
    };

    if (asChild) {
      // Clone the child element with the necessary props
      const child = React.Children.only(children) as React.ReactElement;
      return React.cloneElement(child, {
        onClick: handleClick,
        ref,
        ...props,
      } as any);
    }

    return (
      <button type="button" onClick={handleClick} ref={ref} {...props}>
        {children}
      </button>
    );
  }
);

DialogTrigger.displayName = 'DialogTrigger';

// Dialog portal & backdrop
interface DialogPortalProps {
  children: React.ReactNode;
  className?: string;
}

const DialogPortal = ({ children, className }: DialogPortalProps) => {
  const { open } = useDialogContext();

  if (!open) return null;

  return (
    <div
      className={cn(
        "fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50",
        className
      )}
    >
      {children}
    </div>
  );
};

DialogPortal.displayName = 'DialogPortal';

// Dialog overlay (backdrop)
interface DialogOverlayProps extends React.HTMLAttributes<HTMLDivElement> {}

const DialogOverlay = forwardRef<HTMLDivElement, DialogOverlayProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          "fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-all duration-100",
          className
        )}
        {...props}
      />
    );
  }
);

DialogOverlay.displayName = 'DialogOverlay';

// Dialog content
interface DialogContentProps extends React.HTMLAttributes<HTMLDivElement> {
  onClose?: () => void;
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'full';
  hideCloseButton?: boolean;
  fullHeight?: boolean;
}

const DialogContent = forwardRef<HTMLDivElement, DialogContentProps>(
  ({ className, children, onClose, size = 'md', hideCloseButton = false, fullHeight = false, ...props }, ref) => {
    const { setOpen } = useDialogContext();

    const handleClose = () => {
      if (onClose) {
        onClose();
      } else {
        setOpen(false);
      }
    };

    const sizeClasses = {
      xs: 'max-w-xs',
      sm: 'max-w-sm',
      md: 'max-w-lg',
      lg: 'max-w-2xl',
      xl: 'max-w-4xl',
      full: 'max-w-full mx-4'
    };

    return (
      <DialogPortal>
        <div
          className={cn(
            "fixed left-[50%] top-[50%] z-50 grid w-full translate-x-[-50%] translate-y-[-50%] gap-4 bg-surface p-6 shadow-elevation-3 rounded-lg",
            sizeClasses[size],
            fullHeight && "h-[calc(100vh-2rem)]",
            className
          )}
          ref={ref}
          {...props}
        >
          {children}
          {!hideCloseButton && (
            <button
              className="absolute right-4 top-4 rounded-full p-1 text-on-surface-variant/70 hover:bg-surface-variant focus:outline-none focus:ring-2 focus:ring-primary"
              onClick={handleClose}
            >
              <X className="h-4 w-4" />
              <span className="sr-only">Close</span>
            </button>
          )}
        </div>
      </DialogPortal>
    );
  }
);

DialogContent.displayName = 'DialogContent';

// Dialog header
interface DialogHeaderProps extends React.HTMLAttributes<HTMLDivElement> {}

const DialogHeader = forwardRef<HTMLDivElement, DialogHeaderProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn("flex flex-col gap-1.5 text-left", className)}
        {...props}
      />
    );
  }
);

DialogHeader.displayName = 'DialogHeader';

// Dialog footer
interface DialogFooterProps extends React.HTMLAttributes<HTMLDivElement> {}

const DialogFooter = forwardRef<HTMLDivElement, DialogFooterProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn("flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 mt-4", className)}
        {...props}
      />
    );
  }
);

DialogFooter.displayName = 'DialogFooter';

// Dialog title
interface DialogTitleProps extends React.HTMLAttributes<HTMLHeadingElement> {}

const DialogTitle = forwardRef<HTMLHeadingElement, DialogTitleProps>(
  ({ className, ...props }, ref) => {
    return (
      <h2
        ref={ref}
        className={cn("text-title-large font-medium leading-none", className)}
        {...props}
      />
    );
  }
);

DialogTitle.displayName = 'DialogTitle';

// Dialog description
interface DialogDescriptionProps extends React.HTMLAttributes<HTMLParagraphElement> {}

const DialogDescription = forwardRef<HTMLParagraphElement, DialogDescriptionProps>(
  ({ className, ...props }, ref) => {
    return (
      <p
        ref={ref}
        className={cn("text-body-medium text-on-surface-variant", className)}
        {...props}
      />
    );
  }
);

DialogDescription.displayName = 'DialogDescription';

// Dialog body (main content area)
interface DialogBodyProps extends React.HTMLAttributes<HTMLDivElement> {}

const DialogBody = forwardRef<HTMLDivElement, DialogBodyProps>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn("py-2", className)}
        {...props}
      />
    );
  }
);

DialogBody.displayName = 'DialogBody';

// Export all Dialog components
export {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription,
  DialogOverlay,
  DialogPortal,
  DialogBody,
};

import React, { createContext, useContext, useState, forwardRef, ChangeEvent, SelectHTMLAttributes } from 'react';
import { cn } from '../../utils/cn';
import { ChevronDown } from 'lucide-react';

/**
 * Select component following Material Design 3 guidelines
 *
 * A Select component includes a text field, a list of options, and an optional label.
 * When activated, it displays a list of options, with one option marked as selected.
 * When closed, it shows the selected option.
 *
 * This component is built with a11y in mind and provides a seamless experience
 * for both mouse and keyboard users.
 */

// Select context for state management
type SelectContextType = {
  open: boolean;
  setOpen: (open: boolean) => void;
  value: string;
  setValue: (value: string) => void;
  id: string;
}

const SelectContext = createContext<SelectContextType | undefined>(undefined);

function useSelectContext() {
  const context = useContext(SelectContext);
  if (!context) {
    throw new Error('Select components must be used within a Select component');
  }
  return context;
}

// Root Select component
interface SelectProps extends Omit<SelectHTMLAttributes<HTMLSelectElement>, 'size'> {
  children: React.ReactNode;
  defaultValue?: string;
  value?: string;
  onValueChange?: (value: string) => void;
  id?: string;
  disabled?: boolean;
  // Additional props for compatibility with existing code
  label?: string;
  error?: string;
  helperText?: string;
  variant?: 'outlined' | 'filled';
  name?: string;
  onChange?: (e: ChangeEvent<HTMLSelectElement>) => void;
}

// For backward compatibility with HTML select
export function Select({
  children,
  defaultValue = '',
  value: controlledValue,
  onValueChange,
  id = `select-${Math.random().toString(36).substring(2, 9)}`,
  disabled = false,
  label,
  error,
  helperText,
  variant = 'outlined',
  name,
  onChange,
  className,
  ...props
}: SelectProps) {
  // Check if we're using the new compound component pattern or the legacy HTML select pattern
  const isLegacyMode = React.Children.toArray(children).some(child =>
    React.isValidElement(child) && child.type === 'option'
  );

  if (isLegacyMode) {
    // Render a standard HTML select for backward compatibility
    return (
      <div className={cn("form-control", className)}>
        {label && (
          <label
            htmlFor={id}
            className={cn(
              "block text-sm font-medium mb-1",
              error ? "text-error" : "text-on-surface"
            )}
          >
            {label}
          </label>
        )}
        <select
          id={id}
          name={name}
          value={controlledValue}
          defaultValue={defaultValue}
          onChange={onChange}
          disabled={disabled}
          className={cn(
            "block w-full px-3 py-2 text-sm rounded-md border focus:outline-none focus:ring-2 focus:ring-primary",
            variant === 'filled' ? "bg-surface-variant border-transparent" : "bg-transparent border-outline",
            error ? "border-error focus:ring-error" : "",
            disabled ? "opacity-50 cursor-not-allowed" : "",
          )}
          {...props}
        >
          {children}
        </select>
        {(error || helperText) && (
          <p className={cn(
            "mt-1 text-sm",
            error ? "text-error" : "text-on-surface-variant"
          )}>
            {error || helperText}
          </p>
        )}
      </div>
    );
  }

  // Modern compound component implementation
  const [open, setOpen] = useState(false);
  const [uncontrolledValue, setUncontrolledValue] = useState(defaultValue);

  const isControlled = controlledValue !== undefined;
  const value = isControlled ? controlledValue : uncontrolledValue;

  const setValue = (newValue: string) => {
    if (!isControlled) {
      setUncontrolledValue(newValue);
    }
    onValueChange?.(newValue);

    // Trigger onChange for compatibility
    if (onChange && name) {
      const event = {
        target: {
          name,
          value: newValue
        }
      } as ChangeEvent<HTMLSelectElement>;
      onChange(event);
    }

    setOpen(false);
  };

  return (
    <div className={cn("form-control", className)}>
      {label && (
        <label
          htmlFor={id}
          className={cn(
            "block text-sm font-medium mb-1",
            error ? "text-error" : "text-on-surface"
          )}
        >
          {label}
        </label>
      )}
      <SelectContext.Provider value={{ open, setOpen, value, setValue, id }}>
        <div className={cn(
          "relative",
          disabled && "opacity-50 pointer-events-none"
        )}>
          {children}
        </div>
      </SelectContext.Provider>
      {(error || helperText) && (
        <p className={cn(
          "mt-1 text-sm",
          error ? "text-error" : "text-on-surface-variant"
        )}>
          {error || helperText}
        </p>
      )}
    </div>
  );
}

// Select trigger button
interface SelectTriggerProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  placeholder?: string;
  variant?: 'outlined' | 'filled';
}

export const SelectTrigger = forwardRef<HTMLButtonElement, SelectTriggerProps>(
  ({ className, placeholder = 'Select an option', variant = 'outlined', ...props }, ref) => {
    const { open, setOpen, value, id } = useSelectContext();

    return (
      <button
        ref={ref}
        type="button"
        role="combobox"
        aria-controls={`${id}-content`}
        aria-expanded={open}
        aria-haspopup="listbox"
        className={cn(
          "flex h-10 w-full items-center justify-between rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary",
          variant === 'filled'
            ? "bg-surface-variant border-transparent"
            : "bg-transparent border border-outline",
          "placeholder:text-on-surface-variant",
          className
        )}
        onClick={() => setOpen(!open)}
        {...props}
      >
        <SelectValue placeholder={placeholder} />
        <ChevronDown className={cn(
          "h-4 w-4 transition-transform duration-200",
          open && "rotate-180"
        )} />
      </button>
    );
  }
);

SelectTrigger.displayName = 'SelectTrigger';

// Select value display
interface SelectValueProps {
  placeholder?: string;
}

export const SelectValue = ({ placeholder }: SelectValueProps) => {
  const { value } = useSelectContext();

  return (
    <span className={cn(
      "block truncate",
      !value && "text-on-surface-variant"
    )}>
      {value || placeholder}
    </span>
  );
};

// Select dropdown content
interface SelectContentProps extends React.HTMLAttributes<HTMLDivElement> {
  position?: 'popper' | 'item-aligned';
}

export const SelectContent = forwardRef<HTMLDivElement, SelectContentProps>(
  ({ className, position = 'popper', ...props }, ref) => {
    const { open, id } = useSelectContext();

    if (!open) return null;

    return (
      <div
        ref={ref}
        className={cn(
          "absolute z-50 min-w-[8rem] bg-surface rounded-md border border-outline shadow-elevation-2 py-1 mt-1",
          position === 'popper' && "top-full left-0 w-full",
          className
        )}
        id={`${id}-content`}
        role="listbox"
        {...props}
      />
    );
  }
);

SelectContent.displayName = 'SelectContent';

// Select item
interface SelectItemProps extends React.LiHTMLAttributes<HTMLLIElement> {
  value: string;
}

export const SelectItem = forwardRef<HTMLLIElement, SelectItemProps>(
  ({ className, children, value, ...props }, ref) => {
    const { value: selectedValue, setValue } = useSelectContext();
    const isSelected = selectedValue === value;

    return (
      <li
        ref={ref}
        className={cn(
          "relative flex w-full cursor-pointer select-none items-center py-2 px-3 text-sm outline-none",
          "hover:bg-primary-container hover:text-on-primary-container",
          "focus:bg-primary-container focus:text-on-primary-container",
          isSelected && "bg-primary-container text-on-primary-container",
          className
        )}
        role="option"
        aria-selected={isSelected}
        data-value={value}
        onClick={() => setValue(value)}
        {...props}
      >
        {children || value}
      </li>
    );
  }
);

SelectItem.displayName = 'SelectItem';

// HTML Select for non-JS fallback
interface HTMLSelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  options: { value: string; label: string }[];
}

export const HTMLSelect = forwardRef<HTMLSelectElement, HTMLSelectProps>(
  ({ className, options, ...props }, ref) => {
    return (
      <select
        ref={ref}
        className={cn(
          "h-10 w-full rounded-md border border-outline bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary",
          className
        )}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    );
  }
);

HTMLSelect.displayName = 'HTMLSelect';

import { type ReactNode } from 'react'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { cn } from '@/lib/utils'

interface FormFieldProps {
    label: string
    name: string
    error?: string
    required?: boolean
    className?: string
    children?: ReactNode
    // Input props when no children provided
    type?: string
    value?: string | number
    onChange?: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => void
    placeholder?: string
    disabled?: boolean
    multiline?: boolean
    rows?: number
    min?: number | string
    max?: number | string
    step?: number | string
}

export function FormField({
    label,
    name,
    error,
    required,
    className,
    children,
    type = 'text',
    value,
    onChange,
    placeholder,
    disabled,
    multiline,
    rows = 3,
    min,
    max,
    step,
}: FormFieldProps) {
    return (
        <div className={cn('space-y-2', className)}>
            <Label htmlFor={name} className={cn(error && 'text-destructive')}>
                {label}
                {required && <span className="ml-0.5 text-destructive">*</span>}
            </Label>
            {children ?? (multiline ? (
                <Textarea
                    id={name}
                    name={name}
                    value={value ?? ''}
                    onChange={onChange}
                    placeholder={placeholder}
                    disabled={disabled}
                    rows={rows}
                    className={cn(error && 'border-destructive')}
                />
            ) : (
                <Input
                    id={name}
                    name={name}
                    type={type}
                    value={value ?? ''}
                    onChange={onChange}
                    placeholder={placeholder}
                    disabled={disabled}
                    min={min}
                    max={max}
                    step={step}
                    className={cn(error && 'border-destructive')}
                />
            ))}
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    )
}

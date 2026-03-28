import { type ReactNode } from 'react'
import { Separator } from '@/components/ui/separator'

interface FormSectionProps {
    title: string
    description?: string
    children: ReactNode
}

export function FormSection({ title, description, children }: FormSectionProps) {
    return (
        <div className="space-y-4">
            <div>
                <h3 className="text-lg font-medium">{title}</h3>
                {description && (
                    <p className="text-sm text-muted-foreground">{description}</p>
                )}
            </div>
            <Separator />
            <div className="grid gap-4 sm:grid-cols-2">{children}</div>
        </div>
    )
}

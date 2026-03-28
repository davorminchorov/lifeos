import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { cn } from '@/lib/utils'

interface ConfirmationDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    title: string
    description: string
    onConfirm: () => void
    onCancel?: () => void
    confirmLabel?: string
    cancelLabel?: string
    variant?: 'default' | 'danger'
}

export function ConfirmationDialog({
    open,
    onOpenChange,
    title,
    description,
    onConfirm,
    onCancel,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    variant = 'default',
}: ConfirmationDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>{description}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel onClick={onCancel}>{cancelLabel}</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirm}
                        className={cn(
                            variant === 'danger' && 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
                        )}
                    >
                        {confirmLabel}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    )
}

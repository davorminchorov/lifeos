import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export function formatCurrency(amount: number | string | null | undefined, currency: string = 'USD'): string {
    const numericAmount = typeof amount === 'number' ? amount : Number(amount)
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(Number.isFinite(numericAmount) ? numericAmount : 0)
}

export function formatDate(date: string | Date, options?: Intl.DateTimeFormatOptions): string {
    const defaultOptions: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }
    return new Intl.DateTimeFormat('en-US', options ?? defaultOptions).format(
        typeof date === 'string' ? new Date(date) : date
    )
}

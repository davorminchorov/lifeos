import { useCallback } from 'react'

export function useCurrency(defaultCurrency: string = 'USD') {
    const format = useCallback((amount: number, currency?: string) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency ?? defaultCurrency,
        }).format(amount)
    }, [defaultCurrency])

    return { format }
}

import { router } from '@inertiajs/react'
import { useCallback, useState } from 'react'
import { useDebouncedValue } from './use-debounce'

interface UseFiltersOptions {
    route: string
    defaultFilters?: Record<string, string>
}

export function useFilters({ route, defaultFilters = {} }: UseFiltersOptions) {
    const [filters, setFilters] = useState<Record<string, string>>(defaultFilters)

    const debouncedFilters = useDebouncedValue(filters, 300)

    const updateFilter = useCallback((key: string, value: string) => {
        const newFilters = { ...filters, [key]: value }
        if (!value) delete newFilters[key]
        setFilters(newFilters)

        router.get(route, newFilters, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [filters, route])

    const resetFilters = useCallback(() => {
        setFilters({})
        router.get(route, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [route])

    return { filters, debouncedFilters, updateFilter, resetFilters }
}

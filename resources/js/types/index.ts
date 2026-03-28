import type { User } from './models'

export type * from './enums'
export type * from './models'

export interface SharedProps {
    auth: {
        user: User | null
    }
    tenant: {
        id: number
        name: string
    } | null
    flash: {
        success: string | null
        error: string | null
        info: string | null
    }
    notifications: {
        unread_count: number
    }
    [key: string]: unknown
}

export interface PaginatedData<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    links: PaginationLink[]
}

export interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & SharedProps

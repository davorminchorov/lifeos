import { useEffect } from 'react'
import { usePage } from '@inertiajs/react'
import { toast } from 'sonner'
import type { SharedProps } from '@/types'

export function FlashMessages() {
    const { flash } = usePage<SharedProps>().props

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success)
        }
        if (flash?.error) {
            toast.error(flash.error)
        }
        if (flash?.info) {
            toast.info(flash.info)
        }
    }, [flash])

    return null
}

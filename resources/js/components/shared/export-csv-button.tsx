import { useState } from 'react'
import { router } from '@inertiajs/react'
import { Download } from 'lucide-react'
import { Button } from '@/components/ui/button'

interface ExportCsvButtonProps {
    /** The export module key, e.g. "expenses" or "job-applications". */
    module: string
    label?: string
    variant?: 'default' | 'outline' | 'secondary' | 'ghost'
    size?: 'default' | 'sm' | 'lg' | 'icon'
}

/**
 * Queues a background CSV export for a single module and surfaces the result
 * via the global flash toast. The generated file appears on the Data Export
 * page and the user is notified when it is ready.
 */
export function ExportCsvButton({
    module,
    label = 'Export CSV',
    variant = 'outline',
    size = 'sm',
}: ExportCsvButtonProps) {
    const [submitting, setSubmitting] = useState(false)

    const handleExport = () => {
        if (submitting) {
            return
        }

        setSubmitting(true)
        router.post(
            '/exports',
            { modules: [module] },
            {
                preserveScroll: true,
                preserveState: true,
                onFinish: () => setSubmitting(false),
            },
        )
    }

    return (
        <Button variant={variant} size={size} onClick={handleExport} disabled={submitting}>
            <Download className="mr-2 h-4 w-4" />
            {label}
        </Button>
    )
}

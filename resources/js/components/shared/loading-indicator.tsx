import { useEffect, useState } from 'react'
import { router } from '@inertiajs/react'

export function LoadingIndicator() {
    const [loading, setLoading] = useState(false)
    const [progress, setProgress] = useState(0)

    useEffect(() => {
        let timeout: ReturnType<typeof setTimeout>

        const startHandler = () => {
            setLoading(true)
            setProgress(0)
            // Animate progress quickly to 80%, then slow down
            timeout = setTimeout(() => setProgress(30), 100)
            setTimeout(() => setProgress(60), 300)
            setTimeout(() => setProgress(80), 600)
        }

        const finishHandler = () => {
            setProgress(100)
            setTimeout(() => {
                setLoading(false)
                setProgress(0)
            }, 200)
        }

        router.on('start', startHandler)
        router.on('finish', finishHandler)

        return () => {
            router.on('start', () => {})
            router.on('finish', () => {})
            clearTimeout(timeout)
        }
    }, [])

    if (!loading) return null

    return (
        <div className="fixed inset-x-0 top-0 z-50 h-0.5">
            <div
                className="h-full bg-foreground transition-all duration-300 ease-out"
                style={{ width: `${progress}%` }}
            />
        </div>
    )
}

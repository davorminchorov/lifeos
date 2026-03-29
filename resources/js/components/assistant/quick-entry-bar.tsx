import { useCallback, useEffect, useRef, useState } from 'react'
import Markdown from 'react-markdown'
import { router, usePage } from '@inertiajs/react'
import { Input } from '@/components/ui/input'
import { CheckCircle, Loader2 } from 'lucide-react'

function getCookie(name: string): string {
    const match = document.cookie.match(
        new RegExp('(^|;\\s*)' + name + '=([^;]*)')
    )
    return match ? decodeURIComponent(match[2]) : ''
}

interface Result {
    message: string
}

export function QuickEntryBar() {
    const [input, setInput] = useState('')
    const [loading, setLoading] = useState(false)
    const [result, setResult] = useState<Result | null>(null)
    const dismissTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
    const page = usePage()

    // Clear dismiss timer on unmount
    useEffect(() => {
        return () => {
            if (dismissTimerRef.current) {
                clearTimeout(dismissTimerRef.current)
            }
        }
    }, [])

    const clearResult = useCallback(() => {
        setResult(null)
        if (dismissTimerRef.current) {
            clearTimeout(dismissTimerRef.current)
            dismissTimerRef.current = null
        }
    }, [])

    const onSubmit = useCallback(
        async (e: React.FormEvent | React.KeyboardEvent) => {
            e.preventDefault()
            const trimmed = input.trim()
            if (!trimmed || loading) return

            clearResult()
            setLoading(true)

            try {
                const response = await fetch('/api/assistant/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                        'X-Current-Page': page.component,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ message: trimmed }),
                    credentials: 'include',
                })

                if (!response.ok) {
                    throw new Error('Request failed')
                }

                const json = await response.json()

                if (json.success && json.data) {
                    setResult({ message: json.data.message })
                    setInput('')
                    router.reload()
                } else {
                    setResult({
                        message: 'Sorry, something went wrong. Please try again.',
                    })
                }
            } catch {
                setResult({
                    message: 'Could not reach the assistant. Please try again.',
                })
            } finally {
                setLoading(false)

                // Auto-dismiss result after 5 seconds
                dismissTimerRef.current = setTimeout(() => {
                    setResult(null)
                }, 5000)
            }
        },
        [input, loading, page.component, clearResult]
    )

    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent<HTMLInputElement>) => {
            if (e.key === 'Enter') {
                onSubmit(e)
            }
        },
        [onSubmit]
    )

    const isSuccess =
        result?.message &&
        (/created/i.test(result.message) || /added/i.test(result.message))

    return (
        <div className="relative">
            <div className="relative">
                <Input
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    onKeyDown={handleKeyDown}
                    onFocus={clearResult}
                    placeholder="What happened? Type or press &#8984;K for full chat..."
                    disabled={loading}
                    className="h-11 w-full pr-10"
                />
                {loading ? (
                    <div className="absolute right-3 top-1/2 -translate-y-1/2">
                        <Loader2 className="h-4 w-4 animate-spin text-muted-foreground" />
                    </div>
                ) : null}
            </div>

            {result ? (
                <div className="mt-2 flex items-start gap-2 rounded-lg border bg-card p-3 text-sm text-card-foreground shadow-sm">
                    {isSuccess ? (
                        <CheckCircle className="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                    ) : null}
                    <Markdown className="prose prose-sm dark:prose-invert max-w-none [&>*:first-child]:mt-0 [&>*:last-child]:mb-0">
                        {result.message}
                    </Markdown>
                </div>
            ) : null}
        </div>
    )
}

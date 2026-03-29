import { useCallback, useEffect, useRef, useState } from 'react'
import Markdown from 'react-markdown'
import remarkGfm from 'remark-gfm'
import { router, usePage } from '@inertiajs/react'
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Send } from 'lucide-react'

interface Message {
    role: 'user' | 'assistant'
    content: string
}

function getCookie(name: string): string {
    const match = document.cookie.match(
        new RegExp('(^|;\\s*)' + name + '=([^;]*)')
    )
    return match ? decodeURIComponent(match[2]) : ''
}

export function ChatPanel() {
    const [open, setOpen] = useState(false)
    const [messages, setMessages] = useState<Message[]>([])
    const [input, setInput] = useState('')
    const [loading, setLoading] = useState(false)
    const [conversationId, setConversationId] = useState<string | null>(null)
    const messagesEndRef = useRef<HTMLDivElement>(null)
    const inputRef = useRef<HTMLInputElement>(null)
    const abortRef = useRef<AbortController | null>(null)
    const page = usePage()

    // Cmd+K shortcut
    useEffect(() => {
        function handleKeyDown(e: KeyboardEvent) {
            if (e.metaKey && e.key === 'k') {
                e.preventDefault()
                setOpen((prev) => !prev)
            }
        }

        window.addEventListener('keydown', handleKeyDown)
        return () => window.removeEventListener('keydown', handleKeyDown)
    }, [])

    // Auto-scroll to bottom when messages change
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
    }, [messages, loading])

    // Focus input when sheet opens; abort in-flight request when it closes
    useEffect(() => {
        if (open) {
            setTimeout(() => inputRef.current?.focus(), 100)
        } else {
            abortRef.current?.abort()
            abortRef.current = null
        }
    }, [open])

    const sendMessage = useCallback(
        async (e: React.FormEvent) => {
            e.preventDefault()
            const trimmed = input.trim()
            if (!trimmed || loading) return

            const userMessage: Message = { role: 'user', content: trimmed }
            setMessages((prev) => [...prev, userMessage])
            setInput('')
            setLoading(true)

            // Add empty assistant message that we'll stream into
            const assistantIndex = messages.length + 1
            setMessages((prev) => [...prev, { role: 'assistant', content: '' }])

            try {
                abortRef.current?.abort()
                abortRef.current = new AbortController()

                const response = await fetch('/api/assistant/stream', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
                        'X-Current-Page': page.component,
                        Accept: 'text/event-stream',
                    },
                    body: JSON.stringify({
                        message: trimmed,
                        conversation_id: conversationId,
                    }),
                    credentials: 'include',
                    signal: abortRef.current.signal,
                })

                if (!response.ok) {
                    // Fall back to non-streaming on error
                    const json = await response.json().catch(() => null)
                    setMessages((prev) => {
                        const updated = [...prev]
                        updated[assistantIndex] = {
                            role: 'assistant',
                            content: json?.message || 'Sorry, something went wrong. Please try again.',
                        }
                        return updated
                    })
                    return
                }

                const reader = response.body?.getReader()
                const decoder = new TextDecoder()
                let fullText = ''

                if (reader) {
                    while (true) {
                        const { done, value } = await reader.read()
                        if (done) break

                        const chunk = decoder.decode(value, { stream: true })
                        // Parse SSE events: lines starting with "data: "
                        const lines = chunk.split('\n')
                        for (const line of lines) {
                            if (line.startsWith('data: ')) {
                                const data = line.slice(6)
                                if (data === '[DONE]') continue
                                try {
                                    const parsed = JSON.parse(data)
                                    if (parsed.text) {
                                        fullText += parsed.text
                                    } else if (typeof parsed === 'string') {
                                        fullText += parsed
                                    }
                                } catch {
                                    // Raw text chunk (not JSON)
                                    fullText += data
                                }
                            }
                        }

                        // Update the assistant message in real-time
                        setMessages((prev) => {
                            const updated = [...prev]
                            updated[assistantIndex] = {
                                role: 'assistant',
                                content: fullText,
                            }
                            return updated
                        })
                    }
                }

                // After streaming completes, check if we need to reload
                const writeIndicators = ['Created', 'Added', 'Updated', 'Cancelled', 'Logged', 'Marked']
                if (writeIndicators.some(w => fullText.includes(w))) {
                    router.reload()
                }
            } catch (err) {
                if (err instanceof DOMException && err.name === 'AbortError') {
                    return
                }
                setMessages((prev) => {
                    const updated = [...prev]
                    updated[assistantIndex] = {
                        role: 'assistant',
                        content: 'Could not reach the assistant. Please try again.',
                    }
                    return updated
                })
            } finally {
                setLoading(false)
            }
        },
        [input, loading, conversationId, page.component]
    )

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetContent
                side="right"
                className="w-full sm:w-[640px] lg:w-[720px] sm:max-w-[720px] flex flex-col"
            >
                <SheetHeader>
                    <SheetTitle>LifeOS Assistant</SheetTitle>
                </SheetHeader>

                {/* Messages area */}
                <div className="flex-1 overflow-y-auto space-y-3 py-4">
                    {messages.length === 0 && !loading ? (
                        <div className="flex h-full items-center justify-center px-4">
                            <p className="text-center text-sm text-muted-foreground">
                                Hi! I can help you log expenses, track
                                applications, and more. Try: &ldquo;paid $50 at
                                Costco for groceries&rdquo;
                            </p>
                        </div>
                    ) : null}

                    {messages.map((msg, i) => (
                        <div
                            key={i}
                            className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
                        >
                            <div
                                className={`rounded-lg px-4 py-3 text-sm ${
                                    msg.role === 'user'
                                        ? 'max-w-[80%] bg-secondary text-secondary-foreground'
                                        : 'max-w-[90%] bg-card text-card-foreground border'
                                }`}
                            >
                                {msg.role === 'assistant' ? (
                                    <Markdown
                                        remarkPlugins={[remarkGfm]}
                                        className="assistant-markdown"
                                    >
                                        {msg.content}
                                    </Markdown>
                                ) : (
                                    msg.content
                                )}
                            </div>
                        </div>
                    ))}

                    {loading ? (
                        <div className="flex justify-start">
                            <div className="flex items-center gap-1 rounded-lg border bg-card px-3 py-2">
                                <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:-0.3s]" />
                                <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:-0.15s]" />
                                <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground" />
                            </div>
                        </div>
                    ) : null}

                    <div ref={messagesEndRef} />
                </div>

                {/* Input area */}
                <form
                    onSubmit={sendMessage}
                    className="flex items-center gap-2 border-t pt-3"
                >
                    <Input
                        ref={inputRef}
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        placeholder="Type a message..."
                        disabled={loading}
                        className="flex-1"
                    />
                    <Button
                        type="submit"
                        size="icon"
                        disabled={loading || !input.trim()}
                        className="shrink-0 bg-[#F53003] hover:bg-[#F53003]/90 text-white"
                    >
                        <Send className="h-4 w-4" />
                        <span className="sr-only">Send</span>
                    </Button>
                </form>
            </SheetContent>
        </Sheet>
    )
}

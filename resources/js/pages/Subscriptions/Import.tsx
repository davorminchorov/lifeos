import { Head, Link, router } from '@inertiajs/react'
import { useState, useEffect, useRef, useCallback, type FormEvent, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { Upload, CheckCircle2, AlertCircle, Loader2 } from 'lucide-react'

type ImportStage = 'idle' | 'uploading' | 'processing' | 'completed' | 'failed'

interface ImportProgress {
    status: 'idle' | 'processing' | 'completed' | 'failed'
    total: number
    created: number
    skipped: number
    failed: number
    error?: string
}

export default function SubscriptionImport() {
    const [file, setFile] = useState<File | null>(null)
    const [stage, setStage] = useState<ImportStage>('idle')
    const [uploadProgress, setUploadProgress] = useState(0)
    const [importProgress, setImportProgress] = useState<ImportProgress | null>(null)
    const [fileError, setFileError] = useState<string | null>(null)
    const pollingRef = useRef<ReturnType<typeof setInterval> | null>(null)

    const stopPolling = useCallback(() => {
        if (pollingRef.current) {
            clearInterval(pollingRef.current)
            pollingRef.current = null
        }
    }, [])

    const pollProgress = useCallback(() => {
        pollingRef.current = setInterval(async () => {
            try {
                const response = await fetch('/subscriptions/import/progress')
                const data: ImportProgress = await response.json()

                if (data.status === 'processing' || data.status === 'completed' || data.status === 'failed') {
                    setImportProgress(data)
                }

                if (data.status === 'completed') {
                    setStage('completed')
                    stopPolling()
                } else if (data.status === 'failed') {
                    setStage('failed')
                    stopPolling()
                }
            } catch {
                // Ignore transient fetch errors, retry on next tick
            }
        }, 2000)
    }, [stopPolling])

    useEffect(() => {
        return () => stopPolling()
    }, [stopPolling])

    function handleFileChange(e: ChangeEvent<HTMLInputElement>) {
        setFile(e.target.files?.[0] ?? null)
        setFileError(null)
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        if (!file) return

        setStage('uploading')
        setFileError(null)

        const formData = new FormData()
        formData.append('file', file)

        const xsrfToken = decodeURIComponent(
            document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
        )

        const xhr = new XMLHttpRequest()
        xhr.open('POST', '/subscriptions/import')
        xhr.setRequestHeader('X-XSRF-TOKEN', xsrfToken)
        xhr.setRequestHeader('Accept', 'application/json')
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')

        xhr.upload.onprogress = (event) => {
            if (event.lengthComputable) {
                setUploadProgress(Math.round((event.loaded / event.total) * 100))
            }
        }

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                setStage('processing')
                setUploadProgress(100)
                pollProgress()
            } else {
                setStage('idle')
                try {
                    const errorData = JSON.parse(xhr.responseText)
                    setFileError(errorData.errors?.file?.[0] ?? errorData.message ?? 'Upload failed.')
                } catch {
                    setFileError('Upload failed. Please try again.')
                }
            }
        }

        xhr.onerror = () => {
            setStage('idle')
            setFileError('Network error. Please try again.')
        }

        xhr.send(formData)
    }

    const processed = importProgress
        ? importProgress.created + importProgress.skipped + importProgress.failed
        : 0
    const progressPercent = importProgress && importProgress.total > 0
        ? Math.round((processed / importProgress.total) * 100)
        : 0

    return (
        <AppLayout>
            <Head title="Import Subscriptions" />

            <PageHeader title="Import Subscriptions" description="Upload a CSV file to bulk import subscriptions">
                <Button variant="outline" asChild>
                    <Link href="/subscriptions">Back to List</Link>
                </Button>
            </PageHeader>

            <Card className="mx-auto max-w-2xl">
                <CardHeader>
                    <CardTitle>CSV Import</CardTitle>
                </CardHeader>
                <CardContent>
                    {stage === 'idle' || stage === 'uploading' ? (
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="file">CSV File</Label>
                                <div className="flex items-center gap-4">
                                    <Input
                                        id="file"
                                        type="file"
                                        accept=".csv"
                                        onChange={handleFileChange}
                                        className="flex-1"
                                        disabled={stage === 'uploading'}
                                    />
                                </div>
                                {fileError ? (
                                    <p className="text-sm text-destructive">{fileError}</p>
                                ) : null}
                                {stage === 'uploading' ? (
                                    <div className="mt-2">
                                        <Progress value={uploadProgress} />
                                        <p className="mt-1 text-xs text-muted-foreground">{uploadProgress}% uploaded</p>
                                    </div>
                                ) : null}
                            </div>

                            <div className="rounded-md border border-border bg-muted p-4">
                                <h3 className="mb-2 text-sm font-medium">Expected CSV Format</h3>
                                <p className="text-xs text-muted-foreground">
                                    Your CSV file should include the following columns. Required columns are marked with *.
                                </p>
                                <div className="mt-2 space-y-1">
                                    <code className="block text-xs text-muted-foreground">
                                        service_name*, cost*, billing_cycle*, next_billing_date*
                                    </code>
                                    <code className="block text-xs text-muted-foreground">
                                        currency, category, status, auto_renewal, payment_method,
                                    </code>
                                    <code className="block text-xs text-muted-foreground">
                                        start_date, description, notes, tags, url
                                    </code>
                                </div>
                                <p className="mt-3 text-xs text-muted-foreground">
                                    <strong>Example row:</strong>
                                </p>
                                <code className="mt-1 block text-xs text-muted-foreground">
                                    Netflix,15.99,monthly,2026-04-15,USD,Entertainment,active,true,Credit Card,2024-01-01,Premium plan,,&quot;streaming,entertainment&quot;,https://netflix.com
                                </code>
                            </div>

                            <div className="flex justify-end gap-3">
                                <Button type="button" variant="outline" asChild>
                                    <Link href="/subscriptions">Cancel</Link>
                                </Button>
                                <Button type="submit" disabled={stage === 'uploading' || !file}>
                                    <Upload className="mr-2 h-4 w-4" />
                                    {stage === 'uploading' ? 'Uploading...' : 'Import CSV'}
                                </Button>
                            </div>
                        </form>
                    ) : null}

                    {stage === 'processing' ? (
                        <div className="space-y-6">
                            <div className="flex items-center gap-3">
                                <Loader2 className="h-5 w-5 animate-spin text-primary" />
                                <p className="text-sm font-medium">
                                    {importProgress ? 'Processing your import...' : 'Waiting for import to start...'}
                                </p>
                            </div>

                            {importProgress ? (
                                <>
                                    <Progress value={progressPercent} />

                                    <div className="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <p className="text-2xl font-bold text-primary">{importProgress.created}</p>
                                            <p className="text-xs text-muted-foreground">Created</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-bold text-muted-foreground">{importProgress.skipped}</p>
                                            <p className="text-xs text-muted-foreground">Skipped</p>
                                        </div>
                                        <div>
                                            <p className="text-2xl font-bold text-destructive">{importProgress.failed}</p>
                                            <p className="text-xs text-muted-foreground">Failed</p>
                                        </div>
                                    </div>

                                    <p className="text-xs text-muted-foreground text-center">
                                        {processed} of {importProgress.total} rows processed ({progressPercent}%)
                                    </p>
                                </>
                            ) : (
                                <p className="text-xs text-muted-foreground text-center">
                                    Your import has been queued. Make sure the queue worker is running.
                                </p>
                            )}
                        </div>
                    ) : null}

                    {stage === 'completed' ? (
                        <div className="space-y-6">
                            <div className="flex items-center gap-3">
                                <CheckCircle2 className="h-5 w-5 text-green-600" />
                                <p className="text-sm font-medium">Import completed!</p>
                            </div>

                            <div className="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p className="text-2xl font-bold text-primary">{importProgress?.created ?? 0}</p>
                                    <p className="text-xs text-muted-foreground">Created</p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-muted-foreground">{importProgress?.skipped ?? 0}</p>
                                    <p className="text-xs text-muted-foreground">Skipped</p>
                                </div>
                                <div>
                                    <p className="text-2xl font-bold text-destructive">{importProgress?.failed ?? 0}</p>
                                    <p className="text-xs text-muted-foreground">Failed</p>
                                </div>
                            </div>

                            <div className="flex justify-end gap-3">
                                <Button variant="outline" onClick={() => { setStage('idle'); setFile(null); setImportProgress(null) }}>
                                    Import Another
                                </Button>
                                <Button asChild>
                                    <Link href="/subscriptions">View Subscriptions</Link>
                                </Button>
                            </div>
                        </div>
                    ) : null}

                    {stage === 'failed' ? (
                        <div className="space-y-6">
                            <div className="flex items-center gap-3">
                                <AlertCircle className="h-5 w-5 text-destructive" />
                                <p className="text-sm font-medium">Import failed</p>
                            </div>

                            <p className="text-sm text-muted-foreground">
                                {importProgress?.error ?? 'An unexpected error occurred while processing the import.'}
                            </p>

                            <div className="flex justify-end gap-3">
                                <Button variant="outline" onClick={() => { setStage('idle'); setFile(null); setImportProgress(null) }}>
                                    Try Again
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/subscriptions">Back to Subscriptions</Link>
                                </Button>
                            </div>
                        </div>
                    ) : null}
                </CardContent>
            </Card>
        </AppLayout>
    )
}

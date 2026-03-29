import { Head, Link, useForm } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Upload } from 'lucide-react'

export default function InvestmentImport() {
    const { data, setData, post, processing, errors, progress } = useForm<{ file: File | null }>({
        file: null,
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/investments/import', {
            forceFormData: true,
        })
    }

    return (
        <AppLayout>
            <Head title="Import Investments" />

            <PageHeader title="Import Investments" description="Upload a CSV file to bulk import investments">
                <Button variant="outline" asChild>
                    <Link href="/investments">Back to List</Link>
                </Button>
            </PageHeader>

            <Card className="mx-auto max-w-2xl">
                <CardHeader>
                    <CardTitle>CSV Import</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="file">CSV File</Label>
                            <div className="flex items-center gap-4">
                                <Input
                                    id="file"
                                    type="file"
                                    accept=".csv"
                                    onChange={e => {
                                        const file = e.target.files?.[0] ?? null
                                        setData('file', file)
                                    }}
                                    className="flex-1"
                                />
                            </div>
                            {errors.file ? (
                                <p className="text-sm text-destructive">{errors.file}</p>
                            ) : null}
                            {progress ? (
                                <div className="mt-2">
                                    <div className="h-2 w-full rounded-full bg-secondary">
                                        <div
                                            className="h-2 rounded-full bg-primary transition-all"
                                            style={{ width: `${progress.percentage}%` }}
                                        />
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">{progress.percentage}% uploaded</p>
                                </div>
                            ) : null}
                        </div>

                        <div className="rounded-md border border-border bg-muted p-4">
                            <h3 className="mb-2 text-sm font-medium">Expected CSV Format</h3>
                            <p className="text-xs text-muted-foreground">
                                Your CSV file should include the following columns:
                            </p>
                            <code className="mt-2 block text-xs text-muted-foreground">
                                name, investment_type, symbol_identifier, quantity, purchase_price, currency, purchase_date, status
                            </code>
                        </div>

                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" asChild>
                                <Link href="/investments">Cancel</Link>
                            </Button>
                            <Button type="submit" disabled={processing || !data.file}>
                                <Upload className="mr-2 h-4 w-4" />
                                {processing ? 'Uploading...' : 'Import CSV'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

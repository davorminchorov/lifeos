import { Head, Link } from '@inertiajs/react'
import { Button } from '@/components/ui/button'

export default function Error404() {
    return (
        <>
            <Head title="Page Not Found" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4">
                <h1 className="text-6xl font-bold text-foreground">404</h1>
                <p className="mt-4 text-lg text-muted-foreground">Page not found</p>
                <p className="mt-2 text-sm text-muted-foreground">The page you're looking for doesn't exist or has been moved.</p>
                <Button asChild className="mt-6">
                    <Link href="/dashboard">Go to Dashboard</Link>
                </Button>
            </div>
        </>
    )
}

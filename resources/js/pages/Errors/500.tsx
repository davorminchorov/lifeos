import { Head } from '@inertiajs/react'

export default function Error500() {
    return (
        <>
            <Head title="Server Error" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4">
                <h1 className="text-6xl font-bold text-foreground">500</h1>
                <p className="mt-4 text-lg text-muted-foreground">Something went wrong</p>
                <p className="mt-2 text-sm text-muted-foreground">We're working on fixing this. Please try again later.</p>
            </div>
        </>
    )
}

import { Head } from '@inertiajs/react'

export default function Error503() {
    return (
        <>
            <Head title="Maintenance" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4">
                <h1 className="text-6xl font-bold text-foreground">503</h1>
                <p className="mt-4 text-lg text-muted-foreground">Under Maintenance</p>
                <p className="mt-2 text-sm text-muted-foreground">We'll be back shortly. Thanks for your patience.</p>
            </div>
        </>
    )
}

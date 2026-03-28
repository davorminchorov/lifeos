import { Head } from '@inertiajs/react'

export default function DashboardIndex() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex min-h-screen items-center justify-center">
                <h1 className="text-2xl font-semibold text-primary-700 dark:text-dark-600">
                    LifeOS — React + Inertia v3 Setup Complete
                </h1>
            </div>
        </>
    )
}

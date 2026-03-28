import { type ReactNode } from 'react'

interface GuestLayoutProps {
    children: ReactNode
}

export default function GuestLayout({ children }: GuestLayoutProps) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-background px-4">
            <div className="w-full max-w-md">
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-semibold text-foreground">LifeOS</h1>
                    <p className="mt-2 text-sm text-muted-foreground">Organize your life, beautifully.</p>
                </div>
                {children}
            </div>
        </div>
    )
}

import '../css/app.css'

import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { ThemeProvider } from 'next-themes'
import { Toaster } from 'sonner'
import { LoadingIndicator } from './components/shared/loading-indicator'

createInertiaApp({
    title: (title) => title ? `${title} - LifeOS` : 'LifeOS',
    resolve: (name) => {
        const pages = import.meta.glob<{ default: React.ComponentType }>('./pages/**/*.tsx', { eager: true })
        return pages[`./pages/${name}.tsx`]
    },
    setup({ el, App, props }) {
        createRoot(el).render(
            <ThemeProvider attribute="class" defaultTheme="system" enableSystem>
                <LoadingIndicator />
                <App {...props} />
                <Toaster position="top-right" richColors closeButton />
            </ThemeProvider>
        )
    },
})

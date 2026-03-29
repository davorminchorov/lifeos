import { useState, type ReactNode } from 'react'
import { Sidebar } from './sidebar'
import { Topbar } from './topbar'
import { FlashMessages } from './flash-messages'
import { ChatPanel } from '@/components/assistant/chat-panel'
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet'

interface AppLayoutProps {
    children: ReactNode
}

export default function AppLayout({ children }: AppLayoutProps) {
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false)
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false)

    return (
        <div className="flex h-screen overflow-hidden bg-background">
            {/* Desktop sidebar */}
            <div className="hidden lg:block">
                <Sidebar
                    collapsed={sidebarCollapsed}
                    onToggle={() => setSidebarCollapsed(!sidebarCollapsed)}
                />
            </div>

            {/* Mobile sidebar */}
            <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
                <SheetContent side="left" className="w-64 p-0">
                    <SheetTitle className="sr-only">Navigation</SheetTitle>
                    <Sidebar
                        collapsed={false}
                        onToggle={() => setMobileMenuOpen(false)}
                    />
                </SheetContent>
            </Sheet>

            {/* Main content */}
            <div className="flex flex-1 flex-col overflow-hidden">
                <Topbar onMenuToggle={() => setMobileMenuOpen(true)} />
                <main className="flex-1 overflow-y-auto">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>

            <FlashMessages />
            <ChatPanel />
        </div>
    )
}

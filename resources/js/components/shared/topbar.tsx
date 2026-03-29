import { Link, usePage, router } from '@inertiajs/react'
import { Bell, Menu, Moon, Sun, Settings, LogOut, User } from 'lucide-react'
import { useTheme } from 'next-themes'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type { SharedProps } from '@/types'

interface TopbarProps {
    onMenuToggle: () => void
}

export function Topbar({ onMenuToggle }: TopbarProps) {
    const { auth, notifications } = usePage<SharedProps>().props
    const { theme, setTheme } = useTheme()

    return (
        <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background px-4 lg:px-6">
            <div className="flex items-center gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    className="lg:hidden"
                    onClick={onMenuToggle}
                >
                    <Menu className="h-5 w-5" />
                </Button>
            </div>

            <div className="flex items-center gap-2">
                {/* Dark mode toggle */}
                <Button
                    variant="ghost"
                    size="icon"
                    onClick={() => setTheme(theme === 'dark' ? 'light' : 'dark')}
                >
                    <Sun className="h-4 w-4 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                    <Moon className="absolute h-4 w-4 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                    <span className="sr-only">Toggle theme</span>
                </Button>

                {/* Notifications */}
                <Link href="/notifications">
                    <Button variant="ghost" size="icon" className="relative">
                        <Bell className="h-4 w-4" />
                        {(notifications?.unread_count ?? 0) > 0 && (
                            <span className="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px] font-medium text-destructive-foreground">
                                {notifications.unread_count}
                            </span>
                        )}
                    </Button>
                </Link>

                {/* User menu */}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon">
                            <User className="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-48">
                        {auth?.user && (
                            <>
                                <div className="px-2 py-1.5">
                                    <p className="text-sm font-medium">{auth.user.name}</p>
                                    <p className="text-xs text-muted-foreground">{auth.user.email}</p>
                                </div>
                                <DropdownMenuSeparator />
                            </>
                        )}
                        <DropdownMenuItem asChild>
                            <Link href="/profile">
                                <User className="mr-2 h-4 w-4" />
                                Profile
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <Link href="/settings">
                                <Settings className="mr-2 h-4 w-4" />
                                Settings
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            onClick={() => router.post('/logout')}
                            className="text-destructive"
                        >
                            <LogOut className="mr-2 h-4 w-4" />
                            Log out
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    )
}

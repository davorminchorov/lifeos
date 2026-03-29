import { useState } from 'react'
import { Link, usePage } from '@inertiajs/react'
import {
    ChevronDown,
    ChevronLeft,
    LayoutDashboard,
    Wallet,
    Receipt,
    CreditCard,
    Zap,
    FileText,
    Users,
    FileBarChart,
    FileMinus,
    Percent,
    Tags,
    Briefcase,
    TrendingUp,
    FolderKanban,
    FileSignature,
    Shield,
    HandCoins,
    UtensilsCrossed,
    DollarSign,
    Calendar,
    type LucideIcon,
} from 'lucide-react'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'

interface NavItem {
    label: string
    href: string
    icon: LucideIcon
}

interface NavGroup {
    label: string
    icon: LucideIcon
    items: NavItem[]
}

const navigation: NavGroup[] = [
    {
        label: 'Personal Finance',
        icon: Wallet,
        items: [
            { label: 'Budgets', href: '/budgets', icon: Wallet },
            { label: 'Expenses', href: '/expenses', icon: Receipt },
            { label: 'Subscriptions', href: '/subscriptions', icon: CreditCard },
            { label: 'Utility Bills', href: '/utility-bills', icon: Zap },
        ],
    },
    {
        label: 'Business',
        icon: Briefcase,
        items: [
            { label: 'Invoicing', href: '/invoicing/dashboard', icon: FileText },
            { label: 'Customers', href: '/invoicing/customers', icon: Users },
            { label: 'Invoices', href: '/invoicing/invoices', icon: FileBarChart },
            { label: 'Credit Notes', href: '/invoicing/credit-notes', icon: FileMinus },
            { label: 'Tax Rates', href: '/invoicing/tax-rates', icon: Percent },
            { label: 'Discounts', href: '/invoicing/discounts', icon: Tags },
            { label: 'Job Applications', href: '/job-applications', icon: Briefcase },
        ],
    },
    {
        label: 'Assets',
        icon: TrendingUp,
        items: [
            { label: 'Investments', href: '/investments', icon: TrendingUp },
            { label: 'Project Investments', href: '/project-investments', icon: FolderKanban },
            { label: 'Contracts', href: '/contracts', icon: FileSignature },
            { label: 'Warranties', href: '/warranties', icon: Shield },
            { label: 'IOUs', href: '/ious', icon: HandCoins },
        ],
    },
    {
        label: 'Lifestyle',
        icon: Calendar,
        items: [
            { label: 'Cycle Menus', href: '/cycle-menus', icon: UtensilsCrossed },
            { label: 'Currency', href: '/currency', icon: DollarSign },
            { label: 'Holidays', href: '/holidays', icon: Calendar },
        ],
    },
]

interface SidebarProps {
    collapsed: boolean
    onToggle: () => void
}

export function Sidebar({ collapsed, onToggle }: SidebarProps) {
    const { url } = usePage()
    const [openGroups, setOpenGroups] = useState<string[]>(
        navigation.map(g => g.label)
    )

    const toggleGroup = (label: string) => {
        setOpenGroups(prev =>
            prev.includes(label)
                ? prev.filter(l => l !== label)
                : [...prev, label]
        )
    }

    const isActive = (href: string) => {
        return url.startsWith(href)
    }

    return (
        <aside
            className={cn(
                'flex h-screen flex-col border-r border-border bg-sidebar-background transition-all duration-300',
                collapsed ? 'w-16' : 'w-64'
            )}
        >
            {/* Logo */}
            <div className="flex h-16 items-center justify-between border-b border-border px-4">
                {!collapsed && (
                    <Link href="/dashboard" className="text-xl font-semibold text-sidebar-foreground">
                        LifeOS
                    </Link>
                )}
                <Button
                    variant="ghost"
                    size="icon"
                    onClick={onToggle}
                    className="h-10 w-10 text-sidebar-foreground"
                >
                    <ChevronLeft className={cn('h-4 w-4 transition-transform', collapsed && 'rotate-180')} />
                </Button>
            </div>

            {/* Dashboard link */}
            <div className="px-3 py-2">
                <Link
                    href="/dashboard"
                    className={cn(
                        'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors',
                        isActive('/dashboard')
                            ? 'border-l-2 border-foreground bg-sidebar-accent text-sidebar-accent-foreground'
                            : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
                    )}
                >
                    <LayoutDashboard className="h-4 w-4 shrink-0" />
                    {!collapsed && <span>Dashboard</span>}
                </Link>
            </div>

            {/* Navigation groups */}
            <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-2">
                {navigation.map((group) => (
                    <div key={group.label}>
                        {!collapsed ? (
                            <button
                                onClick={() => toggleGroup(group.label)}
                                className="flex w-full items-center justify-between rounded-md px-3 py-2.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground hover:text-sidebar-foreground"
                            >
                                <span>{group.label}</span>
                                <ChevronDown
                                    className={cn(
                                        'h-3 w-3 transition-transform',
                                        openGroups.includes(group.label) && 'rotate-180'
                                    )}
                                />
                            </button>
                        ) : (
                            <div className="my-2 border-t border-border" />
                        )}
                        {(collapsed || openGroups.includes(group.label)) && (
                            <div className="space-y-0.5">
                                {group.items.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className={cn(
                                            'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition-colors',
                                            isActive(item.href)
                                                ? 'border-l-2 border-foreground bg-sidebar-accent font-medium text-sidebar-accent-foreground'
                                                : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
                                        )}
                                        title={collapsed ? item.label : undefined}
                                    >
                                        <item.icon className="h-4 w-4 shrink-0" />
                                        {!collapsed && <span>{item.label}</span>}
                                    </Link>
                                ))}
                            </div>
                        )}
                    </div>
                ))}
            </nav>
        </aside>
    )
}

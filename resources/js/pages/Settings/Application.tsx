import { Head, Link } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Sun, Moon, Monitor } from 'lucide-react'

const dateFormats = [
    { value: 'm/d/Y', label: 'MM/DD/YYYY' },
    { value: 'd/m/Y', label: 'DD/MM/YYYY' },
    { value: 'Y-m-d', label: 'YYYY-MM-DD' },
    { value: 'F j, Y', label: 'Month Day, Year' },
]

const currencies = [
    { value: 'MKD', label: 'MKD' },
    { value: 'USD', label: 'USD ($)' },
    { value: 'EUR', label: 'EUR' },
    { value: 'GBP', label: 'GBP' },
    { value: 'JPY', label: 'JPY' },
    { value: 'CAD', label: 'CAD (C$)' },
]

const itemsPerPageOptions = ['10', '25', '50', '100']

const timezones = [
    { value: 'America/New_York', label: 'Eastern Time (UTC-5)' },
    { value: 'America/Chicago', label: 'Central Time (UTC-6)' },
    { value: 'America/Denver', label: 'Mountain Time (UTC-7)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (UTC-8)' },
    { value: 'UTC', label: 'UTC (UTC+0)' },
    { value: 'Europe/London', label: 'London (UTC+0)' },
    { value: 'Europe/Paris', label: 'Paris (UTC+1)' },
    { value: 'Europe/Skopje', label: 'Skopje (UTC+1)' },
    { value: 'Asia/Tokyo', label: 'Tokyo (UTC+9)' },
]

const dashboardWidgets = [
    { key: 'dash_quick_stats', label: 'Quick Stats Overview', defaultChecked: true },
    { key: 'dash_recent_notifications', label: 'Recent Notifications', defaultChecked: true },
    { key: 'dash_subscription_summary', label: 'Subscription Summary', defaultChecked: true },
    { key: 'dash_upcoming_renewals', label: 'Upcoming Renewals', defaultChecked: true },
    { key: 'dash_investment_performance', label: 'Investment Performance Chart', defaultChecked: false },
    { key: 'dash_monthly_expense_breakdown', label: 'Monthly Expense Breakdown', defaultChecked: false },
]

const accessibilityOptions = [
    { key: 'acc_high_contrast', label: 'High contrast mode', defaultChecked: false },
    { key: 'acc_larger_text', label: 'Larger text size', defaultChecked: false },
    { key: 'acc_reduce_motion', label: 'Reduce motion effects', defaultChecked: false },
    { key: 'acc_keyboard_navigation', label: 'Keyboard navigation support', defaultChecked: true },
]

type ThemeOption = 'light' | 'dark' | 'system'

function getStoredPreferences<T>(key: string, defaults: T): T {
    if (typeof window === 'undefined') return defaults
    try {
        const stored = localStorage.getItem(key)
        return stored ? { ...defaults, ...JSON.parse(stored) } : defaults
    } catch {
        return defaults
    }
}

export default function SettingsApplication() {
    const [theme, setTheme] = useState<ThemeOption>(() => {
        if (typeof window === 'undefined') return 'system'
        return (localStorage.getItem('color-theme') as ThemeOption) ?? 'system'
    })

    const [displayPrefs, setDisplayPrefs] = useState(() =>
        getStoredPreferences('display_preferences', {
            date_format: 'm/d/Y',
            currency_format: 'MKD',
            items_per_page: '25',
            timezone: 'Europe/Skopje',
        })
    )

    const [dashPrefs, setDashPrefs] = useState<Record<string, boolean>>(() =>
        getStoredPreferences(
            'dashboard_preferences',
            Object.fromEntries(dashboardWidgets.map(w => [w.key, w.defaultChecked]))
        )
    )

    const [accPrefs, setAccPrefs] = useState<Record<string, boolean>>(() =>
        getStoredPreferences(
            'accessibility_preferences',
            Object.fromEntries(accessibilityOptions.map(o => [o.key, o.defaultChecked]))
        )
    )

    const [saved, setSaved] = useState<string | null>(null)

    const showSaved = useCallback((message: string) => {
        setSaved(message)
        setTimeout(() => setSaved(null), 3000)
    }, [])

    function handleThemeChange(newTheme: ThemeOption) {
        setTheme(newTheme)
        if (newTheme === 'system') {
            localStorage.removeItem('color-theme')
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        } else if (newTheme === 'dark') {
            localStorage.setItem('color-theme', 'dark')
            document.documentElement.classList.add('dark')
        } else {
            localStorage.setItem('color-theme', 'light')
            document.documentElement.classList.remove('dark')
        }
        showSaved('Theme updated successfully!')
    }

    function saveDisplayPreferences() {
        localStorage.setItem('display_preferences', JSON.stringify(displayPrefs))
        showSaved('Display preferences saved successfully!')
    }

    function saveDashboardPreferences() {
        localStorage.setItem('dashboard_preferences', JSON.stringify(dashPrefs))
        showSaved('Dashboard layout saved successfully!')
    }

    function saveAccessibilityPreferences() {
        localStorage.setItem('accessibility_preferences', JSON.stringify(accPrefs))
        showSaved('Accessibility settings saved successfully!')
    }

    const themeOptions: { value: ThemeOption; label: string; icon: typeof Sun; description: string }[] = [
        { value: 'light', label: 'Light Mode', icon: Sun, description: 'Clean and bright interface' },
        { value: 'dark', label: 'Dark Mode', icon: Moon, description: 'Easy on the eyes' },
        { value: 'system', label: 'System', icon: Monitor, description: 'Match system preferences' },
    ]

    return (
        <AppLayout>
            <Head title="Application Settings" />

            <PageHeader title="Application Settings" description="Customize your LifeOS experience and interface preferences">
                <Button variant="outline" asChild>
                    <Link href="/settings">Back to Settings</Link>
                </Button>
            </PageHeader>

            {saved && (
                <div className="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">
                    {saved}
                </div>
            )}

            <div className="space-y-6">
                {/* Theme & Appearance */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-1 text-lg font-medium">Theme & Appearance</h3>
                        <p className="mb-4 text-sm text-muted-foreground">Choose your preferred theme and visual settings</p>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            {themeOptions.map((opt) => {
                                const Icon = opt.icon
                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() => handleThemeChange(opt.value)}
                                        className={`rounded-lg border-2 p-4 text-left transition-colors ${
                                            theme === opt.value
                                                ? 'border-primary bg-primary/5'
                                                : 'border-border hover:border-primary/50'
                                        }`}
                                    >
                                        <div className="mb-2 flex items-center gap-2">
                                            <Icon className="h-4 w-4" />
                                            <span className="text-sm font-medium">{opt.label}</span>
                                        </div>
                                        <p className="text-xs text-muted-foreground">{opt.description}</p>
                                    </button>
                                )
                            })}
                        </div>

                        <div className="mt-4 rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950">
                            <p className="text-sm text-blue-800 dark:text-blue-200">
                                Theme changes are applied immediately and saved automatically. Your preference will be remembered across sessions.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                {/* Display Preferences */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-1 text-lg font-medium">Display Preferences</h3>
                        <p className="mb-4 text-sm text-muted-foreground">Customize how information is displayed</p>

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <Label>Date Format</Label>
                                <Select
                                    value={displayPrefs.date_format}
                                    onValueChange={v => setDisplayPrefs(p => ({ ...p, date_format: v }))}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {dateFormats.map(f => (
                                            <SelectItem key={f.value} value={f.value}>{f.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Currency Format</Label>
                                <Select
                                    value={displayPrefs.currency_format}
                                    onValueChange={v => setDisplayPrefs(p => ({ ...p, currency_format: v }))}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {currencies.map(c => (
                                            <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Items Per Page</Label>
                                <Select
                                    value={displayPrefs.items_per_page}
                                    onValueChange={v => setDisplayPrefs(p => ({ ...p, items_per_page: v }))}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {itemsPerPageOptions.map(v => (
                                            <SelectItem key={v} value={v}>{v} items</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Timezone</Label>
                                <Select
                                    value={displayPrefs.timezone}
                                    onValueChange={v => setDisplayPrefs(p => ({ ...p, timezone: v }))}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {timezones.map(tz => (
                                            <SelectItem key={tz.value} value={tz.value}>{tz.label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="mt-6 flex justify-end">
                            <Button onClick={saveDisplayPreferences}>Save Preferences</Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Dashboard Customization */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-1 text-lg font-medium">Dashboard Customization</h3>
                        <p className="mb-4 text-sm text-muted-foreground">Choose which widgets to display on your dashboard</p>

                        <div className="space-y-3">
                            {dashboardWidgets.map(widget => (
                                <div key={widget.key} className="flex items-center space-x-2">
                                    <Checkbox
                                        id={widget.key}
                                        checked={dashPrefs[widget.key] ?? widget.defaultChecked}
                                        onCheckedChange={checked => setDashPrefs(p => ({ ...p, [widget.key]: !!checked }))}
                                    />
                                    <Label htmlFor={widget.key} className="cursor-pointer">{widget.label}</Label>
                                </div>
                            ))}
                        </div>

                        <div className="mt-6 flex justify-end">
                            <Button onClick={saveDashboardPreferences}>Save Dashboard Layout</Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Accessibility */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-1 text-lg font-medium">Accessibility Options</h3>
                        <p className="mb-4 text-sm text-muted-foreground">Enhance usability and accessibility</p>

                        <div className="space-y-3">
                            {accessibilityOptions.map(option => (
                                <div key={option.key} className="flex items-center space-x-2">
                                    <Checkbox
                                        id={option.key}
                                        checked={accPrefs[option.key] ?? option.defaultChecked}
                                        onCheckedChange={checked => setAccPrefs(p => ({ ...p, [option.key]: !!checked }))}
                                    />
                                    <Label htmlFor={option.key} className="cursor-pointer">{option.label}</Label>
                                </div>
                            ))}
                        </div>

                        <div className="mt-6 flex justify-end">
                            <Button onClick={saveAccessibilityPreferences}>Save Accessibility Settings</Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}

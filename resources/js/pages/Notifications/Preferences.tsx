import { Head, Link, router, useForm } from '@inertiajs/react'
import { useState, useCallback, type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { RefreshCw, FileText, Shield, Receipt } from 'lucide-react'

interface NotificationPreference {
    notification_type: string
    email_enabled: boolean
    database_enabled: boolean
    push_enabled: boolean
    settings: {
        days_before?: number[]
    } | null
}

interface PreferencesProps {
    preferences: Record<string, NotificationPreference>
}

interface PreferenceSection {
    type: string
    title: string
    description: string
    icon: typeof RefreshCw
    daysBefore: number[]
    dayLabel: string
}

const sections: PreferenceSection[] = [
    {
        type: 'subscription_renewal',
        title: 'Subscription Renewals',
        description: 'Get notified about upcoming subscription renewals',
        icon: RefreshCw,
        daysBefore: [30, 14, 7, 3, 1, 0],
        dayLabel: 'days before renewal',
    },
    {
        type: 'contract_expiration',
        title: 'Contract Expirations',
        description: 'Get alerts about expiring contracts',
        icon: FileText,
        daysBefore: [60, 30, 14, 7, 3, 1],
        dayLabel: 'days before expiration',
    },
    {
        type: 'warranty_expiration',
        title: 'Warranty Expirations',
        description: 'Get reminded about expiring warranties',
        icon: Shield,
        daysBefore: [60, 30, 14, 7, 3, 1],
        dayLabel: 'days before expiration',
    },
    {
        type: 'utility_bill_due',
        title: 'Utility Bill Due Dates',
        description: 'Get reminded about upcoming bill payments',
        icon: Receipt,
        daysBefore: [14, 7, 5, 3, 1, 0],
        dayLabel: 'days before due date',
    },
]

const defaultPreference: Omit<NotificationPreference, 'notification_type'> = {
    email_enabled: true,
    database_enabled: true,
    push_enabled: false,
    settings: { days_before: [7, 3, 1] },
}

export default function NotificationPreferences({ preferences }: PreferencesProps) {
    const [localPrefs, setLocalPrefs] = useState<Record<string, NotificationPreference>>(() => {
        const result: Record<string, NotificationPreference> = {}
        for (const section of sections) {
            result[section.type] = preferences[section.type] ?? {
                notification_type: section.type,
                ...defaultPreference,
            }
        }
        return result
    })

    const [saving, setSaving] = useState(false)
    const [saved, setSaved] = useState(false)

    const toggleChannel = useCallback((type: string, channel: 'email_enabled' | 'database_enabled' | 'push_enabled') => {
        setLocalPrefs(prev => ({
            ...prev,
            [type]: {
                ...prev[type],
                [channel]: !prev[type][channel],
            },
        }))
    }, [])

    const toggleDay = useCallback((type: string, day: number) => {
        setLocalPrefs(prev => {
            const current = prev[type].settings?.days_before ?? []
            const updated = current.includes(day)
                ? current.filter(d => d !== day)
                : [...current, day]
            return {
                ...prev,
                [type]: {
                    ...prev[type],
                    settings: { ...prev[type].settings, days_before: updated },
                },
            }
        })
    }, [])

    const handleSubmit = useCallback((e: FormEvent) => {
        e.preventDefault()
        setSaving(true)

        const preferencesPayload: Record<string, Record<string, unknown>> = {}
        for (const [type, pref] of Object.entries(localPrefs)) {
            preferencesPayload[type] = {
                email_enabled: pref.email_enabled,
                database_enabled: pref.database_enabled,
                push_enabled: pref.push_enabled,
                days_before: pref.settings?.days_before ?? [],
            }
        }

        router.post('/notifications/preferences', {
            preferences: preferencesPayload as unknown as Record<string, string>,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setSaved(true)
                setTimeout(() => setSaved(false), 3000)
            },
            onFinish: () => setSaving(false),
        })
    }, [localPrefs])

    return (
        <AppLayout>
            <Head title="Notification Preferences" />

            <PageHeader title="Notification Preferences" description="Customize how you receive notifications for important events">
                <Button variant="outline" asChild>
                    <Link href="/notifications">Back to Notifications</Link>
                </Button>
            </PageHeader>

            {saved && (
                <div className="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">
                    Notification preferences updated successfully.
                </div>
            )}

            <form onSubmit={handleSubmit}>
                <Card>
                    <CardContent className="p-6">
                        <div className="space-y-8">
                            {sections.map((section, index) => {
                                const Icon = section.icon
                                const pref = localPrefs[section.type]
                                const selectedDays = pref?.settings?.days_before ?? []

                                return (
                                    <div key={section.type}>
                                        {index > 0 && <Separator className="mb-8" />}

                                        <div className="mb-4 flex items-center">
                                            <div className="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                                <Icon className="h-5 w-5" />
                                            </div>
                                            <div>
                                                <h3 className="text-lg font-medium">{section.title}</h3>
                                                <p className="text-sm text-muted-foreground">{section.description}</p>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                            <div>
                                                <h4 className="mb-3 text-sm font-medium">Notification Channels</h4>
                                                <div className="space-y-3">
                                                    <div className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`${section.type}_email`}
                                                            checked={pref?.email_enabled ?? true}
                                                            onCheckedChange={() => toggleChannel(section.type, 'email_enabled')}
                                                        />
                                                        <Label htmlFor={`${section.type}_email`} className="cursor-pointer">
                                                            Email notifications
                                                        </Label>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`${section.type}_database`}
                                                            checked={pref?.database_enabled ?? true}
                                                            onCheckedChange={() => toggleChannel(section.type, 'database_enabled')}
                                                        />
                                                        <Label htmlFor={`${section.type}_database`} className="cursor-pointer">
                                                            In-app notifications
                                                        </Label>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`${section.type}_push`}
                                                            checked={pref?.push_enabled ?? false}
                                                            onCheckedChange={() => toggleChannel(section.type, 'push_enabled')}
                                                        />
                                                        <Label htmlFor={`${section.type}_push`} className="cursor-pointer">
                                                            Push notifications
                                                        </Label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <h4 className="mb-3 text-sm font-medium">
                                                    Notification Timing ({section.dayLabel})
                                                </h4>
                                                <div className="grid grid-cols-2 gap-2">
                                                    {section.daysBefore.map(day => (
                                                        <div key={day} className="flex items-center space-x-2">
                                                            <Checkbox
                                                                id={`${section.type}_day_${day}`}
                                                                checked={selectedDays.includes(day)}
                                                                onCheckedChange={() => toggleDay(section.type, day)}
                                                            />
                                                            <Label htmlFor={`${section.type}_day_${day}`} className="cursor-pointer text-sm">
                                                                {day === 0 ? 'On the day' : `${day} days before`}
                                                            </Label>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )
                            })}
                        </div>

                        <div className="mt-8 flex justify-end border-t pt-6">
                            <Button type="submit" disabled={saving}>
                                {saving ? 'Saving...' : 'Save Preferences'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    )
}

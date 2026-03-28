import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { User, Settings, Bell, Shield, Database, Link2 } from 'lucide-react'

export default function SettingsIndex() {
    const settingsCards = [
        {
            title: 'Account Settings',
            description: 'Update your personal information, email, and password',
            href: '/settings/account',
            icon: User,
            enabled: true,
        },
        {
            title: 'Application Settings',
            description: 'Customize theme, language, and display preferences',
            href: '/settings/application',
            icon: Settings,
            enabled: true,
        },
        {
            title: 'Notification Preferences',
            description: 'Configure how you receive alerts and reminders',
            href: '/notifications/preferences',
            icon: Bell,
            enabled: true,
        },
        {
            title: 'Privacy & Security',
            description: 'Manage data privacy and security settings',
            href: '#',
            icon: Shield,
            enabled: false,
        },
        {
            title: 'Data Management',
            description: 'Export, import, and backup your data',
            href: '#',
            icon: Database,
            enabled: false,
        },
        {
            title: 'API & Integrations',
            description: 'Manage API keys and third-party connections',
            href: '#',
            icon: Link2,
            enabled: false,
        },
    ]

    return (
        <AppLayout>
            <Head title="Settings" />

            <PageHeader title="Settings" description="Manage your account, application preferences, and notifications" />

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {settingsCards.map((card) => {
                    const Icon = card.icon
                    const content = (
                        <Card
                            key={card.title}
                            className={!card.enabled ? 'opacity-50' : 'transition-shadow hover:shadow-md'}
                        >
                            <CardContent className="p-6">
                                <div className="flex items-center">
                                    <div className="mr-4 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                        <Icon className="h-6 w-6" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-medium">{card.title}</h3>
                                        <p className="text-sm text-muted-foreground">{card.description}</p>
                                        {!card.enabled && (
                                            <p className="mt-1 text-xs text-muted-foreground">Coming Soon</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )

                    if (card.enabled) {
                        return (
                            <Link key={card.title} href={card.href} className="block">
                                {content}
                            </Link>
                        )
                    }

                    return <div key={card.title}>{content}</div>
                })}
            </div>

            <Card className="mt-8">
                <CardContent className="p-6">
                    <h3 className="mb-4 text-lg font-medium">Quick Actions</h3>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Button variant="default" asChild>
                            <Link href="/profile/edit">Edit Profile</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/notifications/preferences">Notifications</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/settings/application">Theme Settings</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/">Back to Dashboard</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

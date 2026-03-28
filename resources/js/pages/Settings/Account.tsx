import { Head, Link, useForm, usePage } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import type { SharedProps } from '@/types'

interface AccountStats {
    member_since: string
    subscriptions_count: number
    contracts_count: number
    notifications_count: number
    created_at: string
    updated_at: string
}

interface AccountProps {
    stats: AccountStats
}

export default function SettingsAccount({ stats }: AccountProps) {
    const { auth } = usePage<SharedProps>().props
    const user = auth.user!

    const profileForm = useForm({
        name: user.name,
        email: user.email,
    })

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    })

    function handleProfileSubmit(e: FormEvent) {
        e.preventDefault()
        profileForm.patch('/profile')
    }

    function handlePasswordSubmit(e: FormEvent) {
        e.preventDefault()
        passwordForm.patch('/profile/password', {
            onSuccess: () => passwordForm.reset(),
        })
    }

    return (
        <AppLayout>
            <Head title="Account Settings" />

            <PageHeader title="Account Settings" description="Manage your personal information and account security">
                <Button variant="outline" asChild>
                    <Link href="/settings">Back to Settings</Link>
                </Button>
            </PageHeader>

            <div className="space-y-6">
                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={handleProfileSubmit} className="space-y-6">
                            <FormSection title="Personal Information" description="Update your name and email address">
                                <FormField
                                    label="Full Name"
                                    name="name"
                                    value={profileForm.data.name}
                                    onChange={e => profileForm.setData('name', e.target.value)}
                                    error={profileForm.errors.name}
                                    required
                                />
                                <FormField
                                    label="Email Address"
                                    name="email"
                                    type="email"
                                    value={profileForm.data.email}
                                    onChange={e => profileForm.setData('email', e.target.value)}
                                    error={profileForm.errors.email}
                                    required
                                />
                            </FormSection>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={profileForm.processing}>
                                    Update Information
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={handlePasswordSubmit} className="space-y-6">
                            <FormSection title="Password Security" description="Change your account password">
                                <FormField
                                    label="Current Password"
                                    name="current_password"
                                    type="password"
                                    value={passwordForm.data.current_password}
                                    onChange={e => passwordForm.setData('current_password', e.target.value)}
                                    error={passwordForm.errors.current_password}
                                    required
                                />
                                <div />
                                <FormField
                                    label="New Password"
                                    name="password"
                                    type="password"
                                    value={passwordForm.data.password}
                                    onChange={e => passwordForm.setData('password', e.target.value)}
                                    error={passwordForm.errors.password}
                                    required
                                />
                                <FormField
                                    label="Confirm New Password"
                                    name="password_confirmation"
                                    type="password"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={e => passwordForm.setData('password_confirmation', e.target.value)}
                                    error={passwordForm.errors.password_confirmation}
                                    required
                                />
                            </FormSection>

                            <div className="rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950">
                                <p className="text-sm font-medium text-yellow-800 dark:text-yellow-200">Password Requirements:</p>
                                <ul className="mt-1 list-inside list-disc text-sm text-yellow-700 dark:text-yellow-300">
                                    <li>At least 8 characters long</li>
                                    <li>Include uppercase and lowercase letters</li>
                                    <li>Include at least one number</li>
                                    <li>Include at least one special character</li>
                                </ul>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={passwordForm.processing}>
                                    Update Password
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <h3 className="mb-4 text-lg font-medium">Account Overview</h3>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div className="rounded-lg bg-muted p-4 text-center">
                                <div className="text-2xl font-bold text-primary">{stats.member_since}</div>
                                <div className="text-sm text-muted-foreground">Member Since</div>
                            </div>
                            <div className="rounded-lg bg-muted p-4 text-center">
                                <div className="text-2xl font-bold text-primary">{stats.subscriptions_count}</div>
                                <div className="text-sm text-muted-foreground">Subscriptions</div>
                            </div>
                            <div className="rounded-lg bg-muted p-4 text-center">
                                <div className="text-2xl font-bold text-primary">{stats.contracts_count}</div>
                                <div className="text-sm text-muted-foreground">Contracts</div>
                            </div>
                            <div className="rounded-lg bg-muted p-4 text-center">
                                <div className="text-2xl font-bold text-primary">{stats.notifications_count}</div>
                                <div className="text-sm text-muted-foreground">Notifications</div>
                            </div>
                        </div>

                        <div className="mt-4 space-y-2 border-t pt-4 text-sm text-muted-foreground">
                            <div className="flex items-center justify-between">
                                <span>Account Created:</span>
                                <span>{stats.created_at}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span>Last Updated:</span>
                                <span>{stats.updated_at}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}

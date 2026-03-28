import { Head, Link, useForm, usePage } from '@inertiajs/react'
import { type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { FormField } from '@/components/shared/form-field'
import { FormSection } from '@/components/shared/form-section'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import type { SharedProps } from '@/types'

export default function ProfileEdit() {
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
            <Head title="Edit Profile" />

            <PageHeader title="Edit Profile" description="Update your personal information and password">
                <Button variant="outline" asChild>
                    <Link href="/profile">Back to Profile</Link>
                </Button>
            </PageHeader>

            <div className="space-y-6">
                {/* Profile Information */}
                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={handleProfileSubmit} className="space-y-6">
                            <FormSection title="Profile Information" description="Update your name and email address">
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
                                    {profileForm.processing ? 'Saving...' : 'Save Changes'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Change Password */}
                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={handlePasswordSubmit} className="space-y-6">
                            <FormSection title="Change Password" description="Ensure your account is using a secure password">
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

                            <div className="flex justify-end">
                                <Button type="submit" disabled={passwordForm.processing}>
                                    {passwordForm.processing ? 'Updating...' : 'Update Password'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}

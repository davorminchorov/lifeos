import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import type { User } from '@/types/models'
import { formatDate } from '@/lib/utils'

interface ProfileShowProps {
    user: User
}

export default function ProfileShow({ user }: ProfileShowProps) {
    return (
        <AppLayout>
            <Head title="Profile" />

            <PageHeader title="Profile" description="Manage your account settings and personal information">
                <Button asChild>
                    <Link href="/profile/edit">Edit Profile</Link>
                </Button>
            </PageHeader>

            <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                {/* Personal Information */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="text-lg font-medium">Personal Information</h3>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Your basic account information and contact details.
                        </p>

                        <dl className="divide-y divide-border">
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Full Name</dt>
                                <dd className="text-sm">{user.name}</dd>
                            </div>
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Email Address</dt>
                                <dd className="text-sm">{user.email}</dd>
                            </div>
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Email Verified</dt>
                                <dd className="text-sm">
                                    {user.email_verified_at ? (
                                        <span className="text-green-600 dark:text-green-400">Verified</span>
                                    ) : (
                                        <span className="text-yellow-600 dark:text-yellow-400">Not verified</span>
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                {/* Account Details */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="text-lg font-medium">Account Details</h3>
                        <p className="mb-4 text-sm text-muted-foreground">
                            Account timestamps and membership information.
                        </p>

                        <dl className="divide-y divide-border">
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Account Created</dt>
                                <dd className="text-sm">{formatDate(user.created_at)}</dd>
                            </div>
                            <div className="flex justify-between py-3">
                                <dt className="text-sm font-medium text-muted-foreground">Last Updated</dt>
                                <dd className="text-sm">{formatDate(user.updated_at)}</dd>
                            </div>
                            {user.current_tenant && (
                                <div className="flex justify-between py-3">
                                    <dt className="text-sm font-medium text-muted-foreground">Current Workspace</dt>
                                    <dd className="text-sm">{user.current_tenant.name}</dd>
                                </div>
                            )}
                        </dl>
                    </CardContent>
                </Card>
            </div>

            <div className="mt-6 flex flex-wrap justify-center gap-4">
                <Button variant="outline" asChild>
                    <Link href="/settings/account">Account Settings</Link>
                </Button>
                <Button variant="outline" asChild>
                    <Link href="/notifications/preferences">Notification Settings</Link>
                </Button>
                <Button variant="outline" asChild>
                    <Link href="/">Back to Dashboard</Link>
                </Button>
            </div>
        </AppLayout>
    )
}

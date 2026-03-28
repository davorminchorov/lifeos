import { Head, useForm } from '@inertiajs/react'
import GuestLayout from '@/components/shared/guest-layout'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { type FormEvent } from 'react'

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    })

    function handleSubmit(e: FormEvent) {
        e.preventDefault()
        post('/login')
    }

    return (
        <GuestLayout>
            <Head title="Sign In" />
            <Card>
                <CardHeader className="text-center">
                    <CardTitle className="text-xl">Sign in to your account</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                placeholder="you@example.com"
                                autoComplete="email"
                                autoFocus
                            />
                            {errors.email ? <p className="text-sm text-destructive">{errors.email}</p> : null}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={e => setData('password', e.target.value)}
                                autoComplete="current-password"
                            />
                            {errors.password ? <p className="text-sm text-destructive">{errors.password}</p> : null}
                        </div>

                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                checked={data.remember}
                                onCheckedChange={(checked) => setData('remember', checked === true)}
                            />
                            <Label htmlFor="remember" className="text-sm font-normal">
                                Remember me
                            </Label>
                        </div>

                        <Button type="submit" className="w-full" disabled={processing}>
                            {processing ? 'Signing in...' : 'Sign in'}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </GuestLayout>
    )
}

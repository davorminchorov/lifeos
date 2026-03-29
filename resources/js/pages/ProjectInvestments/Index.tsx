import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Briefcase, Plus, Search, MoreHorizontal, Eye, Pencil, Trash2, BarChart3 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { ProjectInvestment } from '@/types/models'
import type { PaginatedData } from '@/types'

interface ProjectInvestmentWithTotals extends ProjectInvestment {
    total_invested?: number
}

interface ProjectInvestmentIndexProps {
    projectInvestments: PaginatedData<ProjectInvestmentWithTotals>
    summary: {
        total_projects: number
        active_projects: number
        total_invested: number
        total_current_value: number
        total_gain_loss: number
    }
    filters?: {
        search?: string
        stage?: string
        business_model?: string
        status?: string
    }
}

const stages = [
    { value: 'idea', label: 'Idea' },
    { value: 'prototype', label: 'Prototype' },
    { value: 'mvp', label: 'MVP' },
    { value: 'growth', label: 'Growth' },
    { value: 'mature', label: 'Mature' },
]

const businessModels = [
    { value: 'subscription', label: 'Subscription' },
    { value: 'ads', label: 'Ads' },
    { value: 'one-time', label: 'One-time' },
    { value: 'freemium', label: 'Freemium' },
]

const statuses = [
    { value: 'active', label: 'Active' },
    { value: 'completed', label: 'Completed' },
    { value: 'sold', label: 'Sold' },
    { value: 'abandoned', label: 'Abandoned' },
]

export default function ProjectInvestmentIndex({ projectInvestments, summary, filters = {} }: ProjectInvestmentIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/project-investments', { ...filters, [key]: value || undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [filters])

    const handleSearch = useCallback((e: ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value)
    }, [])

    const handleSearchSubmit = useCallback(() => {
        applyFilter('search', search)
    }, [search, applyFilter])

    const clearFilters = useCallback(() => {
        router.get('/project-investments', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (!confirmDelete) return
        router.delete(`/project-investments/${confirmDelete}`, {
            onFinish: () => setConfirmDelete(null),
        })
    }, [confirmDelete])

    return (
        <AppLayout>
            <Head title="Project Investments" />

            <PageHeader title="Project Investments" description="Track your project investments and returns">
                <Button variant="outline" asChild>
                    <Link href="/project-investments/analytics">
                        <BarChart3 className="mr-2 h-4 w-4" />
                        Analytics
                    </Link>
                </Button>
                <Button asChild>
                    <Link href="/project-investments/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Investment
                    </Link>
                </Button>
            </PageHeader>

            {summary.total_projects > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Projects</p>
                            <p className="text-xl font-semibold">{summary.total_projects}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Active</p>
                            <p className="text-xl font-semibold">{summary.active_projects}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Invested</p>
                            <p className="text-xl font-semibold">{formatCurrency(summary.total_invested, 'USD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Gain / Loss</p>
                            <p className={`text-xl font-semibold ${summary.total_gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                {summary.total_gain_loss >= 0 ? '+' : ''}{formatCurrency(summary.total_gain_loss, 'USD')}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search investments..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.stage ?? '__all__'} onValueChange={(v) => applyFilter('stage', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Stage" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {stages.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.business_model ?? '__all__'} onValueChange={(v) => applyFilter('business_model', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Business Model" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {businessModels.map(b => (
                            <SelectItem key={b.value} value={b.value}>{b.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {statuses.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {projectInvestments.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Stage</TableHead>
                                    <TableHead>Business Model</TableHead>
                                    <TableHead>Current Value</TableHead>
                                    <TableHead>Start Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {projectInvestments.data.map((investment) => (
                                    <TableRow key={investment.id}>
                                        <TableCell>
                                            <Link href={`/project-investments/${investment.id}`} className="font-medium hover:underline">
                                                {investment.name}
                                            </Link>
                                            {investment.project_type ? (
                                                <p className="text-xs text-muted-foreground">{investment.project_type}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm capitalize text-muted-foreground">
                                            {investment.stage ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm capitalize text-muted-foreground">
                                            {investment.business_model ?? '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            {investment.current_value != null
                                                ? formatCurrency(investment.current_value, 'USD')
                                                : '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {investment.start_date ? formatDate(investment.start_date) : '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={investment.status} />
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="h-8 w-8">
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/project-investments/${investment.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/project-investments/${investment.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setConfirmDelete(investment.id)}
                                                        className="text-destructive"
                                                    >
                                                        <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {projectInvestments.data.map((investment) => (
                            <Card key={investment.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/project-investments/${investment.id}`} className="font-medium hover:underline">
                                                {investment.name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground capitalize">{investment.stage ?? 'No stage'}</p>
                                        </div>
                                        <StatusBadge status={investment.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">
                                            {investment.current_value != null ? formatCurrency(investment.current_value, 'USD') : '\u2014'}
                                        </span>
                                        {investment.start_date ? (
                                            <span className="text-muted-foreground">{formatDate(investment.start_date)}</span>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {projectInvestments.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {projectInvestments.from} to {projectInvestments.to} of {projectInvestments.total}
                            </p>
                            <div className="flex gap-2">
                                {projectInvestments.links.map((link, i) => (
                                    <Button
                                        key={i}
                                        variant={link.active ? 'default' : 'outline'}
                                        size="sm"
                                        disabled={!link.url}
                                        onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    ) : null}
                </>
            ) : (
                <EmptyState
                    icon={Briefcase}
                    title="No project investments yet"
                    description="Start tracking your project investments"
                    action={{ label: 'Add Investment', href: '/project-investments/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmDelete !== null}
                onOpenChange={(open) => { if (!open) setConfirmDelete(null) }}
                title="Delete Investment"
                description="Are you sure you want to delete this project investment? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

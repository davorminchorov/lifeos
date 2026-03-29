import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { StatCard } from '@/components/shared/stat-card'
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
import { Briefcase, Plus, Search, MoreHorizontal, Eye, Pencil, Archive, ArchiveRestore, Trash2, LayoutGrid } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { JobApplication } from '@/types/models'
import type { PaginatedData } from '@/types'
import { ApplicationStatus, ApplicationSource } from '@/types/enums'

interface JobApplicationIndexProps {
    applications: PaginatedData<JobApplication>
    filters?: {
        search?: string
        status?: string
        source?: string
        priority?: string
        remote?: string
        archived?: string
        date_from?: string
        date_to?: string
        sort_by?: string
        sort_order?: string
    }
}

const statusOptions = Object.entries(ApplicationStatus).map(([key, value]) => ({
    value,
    label: key.charAt(0) + key.slice(1).toLowerCase(),
}))

const sourceOptions = Object.entries(ApplicationSource).map(([key, value]) => ({
    value,
    label: key.split('_').map(w => w.charAt(0) + w.slice(1).toLowerCase()).join(' '),
}))

const priorityOptions = [
    { value: '1', label: '1 - Low' },
    { value: '2', label: '2 - Medium' },
    { value: '3', label: '3 - High' },
    { value: '4', label: '4 - Critical' },
    { value: '5', label: '5 - Urgent' },
]

export default function JobApplicationIndex({ applications, filters = {} }: JobApplicationIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/job-applications', { ...filters, [key]: value || undefined }, {
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
        router.get('/job-applications', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleArchive = useCallback((id: number) => {
        router.patch(`/job-applications/${id}/archive`, {}, { preserveScroll: true })
    }, [])

    const handleUnarchive = useCallback((id: number) => {
        router.patch(`/job-applications/${id}/unarchive`, {}, { preserveScroll: true })
    }, [])

    const handleDelete = useCallback(() => {
        if (confirmDelete === null) return
        router.delete(`/job-applications/${confirmDelete}`, {
            onFinish: () => setConfirmDelete(null),
        })
    }, [confirmDelete])

    const activeCount = applications.data.filter(a => !a.archived_at).length
    const interviewCount = applications.data.filter(a => a.status === ApplicationStatus.INTERVIEW).length
    const offerCount = applications.data.filter(a => a.status === ApplicationStatus.OFFER).length

    return (
        <AppLayout>
            <Head title="Job Applications" />

            <PageHeader title="Job Applications" description="Track your job search progress">
                <Button variant="outline" asChild>
                    <Link href="/job-applications/kanban">
                        <LayoutGrid className="mr-2 h-4 w-4" />
                        Kanban Board
                    </Link>
                </Button>
                <Button asChild>
                    <Link href="/job-applications/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Application
                    </Link>
                </Button>
            </PageHeader>

            {applications.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-4">
                    <StatCard label="Total Applications" value={applications.total} icon={Briefcase} />
                    <StatCard label="Active" value={activeCount} />
                    <StatCard label="In Interview" value={interviewCount} />
                    <StatCard label="Offers" value={offerCount} />
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search company or job title..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[140px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All Statuses</SelectItem>
                        {statusOptions.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.source ?? '__all__'} onValueChange={(v) => applyFilter('source', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[160px]">
                        <SelectValue placeholder="Source" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All Sources</SelectItem>
                        {sourceOptions.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.priority ?? '__all__'} onValueChange={(v) => applyFilter('priority', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Priority" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">Any Priority</SelectItem>
                        {priorityOptions.map(p => (
                            <SelectItem key={p.value} value={p.value}>{p.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {applications.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Company / Position</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Source</TableHead>
                                    <TableHead>Applied</TableHead>
                                    <TableHead>Salary Range</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {applications.data.map((app) => (
                                    <TableRow key={app.id}>
                                        <TableCell>
                                            <Link href={`/job-applications/${app.id}`} className="font-medium hover:underline">
                                                {app.company_name}
                                            </Link>
                                            <p className="text-xs text-muted-foreground">{app.job_title}</p>
                                            {app.remote ? (
                                                <span className="text-xs text-blue-600 dark:text-blue-400">Remote</span>
                                            ) : app.location ? (
                                                <span className="text-xs text-muted-foreground">{app.location}</span>
                                            ) : null}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={app.status} />
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {app.source ? app.source.replace(/_/g, ' ') : '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {app.applied_at ? formatDate(app.applied_at) : '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {app.salary_min || app.salary_max ? (
                                                <span>
                                                    {app.salary_min ? formatCurrency(app.salary_min, app.currency ?? 'USD') : ''}
                                                    {app.salary_min && app.salary_max ? ' - ' : ''}
                                                    {app.salary_max ? formatCurrency(app.salary_max, app.currency ?? 'USD') : ''}
                                                </span>
                                            ) : '\u2014'}
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
                                                        <Link href={`/job-applications/${app.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/job-applications/${app.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    {app.archived_at ? (
                                                        <DropdownMenuItem onClick={() => handleUnarchive(app.id)}>
                                                            <ArchiveRestore className="mr-2 h-4 w-4" /> Unarchive
                                                        </DropdownMenuItem>
                                                    ) : (
                                                        <DropdownMenuItem onClick={() => handleArchive(app.id)}>
                                                            <Archive className="mr-2 h-4 w-4" /> Archive
                                                        </DropdownMenuItem>
                                                    )}
                                                    <DropdownMenuItem
                                                        onClick={() => setConfirmDelete(app.id)}
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
                        {applications.data.map((app) => (
                            <Card key={app.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/job-applications/${app.id}`} className="font-medium hover:underline">
                                                {app.company_name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{app.job_title}</p>
                                        </div>
                                        <StatusBadge status={app.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        {app.applied_at ? (
                                            <span className="text-muted-foreground">Applied: {formatDate(app.applied_at)}</span>
                                        ) : null}
                                        {app.remote ? (
                                            <span className="text-blue-600 dark:text-blue-400">Remote</span>
                                        ) : app.location ? (
                                            <span className="text-muted-foreground">{app.location}</span>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {applications.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {applications.from} to {applications.to} of {applications.total}
                            </p>
                            <div className="flex gap-2">
                                {applications.links.map((link, i) => (
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
                    title="No job applications yet"
                    description="Start tracking your job search by adding your first application"
                    action={{ label: 'New Application', href: '/job-applications/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmDelete !== null}
                onOpenChange={(open) => { if (!open) setConfirmDelete(null) }}
                title="Delete Application"
                description="Are you sure you want to delete this job application? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

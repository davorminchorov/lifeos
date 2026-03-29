import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, useMemo, lazy, Suspense } from 'react'
import {
    DndContext,
    DragOverlay,
    closestCorners,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    type DragStartEvent,
    type DragEndEvent,
} from '@dnd-kit/core'
import {
    SortableContext,
    verticalListSortingStrategy,
    useSortable,
} from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { List, Plus, Search, GripVertical } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { JobApplication } from '@/types/models'

interface KanbanColumn {
    status: string
    label: string
    color: string
    applications: JobApplication[]
}

interface KanbanProps {
    columns: Record<string, KanbanColumn>
    applications: JobApplication[]
}

function KanbanCard({ application, isDragging }: { application: JobApplication; isDragging?: boolean }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging: isSortableDragging,
    } = useSortable({
        id: application.id,
        data: { status: application.status },
    })

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isSortableDragging ? 0.5 : 1,
    }

    return (
        <Card
            ref={setNodeRef}
            style={style}
            className={`cursor-grab active:cursor-grabbing ${isDragging ? 'shadow-lg ring-2 ring-primary' : ''}`}
        >
            <CardContent className="p-3">
                <div className="flex items-start gap-2">
                    <button
                        className="mt-0.5 flex-shrink-0 text-muted-foreground hover:text-foreground"
                        {...attributes}
                        {...listeners}
                    >
                        <GripVertical className="h-4 w-4" />
                    </button>
                    <div className="min-w-0 flex-1">
                        <Link
                            href={`/job-applications/${application.id}`}
                            className="text-sm font-medium hover:underline"
                        >
                            {application.company_name}
                        </Link>
                        <p className="truncate text-xs text-muted-foreground">{application.job_title}</p>
                        <div className="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                            {application.applied_at ? (
                                <span>{formatDate(application.applied_at)}</span>
                            ) : <span />}
                            {application.salary_min || application.salary_max ? (
                                <span className="font-medium text-foreground">
                                    {application.currency ?? 'USD'}
                                </span>
                            ) : null}
                        </div>
                        {application.remote ? (
                            <span className="mt-1 inline-block text-xs text-blue-600 dark:text-blue-400">Remote</span>
                        ) : application.location ? (
                            <span className="mt-1 inline-block truncate text-xs text-muted-foreground">{application.location}</span>
                        ) : null}
                    </div>
                </div>
            </CardContent>
        </Card>
    )
}

function KanbanColumnComponent({ column }: { column: KanbanColumn }) {
    const applicationIds = column.applications.map(a => a.id)

    return (
        <div className="flex min-h-[200px] w-72 flex-shrink-0 flex-col rounded-lg border border-border bg-muted/30">
            <div className="flex items-center justify-between border-b border-border p-3">
                <div className="flex items-center gap-2">
                    <h3 className="text-sm font-semibold">{column.label}</h3>
                    <span className="rounded-full bg-muted px-2 py-0.5 text-xs font-medium">
                        {column.applications.length}
                    </span>
                </div>
            </div>
            <div className="flex-1 space-y-2 overflow-y-auto p-2">
                <SortableContext items={applicationIds} strategy={verticalListSortingStrategy}>
                    {column.applications.map((app) => (
                        <KanbanCard key={app.id} application={app} />
                    ))}
                </SortableContext>
                {column.applications.length === 0 ? (
                    <p className="py-8 text-center text-xs text-muted-foreground">
                        No applications
                    </p>
                ) : null}
            </div>
        </div>
    )
}

export default function JobApplicationKanban({ columns: initialColumns }: KanbanProps) {
    const [columns, setColumns] = useState(initialColumns)
    const [activeId, setActiveId] = useState<number | null>(null)
    const [search, setSearch] = useState('')

    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 8 } }),
        useSensor(KeyboardSensor)
    )

    const allApplications = useMemo(() => {
        return Object.values(columns).flatMap(c => c.applications)
    }, [columns])

    const activeApplication = activeId !== null
        ? allApplications.find(a => a.id === activeId) ?? null
        : null

    const filteredColumns = useMemo(() => {
        if (!search) return columns

        const result: Record<string, KanbanColumn> = {}
        for (const [key, col] of Object.entries(columns)) {
            const lowerSearch = search.toLowerCase()
            result[key] = {
                ...col,
                applications: col.applications.filter(
                    a => a.company_name.toLowerCase().includes(lowerSearch) ||
                        a.job_title.toLowerCase().includes(lowerSearch)
                ),
            }
        }
        return result
    }, [columns, search])

    const handleDragStart = useCallback((event: DragStartEvent) => {
        setActiveId(event.active.id as number)
    }, [])

    const handleDragEnd = useCallback((event: DragEndEvent) => {
        const { active, over } = event
        setActiveId(null)

        if (!over) return

        const activeAppId = active.id as number
        const overData = over.data.current

        // Determine target column
        let targetStatus: string | null = null

        if (overData?.status) {
            // Dropped on another card - use that card's status
            targetStatus = overData.status as string
        } else if (typeof over.id === 'string' && columns[over.id]) {
            // Dropped on a column container
            targetStatus = over.id
        }

        if (!targetStatus) return

        // Find current column of the dragged card
        let sourceStatus: string | null = null
        for (const [status, col] of Object.entries(columns)) {
            if (col.applications.find(a => a.id === activeAppId)) {
                sourceStatus = status
                break
            }
        }

        if (!sourceStatus || sourceStatus === targetStatus) return

        // Optimistically update local state
        setColumns(prev => {
            const updated = { ...prev }
            const sourceCol = { ...updated[sourceStatus] }
            const targetCol = { ...updated[targetStatus] }

            const appIndex = sourceCol.applications.findIndex(a => a.id === activeAppId)
            if (appIndex === -1) return prev

            const [movedApp] = sourceCol.applications.splice(appIndex, 1)
            const updatedApp = { ...movedApp, status: targetStatus as JobApplication['status'] }
            targetCol.applications = [...targetCol.applications, updatedApp]

            updated[sourceStatus] = { ...sourceCol, applications: [...sourceCol.applications] }
            updated[targetStatus] = targetCol

            return updated
        })

        // Send PATCH to server
        router.patch(`/job-applications/${activeAppId}/kanban/status`, {
            status: targetStatus,
        }, {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                // Revert on error
                setColumns(initialColumns)
            },
        })
    }, [columns, initialColumns])

    return (
        <AppLayout>
            <Head title="Job Applications - Kanban" />

            <PageHeader title="Kanban Board" description="Drag and drop applications between stages">
                <Button variant="outline" asChild>
                    <Link href="/job-applications">
                        <List className="mr-2 h-4 w-4" />
                        List View
                    </Link>
                </Button>
                <Button asChild>
                    <Link href="/job-applications/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Application
                    </Link>
                </Button>
            </PageHeader>

            <div className="mb-4">
                <div className="relative sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Filter cards..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="pl-9"
                    />
                </div>
            </div>

            <div className="overflow-x-auto pb-4">
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCorners}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEnd}
                >
                    <div className="flex gap-4">
                        {Object.entries(filteredColumns).map(([status, column]) => (
                            <KanbanColumnComponent key={status} column={column} />
                        ))}
                    </div>
                    <DragOverlay>
                        {activeApplication ? (
                            <div className="w-72">
                                <KanbanCard application={activeApplication} isDragging />
                            </div>
                        ) : null}
                    </DragOverlay>
                </DndContext>
            </div>
        </AppLayout>
    )
}

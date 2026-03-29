import { Head, Link, router, useForm } from '@inertiajs/react'
import { useState, useCallback, type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { FormField } from '@/components/shared/form-field'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Pencil, Trash2, ArrowLeft, Plus, GripVertical, X } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import { MealType } from '@/types/enums'
import type { CycleMenu, CycleMenuDay, CycleMenuItem } from '@/types/models'
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
    type DragEndEvent,
} from '@dnd-kit/core'
import {
    SortableContext,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'

interface CycleMenuShowProps {
    menu: CycleMenu & { days: (CycleMenuDay & { items: CycleMenuItem[] })[] }
    daysByIndex: Record<number, CycleMenuDay & { items: CycleMenuItem[] }>
}

const mealTypeLabels: Record<string, string> = {
    breakfast: 'Breakfast',
    lunch: 'Lunch',
    dinner: 'Dinner',
    snack: 'Snack',
    other: 'Other',
}

function SortableItem({ item, onRemove }: { item: CycleMenuItem; onRemove: (id: number) => void }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id: item.id })

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    }

    return (
        <div
            ref={setNodeRef}
            style={style}
            className="flex items-center gap-3 rounded-md border border-border bg-card px-3 py-2"
        >
            <button
                type="button"
                className="shrink-0 cursor-grab text-muted-foreground hover:text-foreground"
                {...attributes}
                {...listeners}
            >
                <GripVertical className="h-4 w-4" />
            </button>
            <div className="flex-1">
                <div className="text-sm font-medium">{item.title}</div>
                <div className="text-xs text-muted-foreground">
                    {mealTypeLabels[item.meal_type] ?? item.meal_type}
                    {item.time_of_day ? ` \u2022 ${item.time_of_day}` : ''}
                    {item.quantity ? ` \u2022 ${item.quantity}` : ''}
                </div>
            </div>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="h-7 w-7 text-destructive hover:text-destructive"
                onClick={() => onRemove(item.id)}
            >
                <X className="h-4 w-4" />
            </Button>
        </div>
    )
}

function DayCard({
    dayIndex,
    day,
}: {
    dayIndex: number
    day: (CycleMenuDay & { items: CycleMenuItem[] }) | undefined
}) {
    const [showAddForm, setShowAddForm] = useState(false)
    const [deleteItemId, setDeleteItemId] = useState<number | null>(null)

    const addItemForm = useForm({
        cycle_menu_day_id: day?.id?.toString() ?? '',
        title: '',
        meal_type: MealType.BREAKFAST,
        time_of_day: '',
        quantity: '',
    })

    const notesForm = useForm({
        notes: day?.notes ?? '',
    })

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    )

    const items = day?.items ?? []

    function handleAddItem(e: FormEvent) {
        e.preventDefault()
        addItemForm.post('/cycle-menu-items', {
            preserveScroll: true,
            onSuccess: () => {
                addItemForm.reset()
                setShowAddForm(false)
            },
        })
    }

    function handleSaveNotes(e: FormEvent) {
        e.preventDefault()
        if (!day) return
        notesForm.put(`/cycle-menu-days/${day.id}`, {
            preserveScroll: true,
        })
    }

    function handleDragEnd(event: DragEndEvent) {
        const { active, over } = event
        if (!over || active.id === over.id) return

        const oldIndex = items.findIndex(item => item.id === active.id)
        const newIndex = items.findIndex(item => item.id === over.id)

        if (oldIndex === -1 || newIndex === -1) return

        // Build new order
        const reordered = [...items]
        const [moved] = reordered.splice(oldIndex, 1)
        reordered.splice(newIndex, 0, moved)

        const orders = reordered.map((item, idx) => ({
            id: item.id,
            position: idx,
        }))

        router.post('/cycle-menu-items/reorder', { orders }, {
            preserveScroll: true,
        })
    }

    function handleRemoveItem() {
        if (!deleteItemId) return
        router.delete(`/cycle-menu-items/${deleteItemId}`, {
            preserveScroll: true,
            onFinish: () => setDeleteItemId(null),
        })
    }

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                <CardTitle className="text-base">Day {dayIndex + 1}</CardTitle>
                {day ? (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setShowAddForm(!showAddForm)}
                    >
                        <Plus className="mr-1 h-3 w-3" />
                        Add Item
                    </Button>
                ) : null}
            </CardHeader>
            <CardContent className="space-y-4">
                {/* Day Notes */}
                {day ? (
                    <form onSubmit={handleSaveNotes} className="space-y-2">
                        <FormField
                            label="Notes"
                            name={`notes_${day.id}`}
                            value={notesForm.data.notes}
                            onChange={e => notesForm.setData('notes', e.target.value)}
                            error={notesForm.errors.notes}
                            multiline
                            rows={2}
                            placeholder="Day notes..."
                        />
                        <div className="flex justify-end">
                            <Button type="submit" size="sm" disabled={notesForm.processing}>
                                Save Notes
                            </Button>
                        </div>
                    </form>
                ) : null}

                {/* Add Item Form */}
                {showAddForm && day ? (
                    <form onSubmit={handleAddItem} className="space-y-3 rounded-md border border-border p-3">
                        <FormField
                            label="Item Title"
                            name={`title_${dayIndex}`}
                            value={addItemForm.data.title}
                            onChange={e => addItemForm.setData('title', e.target.value)}
                            error={addItemForm.errors.title}
                            required
                            placeholder="e.g., Oatmeal with berries"
                        />
                        <div className="grid grid-cols-3 gap-3">
                            <FormField label="Type" name={`meal_type_${dayIndex}`} error={addItemForm.errors.meal_type}>
                                <Select
                                    value={addItemForm.data.meal_type}
                                    onValueChange={v => addItemForm.setData('meal_type', v as typeof addItemForm.data.meal_type)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(mealTypeLabels).map(([value, label]) => (
                                            <SelectItem key={value} value={value}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormField>
                            <FormField
                                label="Time"
                                name={`time_${dayIndex}`}
                                type="time"
                                value={addItemForm.data.time_of_day}
                                onChange={e => addItemForm.setData('time_of_day', e.target.value)}
                                error={addItemForm.errors.time_of_day}
                            />
                            <FormField
                                label="Quantity"
                                name={`qty_${dayIndex}`}
                                value={addItemForm.data.quantity}
                                onChange={e => addItemForm.setData('quantity', e.target.value)}
                                error={addItemForm.errors.quantity}
                                placeholder="e.g., 1 serving"
                            />
                        </div>
                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="ghost" size="sm" onClick={() => setShowAddForm(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" size="sm" disabled={addItemForm.processing}>
                                {addItemForm.processing ? 'Adding...' : 'Add'}
                            </Button>
                        </div>
                    </form>
                ) : null}

                {/* Items List */}
                <div className="space-y-2">
                    <p className="text-sm font-medium">Items</p>
                    {items.length > 0 ? (
                        <DndContext
                            sensors={sensors}
                            collisionDetection={closestCenter}
                            onDragEnd={handleDragEnd}
                        >
                            <SortableContext
                                items={items.map(i => i.id)}
                                strategy={verticalListSortingStrategy}
                            >
                                <div className="space-y-2">
                                    {items.map(item => (
                                        <SortableItem
                                            key={item.id}
                                            item={item}
                                            onRemove={setDeleteItemId}
                                        />
                                    ))}
                                </div>
                            </SortableContext>
                        </DndContext>
                    ) : (
                        <p className="text-sm text-muted-foreground">No items yet.</p>
                    )}
                </div>
            </CardContent>

            <ConfirmationDialog
                open={deleteItemId !== null}
                onOpenChange={(open) => { if (!open) setDeleteItemId(null) }}
                title="Remove Item"
                description="Are you sure you want to remove this item?"
                onConfirm={handleRemoveItem}
                confirmLabel="Remove"
                variant="danger"
            />
        </Card>
    )
}

export default function CycleMenuShow({ menu, daysByIndex }: CycleMenuShowProps) {
    const [confirmDelete, setConfirmDelete] = useState(false)

    const handleDeleteMenu = useCallback(() => {
        router.delete(`/cycle-menus/${menu.id}`, {
            onFinish: () => setConfirmDelete(false),
        })
    }, [menu.id])

    return (
        <AppLayout>
            <Head title={menu.name} />

            <PageHeader title={menu.name} description={`Cycle length: ${menu.cycle_length_days} days${menu.starts_on ? ` \u2022 Starts ${formatDate(menu.starts_on)}` : ''}`}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/cycle-menus">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/cycle-menus/${menu.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <StatusBadge status={menu.is_active ? 'active' : 'paused'} />
                <Button variant="destructive" size="sm" onClick={() => setConfirmDelete(true)}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            {menu.notes ? (
                <Card className="mb-6">
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">{menu.notes}</p>
                    </CardContent>
                </Card>
            ) : null}

            <div className="space-y-6">
                {Array.from({ length: menu.cycle_length_days }, (_, i) => (
                    <DayCard
                        key={i}
                        dayIndex={i}
                        day={daysByIndex[i]}
                    />
                ))}
            </div>

            <ConfirmationDialog
                open={confirmDelete}
                onOpenChange={setConfirmDelete}
                title="Delete Cycle Menu"
                description="Are you sure you want to delete this cycle menu? This action cannot be undone."
                onConfirm={handleDeleteMenu}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

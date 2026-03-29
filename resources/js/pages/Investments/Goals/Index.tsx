import { Head, Link, router, useForm } from '@inertiajs/react'
import { useState, useCallback, type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import { Target, Plus, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { InvestmentGoal } from '@/types/models'

interface GoalsIndexProps {
    goals: InvestmentGoal[]
}

export default function GoalsIndex({ goals }: GoalsIndexProps) {
    const [confirmDeleteId, setConfirmDeleteId] = useState<number | null>(null)
    const [showCreateDialog, setShowCreateDialog] = useState(false)
    const [editGoalId, setEditGoalId] = useState<number | null>(null)

    const createForm = useForm({
        title: '',
        target_amount: '',
        target_date: '',
        description: '',
    })

    const editGoal = editGoalId !== null ? goals.find(g => g.id === editGoalId) : null
    const editForm = useForm({
        title: editGoal?.title ?? '',
        target_amount: editGoal ? String(editGoal.target_amount) : '',
        target_date: editGoal?.target_date ?? '',
        description: editGoal?.description ?? '',
    })

    function handleCreate(e: FormEvent) {
        e.preventDefault()
        createForm.post('/investments/goals', {
            onSuccess: () => {
                setShowCreateDialog(false)
                createForm.reset()
            },
        })
    }

    function handleEdit(e: FormEvent) {
        e.preventDefault()
        if (editGoalId === null) return
        editForm.put(`/investments/goals/${editGoalId}`, {
            onSuccess: () => setEditGoalId(null),
        })
    }

    const handleDelete = useCallback(() => {
        if (confirmDeleteId === null) return
        router.delete(`/investments/goals/${confirmDeleteId}`, {
            onFinish: () => setConfirmDeleteId(null),
        })
    }, [confirmDeleteId])

    function openEdit(goal: InvestmentGoal) {
        editForm.setData({
            title: goal.title,
            target_amount: String(goal.target_amount),
            target_date: goal.target_date ?? '',
            description: goal.description ?? '',
        })
        setEditGoalId(goal.id)
    }

    return (
        <AppLayout>
            <Head title="Investment Goals" />

            <PageHeader title="Investment Goals" description="Track your investment targets and progress">
                <Button variant="outline" asChild>
                    <Link href="/investments">Back to Investments</Link>
                </Button>
                <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
                    <DialogTrigger asChild>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Goal
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create Investment Goal</DialogTitle>
                        </DialogHeader>
                        <form onSubmit={handleCreate} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="create-title">Title</Label>
                                <Input
                                    id="create-title"
                                    value={createForm.data.title}
                                    onChange={e => createForm.setData('title', e.target.value)}
                                    required
                                    placeholder="e.g., Retirement Fund"
                                />
                                {createForm.errors.title ? <p className="text-sm text-destructive">{createForm.errors.title}</p> : null}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="create-target">Target Amount</Label>
                                <Input
                                    id="create-target"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={createForm.data.target_amount}
                                    onChange={e => createForm.setData('target_amount', e.target.value)}
                                    required
                                    placeholder="0.00"
                                />
                                {createForm.errors.target_amount ? <p className="text-sm text-destructive">{createForm.errors.target_amount}</p> : null}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="create-date">Target Date</Label>
                                <Input
                                    id="create-date"
                                    type="date"
                                    value={createForm.data.target_date}
                                    onChange={e => createForm.setData('target_date', e.target.value)}
                                />
                                {createForm.errors.target_date ? <p className="text-sm text-destructive">{createForm.errors.target_date}</p> : null}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="create-description">Description</Label>
                                <Textarea
                                    id="create-description"
                                    value={createForm.data.description}
                                    onChange={e => createForm.setData('description', e.target.value)}
                                    placeholder="Optional description"
                                />
                            </div>
                            <div className="flex justify-end gap-3">
                                <Button type="button" variant="outline" onClick={() => setShowCreateDialog(false)}>Cancel</Button>
                                <Button type="submit" disabled={createForm.processing}>
                                    {createForm.processing ? 'Creating...' : 'Create Goal'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </PageHeader>

            {goals.length > 0 ? (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {goals.map((goal) => {
                        const progressPercent = goal.target_amount > 0
                            ? Math.min(100, (goal.current_progress / goal.target_amount) * 100)
                            : 0

                        return (
                            <Card key={goal.id}>
                                <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                                    <CardTitle className="text-base font-medium">{goal.title}</CardTitle>
                                    <div className="flex gap-1">
                                        <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => openEdit(goal)}>
                                            <Pencil className="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon" className="h-8 w-8 text-destructive" onClick={() => setConfirmDeleteId(goal.id)}>
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    {goal.description ? (
                                        <p className="mb-3 text-sm text-muted-foreground">{goal.description}</p>
                                    ) : null}
                                    <div className="space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">Progress</span>
                                            <span className="font-medium">{progressPercent.toFixed(1)}%</span>
                                        </div>
                                        <div className="h-2 w-full rounded-full bg-secondary">
                                            <div
                                                className="h-2 rounded-full bg-primary transition-all"
                                                style={{ width: `${progressPercent}%` }}
                                            />
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">
                                                {formatCurrency(goal.current_progress, 'USD')} of {formatCurrency(goal.target_amount, 'USD')}
                                            </span>
                                        </div>
                                        {goal.target_date ? (
                                            <p className="text-xs text-muted-foreground">Target: {formatDate(goal.target_date)}</p>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        )
                    })}
                </div>
            ) : (
                <div className="flex flex-col items-center justify-center rounded-lg border border-dashed border-border py-12">
                    <Target className="h-10 w-10 text-muted-foreground" />
                    <h3 className="mt-4 text-sm font-semibold text-foreground">No investment goals yet</h3>
                    <p className="mt-1 text-sm text-muted-foreground">Set goals to track your investment progress</p>
                    <Button className="mt-4" size="sm" onClick={() => setShowCreateDialog(true)}>
                        Add Goal
                    </Button>
                </div>
            )}

            {/* Edit Dialog */}
            <Dialog open={editGoalId !== null} onOpenChange={(open) => { if (!open) setEditGoalId(null) }}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Investment Goal</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleEdit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="edit-title">Title</Label>
                            <Input
                                id="edit-title"
                                value={editForm.data.title}
                                onChange={e => editForm.setData('title', e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-target">Target Amount</Label>
                            <Input
                                id="edit-target"
                                type="number"
                                min="0"
                                step="0.01"
                                value={editForm.data.target_amount}
                                onChange={e => editForm.setData('target_amount', e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-date">Target Date</Label>
                            <Input
                                id="edit-date"
                                type="date"
                                value={editForm.data.target_date}
                                onChange={e => editForm.setData('target_date', e.target.value)}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="edit-description">Description</Label>
                            <Textarea
                                id="edit-description"
                                value={editForm.data.description}
                                onChange={e => editForm.setData('description', e.target.value)}
                            />
                        </div>
                        <div className="flex justify-end gap-3">
                            <Button type="button" variant="outline" onClick={() => setEditGoalId(null)}>Cancel</Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {editForm.processing ? 'Saving...' : 'Save Changes'}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            <ConfirmationDialog
                open={confirmDeleteId !== null}
                onOpenChange={(open) => { if (!open) setConfirmDeleteId(null) }}
                title="Delete Investment Goal"
                description="Are you sure you want to delete this goal? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}

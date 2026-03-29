import { useState, useCallback } from 'react'

interface ConfirmationState {
    isOpen: boolean
    title: string
    message: string
    action: (() => void) | null
    variant: 'danger' | 'warning' | 'default'
}

export function useConfirmation() {
    const [state, setState] = useState<ConfirmationState>({
        isOpen: false,
        title: '',
        message: '',
        action: null,
        variant: 'default',
    })

    const confirm = useCallback(({ title, message, action, variant = 'default' }: Omit<ConfirmationState, 'isOpen'>) => {
        setState({ isOpen: true, title, message, action, variant })
    }, [])

    const handleConfirm = useCallback(() => {
        state.action?.()
        setState(prev => ({ ...prev, isOpen: false }))
    }, [state.action])

    const handleCancel = useCallback(() => {
        setState(prev => ({ ...prev, isOpen: false }))
    }, [])

    return { ...state, confirm, handleConfirm, handleCancel }
}

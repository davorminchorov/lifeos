import { useState } from 'react'
import { format } from 'date-fns'
import { CalendarIcon } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { cn } from '@/lib/utils'

interface DatePickerProps {
    value?: string
    onChange: (date: string) => void
    placeholder?: string
    disabled?: boolean
    className?: string
}

export function DatePicker({
    value,
    onChange,
    placeholder = 'Pick a date',
    disabled,
    className,
}: DatePickerProps) {
    const [open, setOpen] = useState(false)
    const date = value ? new Date(value) : undefined

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    disabled={disabled}
                    className={cn(
                        'w-full justify-start text-left font-normal',
                        !value && 'text-muted-foreground',
                        className
                    )}
                >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {date ? format(date, 'PPP') : placeholder}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                    mode="single"
                    selected={date}
                    onSelect={(day) => {
                        if (day) {
                            onChange(format(day, 'yyyy-MM-dd'))
                            setOpen(false)
                        }
                    }}
                    initialFocus
                />
            </PopoverContent>
        </Popover>
    )
}

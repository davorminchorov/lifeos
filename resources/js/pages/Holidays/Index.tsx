import { Head } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { formatDate } from '@/lib/utils'
import type { Holiday } from '@/types/models'

interface HolidayIndexProps {
    holidays: Holiday[]
    countryName: string
    countryCode: string
}

export default function HolidayIndex({ holidays, countryName, countryCode }: HolidayIndexProps) {
    return (
        <AppLayout>
            <Head title={`${countryName} Holidays`} />

            <PageHeader
                title={`${countryName} Holidays`}
                description={`National and religious holidays celebrated in ${countryName}`}
            />

            {/* Desktop Table */}
            <div className="hidden rounded-md border border-border md:block">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Holiday Name</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Description</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {holidays.map((holiday) => (
                            <TableRow key={holiday.id}>
                                <TableCell className="font-medium">{holiday.name}</TableCell>
                                <TableCell className="whitespace-nowrap">
                                    {formatDate(holiday.date)}
                                </TableCell>
                                <TableCell className="text-sm text-muted-foreground">
                                    {holiday.description ?? '\u2014'}
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            {/* Mobile Cards */}
            <div className="space-y-3 md:hidden">
                {holidays.map((holiday) => (
                    <Card key={holiday.id}>
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between">
                                <p className="font-medium">{holiday.name}</p>
                                <p className="text-sm text-muted-foreground">{formatDate(holiday.date)}</p>
                            </div>
                            {holiday.description ? (
                                <p className="mt-1 text-sm text-muted-foreground">{holiday.description}</p>
                            ) : null}
                        </CardContent>
                    </Card>
                ))}
            </div>

            {/* About Section */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle className="text-base">About {countryName} Holidays</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2 text-sm text-muted-foreground">
                    <p>
                        {countryName} celebrates a rich mix of national and religious holidays that reflect its diverse cultural heritage.
                    </p>
                    <p>
                        <span className="font-medium text-foreground">National Holidays</span> commemorate important historical events such as independence, uprisings, and significant cultural figures.
                    </p>
                    <p>
                        <span className="font-medium text-foreground">Religious Holidays</span> follow the calendar and include major celebrations observed by the national church.
                    </p>
                    <p className="italic">
                        Note: Some holidays are movable feasts and their dates vary each year.
                    </p>
                </CardContent>
            </Card>
        </AppLayout>
    )
}

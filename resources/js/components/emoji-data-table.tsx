'use client';

import {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { ArrowUpDown, Edit, MoreHorizontal, Trash2 } from 'lucide-react';
import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { router } from '@inertiajs/react';

export type SpriteParams = {
    x?: number;
    y?: number;
    width?: number;
    height?: number;
    sprite_sheet_url?: string;
} & Record<string, string | number | boolean | undefined>;

export type Emoji = {
    id: number;
    title: string;
    emoji_shortcode: string;
    emoji_text?: string;
    image_url?: string;
    sprite_mode?: boolean;
    sprite_params?: SpriteParams;
    emoji_category_id?: number;
    category?: {
        id: number;
        title: string;
    };
    aliases?: {
        id: number;
        alias: string;
    }[];
    created_at: string;
};

interface DataTableProps {
    data: Emoji[];
    routePrefix?: string;
}

export function EmojiDataTable({ data, routePrefix = 'emojis' }: DataTableProps) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});

    const columns: ColumnDef<Emoji>[] = [
        {
            accessorKey: 'emoji_shortcode',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Shortcode
                        <ArrowUpDown className="ml-2 h-4 w-4" />
                    </Button>
                );
            },
            cell: ({ row }) => <div className="font-mono text-sm">{row.getValue('emoji_shortcode')}</div>,
        },
        {
            accessorKey: 'title',
            header: 'Title',
            cell: ({ row }) => <div className="font-medium">{row.getValue('title')}</div>,
        },
        {
            accessorKey: 'emoji_text',
            header: 'Display',
            cell: ({ row }) => {
                const emoji = row.original;
                if (emoji.emoji_text) {
                    return <div className="text-2xl">{emoji.emoji_text}</div>;
                }
                if (emoji.image_url) {
                    return <img src={`/storage/${emoji.image_url}`} alt={emoji.emoji_shortcode} className="h-8 w-8" />;
                }
                return <div className="text-gray-400">N/A</div>;
            },
        },
        {
            accessorKey: 'sprite_mode',
            header: 'Type',
            cell: ({ row }) => {
                const emoji = row.original;
                let type = 'text';
                if (emoji.sprite_mode) type = 'sprite';
                else if (emoji.image_url) type = 'image';

                return <Badge variant={type === 'text' ? 'default' : type === 'image' ? 'secondary' : 'outline'}>{type}</Badge>;
            },
        },
        {
            accessorKey: 'category',
            header: 'Category',
            cell: ({ row }) => {
                const category = row.original.category;
                return category ? <Badge variant="outline">{category.title}</Badge> : <span className="text-gray-400">None</span>;
            },
        },
        {
            accessorKey: 'aliases',
            header: 'Aliases',
            cell: ({ row }) => {
                const aliases = row.original.aliases || [];
                if (aliases.length === 0) {
                    return <span className="text-gray-400">None</span>;
                }
                return (
                    <div className="flex flex-wrap gap-1">
                        {aliases.slice(0, 2).map((alias) => (
                            <Badge key={alias.id} variant="secondary" className="text-xs">
                                {alias.alias}
                            </Badge>
                        ))}
                        {aliases.length > 2 && (
                            <Badge variant="secondary" className="text-xs">
                                +{aliases.length - 2} more
                            </Badge>
                        )}
                    </div>
                );
            },
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => {
                return (
                    <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}>
                        Created
                        <ArrowUpDown className="ml-2 h-4 w-4" />
                    </Button>
                );
            },
            cell: ({ row }) => {
                const date = new Date(row.getValue('created_at'));
                return <div className="text-sm text-gray-500">{date.toLocaleDateString()}</div>;
            },
        },
        {
            id: 'actions',
            cell: ({ row }) => {
                const emoji = row.original;

                return (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="h-8 w-8 p-0">
                                <span className="sr-only">Open menu</span>
                                <MoreHorizontal className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuItem onClick={() => navigator.clipboard.writeText(emoji.emoji_shortcode)}>Copy shortcode</DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem onClick={() => router.get(route(`${routePrefix}.edit`, emoji.id))}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                onClick={() => {
                                    if (confirm('Are you sure you want to delete this emoji?')) {
                                        router.delete(route(`${routePrefix}.destroy`, emoji.id));
                                    }
                                }}
                                className="text-red-600"
                            >
                                <Trash2 className="mr-2 h-4 w-4" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                );
            },
        },
    ];

    const table = useReactTable({
        data,
        columns,
        onSortingChange: setSorting,
        onColumnFiltersChange: setColumnFilters,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onColumnVisibilityChange: setColumnVisibility,
        state: {
            sorting,
            columnFilters,
            columnVisibility,
        },
    });

    return (
        <div className="w-full">
            <div className="flex items-center py-4">
                <Input
                    placeholder="Filter shortcodes..."
                    value={(table.getColumn('emoji_shortcode')?.getFilterValue() as string) ?? ''}
                    onChange={(event) => table.getColumn('emoji_shortcode')?.setFilterValue(event.target.value)}
                    className="max-w-sm"
                />
            </div>
            <div className="rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
            <div className="flex items-center justify-end space-x-2 py-4">
                <div className="flex-1 text-sm text-muted-foreground">{table.getFilteredRowModel().rows.length} emoji(s) total.</div>
                <div className="space-x-2">
                    <Button variant="outline" size="sm" onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()}>
                        Previous
                    </Button>
                    <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
                        Next
                    </Button>
                </div>
            </div>
        </div>
    );
}

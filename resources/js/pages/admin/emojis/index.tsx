import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Filter, Plus } from 'lucide-react';
import { useState } from 'react';

import { EmojiDataTable, type Emoji } from '@/components/emoji-data-table';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Control Panel',
        href: '/admin',
    },
    {
        title: 'Emoji Management',
        href: '/admin/emojis',
    },
];

type Category = {
    id: number;
    title: string;
    display_order: number;
    emojis_count?: number;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PageProps = {
    emojis: {
        data: Emoji[];
        links: PaginationLink[];
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
    categories: Category[];
    filters: {
        search?: string;
        category_id?: number;
    };
};

export default function AdminEmojiIndex({ emojis, categories, filters }: PageProps) {
    const { flash } = usePage<SharedData>().props;
    const [categoryFilter, setCategoryFilter] = useState(filters.category_id?.toString() || '');

    const handleCategoryFilter = (value: string) => {
        setCategoryFilter(value);
        const params = new URLSearchParams(window.location.search);

        if (value) {
            params.set('category_id', value);
        } else {
            params.delete('category_id');
        }

        router.get(route('admin.emojis.index'), Object.fromEntries(params), {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Control Panel - Emoji Management" />

            <div className="space-y-6 px-4 py-6">
                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-4">
                        <div className="text-sm font-medium text-green-800">{flash.success}</div>
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <div className="flex-1">
                        <HeadingSmall title="Emoji Management" description="Administrative emoji management - create, edit, and organize emojis" />
                    </div>
                    <Link href={route('admin.emojis.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add emoji
                        </Button>
                    </Link>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div className="rounded-lg border bg-gradient-to-r from-blue-50 to-blue-100 p-4">
                        <div className="text-2xl font-bold text-blue-900">{emojis.total || 0}</div>
                        <p className="text-sm text-blue-700">Total emojis</p>
                    </div>
                    <div className="rounded-lg border bg-gradient-to-r from-green-50 to-green-100 p-4">
                        <div className="text-2xl font-bold text-green-900">{categories.length}</div>
                        <p className="text-sm text-green-700">Categories</p>
                    </div>
                    <div className="rounded-lg border bg-gradient-to-r from-purple-50 to-purple-100 p-4">
                        <div className="text-2xl font-bold text-purple-900">
                            {emojis.data?.reduce((acc, emoji) => acc + (emoji.aliases?.length || 0), 0) || 0}
                        </div>
                        <p className="text-sm text-purple-700">Total aliases</p>
                    </div>
                    <div className="rounded-lg border bg-gradient-to-r from-orange-50 to-orange-100 p-4">
                        <div className="text-2xl font-bold text-orange-900">{emojis.data?.filter((emoji) => emoji.sprite_mode).length || 0}</div>
                        <p className="text-sm text-orange-700">Sprite emojis</p>
                    </div>
                </div>

                {/* Advanced Filters */}
                <div className="rounded-lg border bg-gray-50 p-4">
                    <div className="flex flex-wrap items-center gap-4">
                        <div className="flex items-center gap-2">
                            <Filter className="h-4 w-4 text-muted-foreground" />
                            <span className="text-sm font-medium">Filters:</span>
                        </div>
                        <select
                            value={categoryFilter}
                            onChange={(e) => handleCategoryFilter(e.target.value)}
                            className="rounded-md border border-input bg-background px-3 py-1 text-sm"
                        >
                            <option value="">All categories</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id.toString()}>
                                    {category.title} ({category.emojis_count || 0})
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Data Table */}
                <div className="rounded-lg border p-6">
                    <EmojiDataTable data={emojis.data || []} routePrefix="admin.emojis" />
                </div>

                {/* Pagination */}
                {emojis.links && emojis.links.length > 3 && (
                    <div className="flex items-center justify-center space-x-2">
                        {emojis.links.map((link, index) => (
                            <Button
                                key={index}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url)}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

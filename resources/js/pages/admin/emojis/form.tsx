import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Shield, Upload, X } from 'lucide-react';
import { ChangeEvent, FormEventHandler, useState } from 'react';

import { type SpriteParams } from '@/components/emoji-data-table';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';

type Category = {
    id: number;
    title: string;
    display_order: number;
};

type Alias = {
    id: number;
    alias: string;
};

type Emoji = {
    id: number;
    title: string;
    emoji_shortcode: string;
    emoji_text?: string;
    image_url?: string;
    sprite_mode?: boolean;
    sprite_params?: SpriteParams;
    emoji_category_id?: number;
    display_order: number;
    aliases?: Alias[];
};

type PageProps = {
    categories: Category[];
    emoji?: Emoji;
};

type EmojiForm = {
    title: string;
    emoji_shortcode: string;
    emoji_text: string;
    image?: File;
    sprite_mode: boolean;
    sprite_params?: Record<string, string | number | boolean>;
    emoji_category_id: number | '';
    display_order: number;
};

export default function AdminEmojiForm({ categories, emoji }: PageProps) {
    const { flash } = usePage<SharedData>().props;
    const isEditing = !!emoji;
    const [imagePreview, setImagePreview] = useState<string | null>(null);
    const [newAlias, setNewAlias] = useState('');

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Control Panel',
            href: '/admin',
        },
        {
            title: 'Emoji Management',
            href: '/admin/emojis',
        },
        {
            title: isEditing ? 'Edit emoji' : 'Create emoji',
            href: '#',
        },
    ];

    const { data, setData, post, patch, errors, processing } = useForm<EmojiForm>({
        title: emoji?.title || '',
        emoji_shortcode: emoji?.emoji_shortcode || '',
        emoji_text: emoji?.emoji_text || '',
        sprite_mode: emoji?.sprite_mode || false,
        sprite_params: (emoji?.sprite_params as Record<string, string | number | boolean>) || {},
        emoji_category_id: emoji?.emoji_category_id || '',
        display_order: emoji?.display_order || 0,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEditing) {
            patch(route('admin.emojis.update', emoji.id), {
                preserveScroll: true,
            });
        } else {
            post(route('admin.emojis.store'), {
                preserveScroll: true,
            });
        }
    };

    const handleImageChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('image', file);
            setData('sprite_mode', false);

            const reader = new FileReader();
            reader.onload = (e) => {
                setImagePreview(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleTypeChange = (type: 'text' | 'image' | 'sprite') => {
        if (type === 'sprite') {
            setData('sprite_mode', true);
        } else {
            setData('sprite_mode', false);
        }

        if (type !== 'image') {
            setImagePreview(null);
            setData('image', undefined);
        }
    };

    const addAlias = () => {
        if (!newAlias.trim() || !emoji) return;

        router.post(
            route('admin.emoji-aliases.store'),
            {
                emoji_id: emoji.id,
                alias: newAlias,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setNewAlias('');
                },
            },
        );
    };

    const removeAlias = (aliasId: number) => {
        if (confirm('Are you sure you want to remove this alias?')) {
            router.delete(route('admin.emoji-aliases.destroy', aliasId), {
                preserveScroll: true,
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Control Panel - ${isEditing ? 'Edit' : 'Create'} Emoji`} />

            <div className="space-y-6 px-4 py-6">
                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-4">
                        <div className="text-sm font-medium text-green-800">{flash.success}</div>
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <div className="flex-1">
                        <header>
                            <h3 className="mb-0.5 flex items-center gap-2 text-base font-medium">
                                <Shield className="h-5 w-5 text-blue-600" />
                                {isEditing ? 'Edit emoji' : 'Create emoji'}
                            </h3>
                            <p className="text-sm text-muted-foreground">Administrative emoji management - full control over emoji properties</p>
                        </header>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>Configure the fundamental properties of this emoji</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid gap-2">
                                <Label htmlFor="title">Title *</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    placeholder="Emoji title"
                                    required
                                />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="emoji_shortcode">Shortcode *</Label>
                                <Input
                                    id="emoji_shortcode"
                                    value={data.emoji_shortcode}
                                    onChange={(e) => setData('emoji_shortcode', e.target.value)}
                                    placeholder=":example:"
                                    required
                                />
                                <InputError message={errors.emoji_shortcode} />
                                <p className="text-sm text-muted-foreground">Must be in format :name: (e.g., :smile:)</p>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="emoji_category_id">Category</Label>
                                <select
                                    id="emoji_category_id"
                                    value={data.emoji_category_id}
                                    onChange={(e) => setData('emoji_category_id', e.target.value ? Number(e.target.value) : '')}
                                    className="rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option value="">No category</option>
                                    {categories.map((category) => (
                                        <option key={category.id} value={category.id}>
                                            {category.title}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.emoji_category_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="display_order">Display Order</Label>
                                <Input
                                    id="display_order"
                                    type="number"
                                    value={data.display_order}
                                    onChange={(e) => setData('display_order', Number(e.target.value))}
                                    min="0"
                                />
                                <InputError message={errors.display_order} />
                                <p className="text-sm text-muted-foreground">Lower numbers appear first in lists</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Emoji Type */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Emoji Type & Content</CardTitle>
                            <CardDescription>Choose how this emoji will be displayed to users</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="flex gap-4">
                                <Button
                                    type="button"
                                    variant={!data.sprite_mode && !imagePreview ? 'default' : 'outline'}
                                    onClick={() => handleTypeChange('text')}
                                >
                                    Text/Unicode
                                </Button>
                                <Button
                                    type="button"
                                    variant={imagePreview && !data.sprite_mode ? 'default' : 'outline'}
                                    onClick={() => handleTypeChange('image')}
                                >
                                    Custom Image
                                </Button>
                                <Button type="button" variant={data.sprite_mode ? 'default' : 'outline'} onClick={() => handleTypeChange('sprite')}>
                                    Sprite Sheet
                                </Button>
                            </div>

                            {!data.sprite_mode && !imagePreview && (
                                <div className="grid gap-2">
                                    <Label htmlFor="emoji_text">Emoji Character</Label>
                                    <Input
                                        id="emoji_text"
                                        value={data.emoji_text}
                                        onChange={(e) => setData('emoji_text', e.target.value)}
                                        placeholder="😀"
                                        className="text-2xl"
                                    />
                                    <InputError message={errors.emoji_text} />
                                    <p className="text-sm text-muted-foreground">Enter the Unicode emoji character</p>
                                </div>
                            )}

                            {!data.sprite_mode && (
                                <div className="grid gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="image">Upload Image</Label>
                                        <div className="rounded-lg border-2 border-dashed border-gray-300 p-6">
                                            <div className="text-center">
                                                {imagePreview ? (
                                                    <div className="space-y-4">
                                                        <img src={imagePreview} alt="Preview" className="mx-auto h-16 w-16 object-contain" />
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            onClick={() => {
                                                                setImagePreview(null);
                                                                setData('image', undefined);
                                                            }}
                                                        >
                                                            Remove image
                                                        </Button>
                                                    </div>
                                                ) : (
                                                    <div className="space-y-4">
                                                        <Upload className="mx-auto h-12 w-12 text-gray-400" />
                                                        <div>
                                                            <input
                                                                type="file"
                                                                id="image"
                                                                accept="image/*"
                                                                onChange={handleImageChange}
                                                                className="hidden"
                                                            />
                                                            <Label htmlFor="image" className="cursor-pointer">
                                                                <Button type="button" variant="outline" asChild>
                                                                    <span>Choose file</span>
                                                                </Button>
                                                            </Label>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                        <InputError message={errors.image} />
                                        <p className="text-sm text-muted-foreground">PNG, JPG, GIF up to 2MB. Recommended size: 32x32px</p>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Aliases - Only for editing */}
                    {isEditing && emoji?.aliases && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Aliases</CardTitle>
                                <CardDescription>Alternative shortcodes that users can use for this emoji</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {emoji.aliases.length > 0 && (
                                    <div className="flex flex-wrap gap-2">
                                        {emoji.aliases.map((alias) => (
                                            <Badge key={alias.id} variant="secondary" className="flex items-center gap-2">
                                                {alias.alias}
                                                <button
                                                    type="button"
                                                    onClick={() => removeAlias(alias.id)}
                                                    className="text-red-500 hover:text-red-700"
                                                >
                                                    <X className="h-3 w-3" />
                                                </button>
                                            </Badge>
                                        ))}
                                    </div>
                                )}

                                <div className="flex gap-2">
                                    <Input
                                        placeholder=":alias:"
                                        value={newAlias}
                                        onChange={(e) => setNewAlias(e.target.value)}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                addAlias();
                                            }
                                        }}
                                    />
                                    <Button type="button" onClick={addAlias} variant="outline">
                                        Add alias
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Actions */}
                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : isEditing ? 'Update emoji' : 'Create emoji'}
                        </Button>
                        <Link href={route('admin.emojis.index')}>
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

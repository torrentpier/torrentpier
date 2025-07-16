import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { BarChart3, Database, Settings, Shield, Smile, Users } from 'lucide-react';

import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Control Panel',
        href: '/admin',
    },
];

const adminSections = [
    {
        title: 'Emoji Management',
        description: 'Manage custom emojis, categories, and aliases',
        icon: Smile,
        href: '/admin/emojis',
        color: 'bg-yellow-500',
    },
    {
        title: 'User Management',
        description: 'Manage users, roles, and permissions',
        icon: Users,
        href: '/admin/users',
        color: 'bg-blue-500',
        disabled: true,
    },
    {
        title: 'System Settings',
        description: 'Configure application settings and preferences',
        icon: Settings,
        href: '/admin/settings',
        color: 'bg-gray-500',
        disabled: true,
    },
    {
        title: 'Database Management',
        description: 'Database operations and maintenance',
        icon: Database,
        href: '/admin/database',
        color: 'bg-green-500',
        disabled: true,
    },
    {
        title: 'Analytics',
        description: 'View system analytics and reports',
        icon: BarChart3,
        href: '/admin/analytics',
        color: 'bg-purple-500',
        disabled: true,
    },
    {
        title: 'Security',
        description: 'Security settings and audit logs',
        icon: Shield,
        href: '/admin/security',
        color: 'bg-red-500',
        disabled: true,
    },
];

export default function AdminIndex() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Control Panel" />

            <div className="space-y-8 px-4 py-6">
                <Heading title="Control Panel" description="Administrative tools and system management" />

                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {adminSections.map((section) => {
                        const IconComponent = section.icon;

                        return (
                            <Card
                                key={section.title}
                                className={`transition-all duration-200 hover:shadow-lg ${
                                    section.disabled ? 'cursor-not-allowed opacity-50' : 'hover:scale-105'
                                }`}
                            >
                                <CardHeader className="pb-3">
                                    <div className="flex items-center gap-3">
                                        <div className={`rounded-lg p-2 ${section.color} text-white`}>
                                            <IconComponent className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <CardTitle className="text-lg">{section.title}</CardTitle>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent className="pt-0">
                                    <CardDescription className="mb-4">{section.description}</CardDescription>

                                    {section.disabled ? (
                                        <Button variant="outline" disabled className="w-full">
                                            Coming Soon
                                        </Button>
                                    ) : (
                                        <Link href={section.href}>
                                            <Button className="w-full">To {section.title}</Button>
                                        </Link>
                                    )}
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Quick Stats */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Users</CardTitle>
                            <div className="text-2xl font-bold">1,234</div>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Sessions</CardTitle>
                            <div className="text-2xl font-bold">89</div>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">System Load</CardTitle>
                            <div className="text-2xl font-bold">12%</div>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Storage Used</CardTitle>
                            <div className="text-2xl font-bold">2.4 GB</div>
                        </CardHeader>
                    </Card>
                </div>

                {/* Recent Activity */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>Latest administrative actions and system events</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-2">
                            <div className="flex items-center justify-between border-b py-2">
                                <div>
                                    <div className="font-medium">User registration spike</div>
                                    <div className="text-sm text-muted-foreground">25 new users in the last hour</div>
                                </div>
                                <div className="text-sm text-muted-foreground">2 min ago</div>
                            </div>
                            <div className="flex items-center justify-between border-b py-2">
                                <div>
                                    <div className="font-medium">System backup completed</div>
                                    <div className="text-sm text-muted-foreground">Database backup finished successfully</div>
                                </div>
                                <div className="text-sm text-muted-foreground">1 hour ago</div>
                            </div>
                            <div className="flex items-center justify-between py-2">
                                <div>
                                    <div className="font-medium">Security scan completed</div>
                                    <div className="text-sm text-muted-foreground">No threats detected</div>
                                </div>
                                <div className="text-sm text-muted-foreground">3 hours ago</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

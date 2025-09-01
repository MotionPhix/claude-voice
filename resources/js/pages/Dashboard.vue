<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    TrendingUp,
    TrendingDown,
    FileText,
    Users,
    DollarSign,
    Clock,
    AlertTriangle,
    Plus,
    ArrowRight,
    Calendar,
    Eye,
    Send,
    CreditCard
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';

interface DashboardStats {
    total_invoices: number;
    total_revenue: number;
    pending_amount: number;
    overdue_amount: number;
    draft_count: number;
    paid_count: number;
    overdue_count: number;
    clients_count: number;
    revenue_growth: number;
    invoice_growth: number;
}

interface RecentInvoice {
    id: number;
    invoice_number: string;
    client: {
        name: string;
    };
    total: number;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    due_date: string;
    created_at: string;
}

interface UpcomingInvoice {
    id: number;
    invoice_number: string;
    client: {
        name: string;
    };
    total: number;
    due_date: string;
    days_until_due: number;
}

interface Props {
    stats: DashboardStats;
    recent_invoices: RecentInvoice[];
    upcoming_due: UpcomingInvoice[];
    monthly_revenue: Array<{
        month: string;
        revenue: number;
    }>;
}

const props = defineProps<Props>();

// Format currency
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

// Format percentage
const formatPercentage = (value: number): string => {
    return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`;
};

// Status colors
const getStatusColor = (status: string) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800',
        sent: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        overdue: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-600'
    };
    return colors[status] || colors.draft;
};

// Calculate collection rate
const collectionRate = computed(() => {
    const total = props.stats.total_revenue + props.stats.pending_amount;
    if (total === 0) return 0;
    return (props.stats.total_revenue / total) * 100;
});

// Get overdue urgency
const getUrgencyColor = (daysOverdue: number) => {
    if (daysOverdue <= 0) return 'text-green-600';
    if (daysOverdue <= 7) return 'text-yellow-600';
    if (daysOverdue <= 30) return 'text-orange-600';
    return 'text-red-600';
};

// Format relative time
const formatRelativeTime = (date: string) => {
    const now = new Date();
    const targetDate = new Date(date);
    const diffTime = targetDate.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    if (diffDays === -1) return 'Yesterday';
    if (diffDays > 1) return `In ${diffDays} days`;
    return `${Math.abs(diffDays)} days ago`;
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Good morning! 👋
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Here's what's happening with your business today.
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Revenue
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(stats.total_revenue) }}
                                </p>
                                <div class="flex items-center mt-1">
                                    <TrendingUp
                                        v-if="stats.revenue_growth >= 0"
                                        class="h-4 w-4 text-green-600 mr-1"
                                    />
                                    <TrendingDown
                                        v-else
                                        class="h-4 w-4 text-red-600 mr-1"
                                    />
                                    <span
                                        :class="stats.revenue_growth >= 0 ? 'text-green-600' : 'text-red-600'"
                                        class="text-sm font-medium"
                                    >
                                        {{ formatPercentage(stats.revenue_growth) }}
                                    </span>
                                    <span class="text-sm text-gray-500 ml-1">vs last month</span>
                                </div>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-full">
                                <DollarSign class="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total Invoices -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Total Invoices
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ stats.total_invoices.toLocaleString() }}
                                </p>
                                <div class="flex items-center mt-1">
                                    <TrendingUp
                                        v-if="stats.invoice_growth >= 0"
                                        class="h-4 w-4 text-blue-600 mr-1"
                                    />
                                    <TrendingDown
                                        v-else
                                        class="h-4 w-4 text-red-600 mr-1"
                                    />
                                    <span
                                        :class="stats.invoice_growth >= 0 ? 'text-blue-600' : 'text-red-600'"
                                        class="text-sm font-medium"
                                    >
                                        {{ formatPercentage(stats.invoice_growth) }}
                                    </span>
                                    <span class="text-sm text-gray-500 ml-1">vs last month</span>
                                </div>
                            </div>
                            <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                                <FileText class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pending Amount -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Pending Amount
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(stats.pending_amount) }}
                                </p>
                                <div class="flex items-center mt-1">
                                    <Clock class="h-4 w-4 text-yellow-600 mr-1" />
                                    <span class="text-sm text-gray-500">
                                        {{ stats.total_invoices - stats.paid_count - stats.draft_count }} invoices
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-full">
                                <Clock class="h-6 w-6 text-yellow-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Overdue Amount -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Overdue Amount
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(stats.overdue_amount) }}
                                </p>
                                <div class="flex items-center mt-1">
                                    <AlertTriangle class="h-4 w-4 text-red-600 mr-1" />
                                    <span class="text-sm text-gray-500">
                                        {{ stats.overdue_count }} invoices overdue
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-full">
                                <AlertTriangle class="h-6 w-6 text-red-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Collection Rate & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Collection Rate -->
                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle>Collection Rate</CardTitle>
                        <CardDescription>Percentage of invoices paid</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ collectionRate.toFixed(1) }}%
                            </div>
                            <Progress :value="collectionRate" class="mb-4" />
                            <p class="text-sm text-gray-500">
                                {{ stats.paid_count }} of {{ stats.total_invoices }} invoices paid
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common tasks and shortcuts</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link href="/invoices/create">
                                <Button class="w-full h-20 flex-col gap-2">
                                    <Plus class="h-5 w-5" />
                                    <span class="text-xs">Create Invoice</span>
                                </Button>
                            </Link>
                            <Link href="/clients/create">
                                <Button variant="outline" class="w-full h-20 flex-col gap-2">
                                    <Users class="h-5 w-5" />
                                    <span class="text-xs">Add Client</span>
                                </Button>
                            </Link>
                            <Link href="/invoices?status=overdue">
                                <Button variant="outline" class="w-full h-20 flex-col gap-2">
                                    <AlertTriangle class="h-5 w-5" />
                                    <span class="text-xs">View Overdue</span>
                                </Button>
                            </Link>
                            <Link href="/reports">
                                <Button variant="outline" class="w-full h-20 flex-col gap-2">
                                    <FileText class="h-5 w-5" />
                                    <span class="text-xs">Reports</span>
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity & Upcoming Due -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Invoices -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Recent Invoices</CardTitle>
                                <CardDescription>Latest invoice activity</CardDescription>
                            </div>
                            <Link href="/invoices">
                                <Button variant="ghost" size="sm">
                                    View all
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="invoice in recent_invoices"
                                :key="invoice.id"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <FileText class="h-8 w-8 p-1.5 bg-white dark:bg-gray-700 rounded border" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ invoice.invoice_number }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ invoice.client.name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(invoice.total) }}
                                        </p>
                                        <Badge
                                            :class="getStatusColor(invoice.status)"
                                            class="text-xs"
                                        >
                                            {{ invoice.status }}
                                        </Badge>
                                    </div>
                                    <Link :href="`/invoices/${invoice.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </div>
                            </div>

                            <div v-if="!recent_invoices.length" class="text-center py-8">
                                <FileText class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                    No recent invoices
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Get started by creating your first invoice.
                                </p>
                                <div class="mt-6">
                                    <Link href="/invoices/create">
                                        <Button>
                                            <Plus class="mr-2 h-4 w-4" />
                                            Create Invoice
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Upcoming Due Invoices -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Upcoming Due</CardTitle>
                                <CardDescription>Invoices due soon</CardDescription>
                            </div>
                            <Link href="/invoices?due_soon=7">
                                <Button variant="ghost" size="sm">
                                    View all
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="invoice in upcoming_due"
                                :key="invoice.id"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <Calendar class="h-8 w-8 p-1.5 bg-white dark:bg-gray-700 rounded border" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ invoice.invoice_number }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ invoice.client.name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(invoice.total) }}
                                        </p>
                                        <p
                                            :class="getUrgencyColor(invoice.days_until_due)"
                                            class="text-xs font-medium"
                                        >
                                            {{ formatRelativeTime(invoice.due_date) }}
                                        </p>
                                    </div>
                                    <Link :href="`/invoices/${invoice.id}`">
                                        <Button variant="ghost" size="icon">
                                            <Send class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </div>
                            </div>

                            <div v-if="!upcoming_due.length" class="text-center py-8">
                                <Calendar class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                    No upcoming due dates
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    All your invoices are up to date!
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

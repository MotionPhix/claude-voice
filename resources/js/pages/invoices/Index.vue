<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit2,
    Trash2,
    Download,
    Send,
    Copy,
    Calendar,
    FileText,
    CheckCircle,
    Clock,
    AlertCircle
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

interface Invoice {
    id: number;
    invoice_number: string;
    client: {
        id: number;
        name: string;
        email: string;
    };
    issue_date: string;
    due_date: string;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    total: number;
    currency: string;
    created_at: string;
}

interface Props {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters?: {
        search?: string;
        status?: string;
        client_id?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Invoices', href: '/invoices' }
];

// Local state
const searchTerm = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const isLoading = ref(false);

// Status configuration
const statusConfig = {
    draft: { color: 'bg-gray-100 text-gray-800', icon: FileText, label: 'Draft' },
    sent: { color: 'bg-blue-100 text-blue-800', icon: Send, label: 'Sent' },
    paid: { color: 'bg-green-100 text-green-800', icon: CheckCircle, label: 'Paid' },
    overdue: { color: 'bg-red-100 text-red-800', icon: AlertCircle, label: 'Overdue' },
    cancelled: { color: 'bg-gray-100 text-gray-600', icon: Clock, label: 'Cancelled' }
};

// Computed properties
const stats = computed(() => {
    const invoices = props.invoices.data;
    return {
        total: invoices.length,
        draft: invoices.filter(i => i.status === 'draft').length,
        sent: invoices.filter(i => i.status === 'sent').length,
        paid: invoices.filter(i => i.status === 'paid').length,
        overdue: invoices.filter(i => i.status === 'overdue').length,
    };
});

// Methods
const formatCurrency = (amount: number, currency: string = 'USD'): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const handleSearch = () => {
    const params = new URLSearchParams();
    if (searchTerm.value) params.set('search', searchTerm.value);
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);

    router.visit(`/invoices?${params.toString()}`, {
        preserveState: true,
        replace: true
    });
};

const handleDelete = (invoice: Invoice) => {
    if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
        router.delete(`/invoices/${invoice.id}`, {
            onSuccess: () => {
                // Handle success
            },
            onError: () => {
                // Handle error
            }
        });
    }
};

const handleSendInvoice = (invoice: Invoice) => {
    router.post(`/invoices/${invoice.id}/send`, {}, {
        onSuccess: () => {
            // Handle success
        },
        onError: () => {
            // Handle error
        }
    });
};

const handleDuplicate = (invoice: Invoice) => {
    router.post(`/invoices/${invoice.id}/duplicate`, {}, {
        onSuccess: (page) => {
            const newInvoice = page.props.invoice as Invoice;
            router.visit(`/invoices/${newInvoice.id}/edit`);
        }
    });
};

onMounted(() => {
    // Any initialization logic
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Invoices" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Invoices</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your invoices and track payments
                    </p>
                </div>
                <Link :href="route('invoices.create')">
                    <Button>
                        <Plus class="h-4 w-4 mr-2" />
                        New Invoice
                    </Button>
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Draft</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.draft }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Sent</CardTitle>
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.sent }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Paid</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.paid }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Overdue</CardTitle>
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.overdue }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="searchTerm"
                                    placeholder="Search invoices..."
                                    class="pl-10"
                                    @keyup.enter="handleSearch"
                                />
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <select
                                v-model="statusFilter"
                                @change="handleSearch"
                                class="flex h-9 items-center justify-between rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="all">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="cancelled">Cancelled</option>
                            </select>

                            <Button variant="outline" size="icon" @click="handleSearch">
                                <Filter class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Invoices Table -->
            <Card>
                <CardContent class="pt-6">
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Issue Date</TableHead>
                                    <TableHead>Due Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Amount</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                                    <TableCell class="font-medium">
                                        <Link :href="`/invoices/${invoice.id}`" class="hover:underline">
                                            {{ invoice.invoice_number }}
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        <div>
                                            <div class="font-medium">{{ invoice.client.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ invoice.client.email }}</div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{ formatDate(invoice.issue_date) }}</TableCell>
                                    <TableCell>{{ formatDate(invoice.due_date) }}</TableCell>
                                    <TableCell>
                                        <Badge :class="statusConfig[invoice.status].color">
                                            {{ statusConfig[invoice.status].label }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ formatCurrency(invoice.total, invoice.currency) }}
                                    </TableCell>
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="$inertia.visit(`/invoices/${invoice.id}`)">
                                                    <Eye class="h-4 w-4 mr-2" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="invoice.status === 'draft'"
                                                    @click="$inertia.visit(`/invoices/${invoice.id}/edit`)"
                                                >
                                                    <Edit2 class="h-4 w-4 mr-2" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="invoice.status === 'draft'"
                                                    @click="handleSendInvoice(invoice)"
                                                >
                                                    <Send class="h-4 w-4 mr-2" />
                                                    Send
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="$inertia.visit(`/invoices/${invoice.id}/pdf`)">
                                                    <Download class="h-4 w-4 mr-2" />
                                                    Download PDF
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="handleDuplicate(invoice)">
                                                    <Copy class="h-4 w-4 mr-2" />
                                                    Duplicate
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    @click="handleDelete(invoice)"
                                                    class="text-red-600"
                                                    v-if="invoice.status !== 'paid'"
                                                >
                                                    <Trash2 class="h-4 w-4 mr-2" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between mt-4" v-if="invoices.last_page > 1">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ ((invoices.current_page - 1) * invoices.per_page) + 1 }} to
                            {{ Math.min(invoices.current_page * invoices.per_page, invoices.total) }} of
                            {{ invoices.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="invoices.current_page === 1"
                                @click="$inertia.visit(`/invoices?page=${invoices.current_page - 1}`)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="invoices.current_page === invoices.last_page"
                                @click="$inertia.visit(`/invoices?page=${invoices.current_page + 1}`)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="invoices.data.length === 0" class="text-center py-12">
                        <FileText class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">No invoices found</h3>
                        <p class="text-sm text-muted-foreground mb-4">
                            {{ searchTerm || statusFilter !== 'all' ? 'Try adjusting your search or filters.' : 'Get started by creating your first invoice.' }}
                        </p>
                        <Link :href="route('invoices.create')" v-if="!searchTerm && statusFilter === 'all'">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />
                                Create Invoice
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

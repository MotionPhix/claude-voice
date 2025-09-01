<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    AlertCircle,
    X,
    ChevronDown,
    SortAsc,
    SortDesc,
    Users,
    DollarSign,
    RefreshCw,
    ArrowUpDown,
    Grid,
    List
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

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
    amount_paid: number;
    currency: string;
    created_at: string;
    is_overdue?: boolean;
    days_overdue?: number;
}

interface Client {
    id: number;
    name: string;
    email: string;
}

interface Props {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    clients?: Client[];
    filters?: {
        search?: string;
        status?: string;
        client_id?: number;
        date_from?: string;
        date_to?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
    stats?: {
        total_amount: number;
        paid_amount: number;
        pending_amount: number;
        overdue_amount: number;
        total_count: number;
    };
}

const props = defineProps<Props>();

// Reactive state
const selectedInvoices = ref<number[]>([]);
const showFilters = ref(false);
const isLoading = ref(false);
const viewMode = ref<'table' | 'grid'>('table');

// Form for filters
const filterForm = useForm({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    client_id: props.filters?.client_id?.toString() || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    sort_by: props.filters?.sort_by || 'created_at',
    sort_direction: props.filters?.sort_direction || 'desc',
});

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Invoices', href: '/invoices' }
];

// Status configuration
const statusConfig = {
    draft: { 
        color: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300', 
        icon: FileText,
        label: 'Draft'
    },
    sent: { 
        color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300', 
        icon: Send,
        label: 'Sent'
    },
    paid: { 
        color: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300', 
        icon: CheckCircle,
        label: 'Paid'
    },
    overdue: { 
        color: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300', 
        icon: AlertCircle,
        label: 'Overdue'
    },
    cancelled: { 
        color: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400', 
        icon: X,
        label: 'Cancelled'
    }
};

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'paid', label: 'Paid' },
    { value: 'overdue', label: 'Overdue' },
    { value: 'cancelled', label: 'Cancelled' }
];

// Computed properties
const hasActiveFilters = computed(() => {
    return filterForm.search || 
           filterForm.status || 
           filterForm.client_id || 
           filterForm.date_from || 
           filterForm.date_to;
});

const allSelected = computed({
    get: () => {
        return props.invoices.data.length > 0 && 
               selectedInvoices.value.length === props.invoices.data.length;
    },
    set: (value: boolean) => {
        if (value) {
            selectedInvoices.value = props.invoices.data.map(inv => inv.id);
        } else {
            selectedInvoices.value = [];
        }
    }
});

// Helper functions
const formatCurrency = (amount: number, currency: string = 'USD'): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const getStatusConfig = (status: string) => {
    return statusConfig[status] || statusConfig.draft;
};

const isInvoiceSelected = (invoiceId: number): boolean => {
    return selectedInvoices.value.includes(invoiceId);
};

const toggleInvoiceSelection = (invoiceId: number): void => {
    const index = selectedInvoices.value.indexOf(invoiceId);
    if (index > -1) {
        selectedInvoices.value.splice(index, 1);
    } else {
        selectedInvoices.value.push(invoiceId);
    }
};

const applyFilters = () => {
    isLoading.value = true;
    const params = {
        search: filterForm.search || undefined,
        status: filterForm.status || undefined,
        client_id: filterForm.client_id || undefined,
        date_from: filterForm.date_from || undefined,
        date_to: filterForm.date_to || undefined,
        sort_by: filterForm.sort_by,
        sort_direction: filterForm.sort_direction,
    };
    
    router.get('/invoices', params, {
        preserveState: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.sort_by = 'created_at';
    filterForm.sort_direction = 'desc';
    applyFilters();
};

const sortBy = (column: string) => {
    if (filterForm.sort_by === column) {
        filterForm.sort_direction = filterForm.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        filterForm.sort_by = column;
        filterForm.sort_direction = 'asc';
    }
    applyFilters();
};

const getSortIcon = (column: string) => {
    if (filterForm.sort_by !== column) return ArrowUpDown;
    return filterForm.sort_direction === 'asc' ? SortAsc : SortDesc;
};

// Actions
const deleteInvoice = (invoice: Invoice) => {
    if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
        router.delete(`/invoices/${invoice.id}`);
    }
};

const duplicateInvoice = (invoice: Invoice) => {
    router.post(`/invoices/${invoice.id}/duplicate`);
};

const sendInvoice = (invoice: Invoice) => {
    router.post(`/invoices/${invoice.id}/send`);
};

const downloadPDF = (invoice: Invoice) => {
    window.open(`/invoices/${invoice.id}/pdf`, '_blank');
};

// Bulk actions
const performBulkAction = (action: string) => {
    if (selectedInvoices.value.length === 0) return;
    
    const ids = selectedInvoices.value;
    
    switch (action) {
        case 'delete':
            if (confirm(`Delete ${ids.length} selected invoices?`)) {
                router.delete('/invoices/bulk-delete', {
                    data: { ids },
                    onSuccess: () => {
                        selectedInvoices.value = [];
                    }
                });
            }
            break;
        case 'send':
            router.post('/invoices/bulk-send', { ids });
            break;
        case 'export':
            router.get('/invoices/export', { ids });
            break;
    }
};

// Watch for search input changes (debounced)
let searchTimeout: NodeJS.Timeout;
watch(() => filterForm.search, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Watch for other filter changes
watch([() => filterForm.status, () => filterForm.client_id], () => {
    applyFilters();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Invoices" />

        <TooltipProvider>
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Invoices
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Manage and track all your invoices in one place
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button variant="outline" @click="showFilters = !showFilters">
                            <Filter class="h-4 w-4 mr-2" />
                            Filters
                            <Badge v-if="hasActiveFilters" variant="secondary" class="ml-2">
                                {{ Object.values(props.filters || {}).filter(Boolean).length }}
                            </Badge>
                        </Button>
                        <Link href="/invoices/create">
                            <Button>
                                <Plus class="h-4 w-4 mr-2" />
                                Create Invoice
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <Card>
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Total Amount
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.total_amount) }}
                                    </p>
                                </div>
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                                    <DollarSign class="h-5 w-5 text-blue-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Paid Amount
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.paid_amount) }}
                                    </p>
                                </div>
                                <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-full">
                                    <CheckCircle class="h-5 w-5 text-green-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Pending Amount
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.pending_amount) }}
                                    </p>
                                </div>
                                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-full">
                                    <Clock class="h-5 w-5 text-yellow-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Overdue Amount
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.overdue_amount) }}
                                    </p>
                                </div>
                                <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-full">
                                    <AlertTriangle class="h-5 w-5 text-red-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Filters Panel -->
                <Card v-if="showFilters" class="mb-6">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Filters</CardTitle>
                            <Button variant="ghost" size="sm" @click="showFilters = false">
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                    Search
                                </label>
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input
                                        v-model="filterForm.search"
                                        placeholder="Search invoices..."
                                        class="pl-9"
                                    />
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                    Status
                                </label>
                                <Select v-model="filterForm.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="status in statusOptions" :key="status.value" :value="status.value">
                                            {{ status.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Client Filter -->
                            <div v-if="clients?.length">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                    Client
                                </label>
                                <Select v-model="filterForm.client_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All clients" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All clients</SelectItem>
                                        <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                            {{ client.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                    Date Range
                                </label>
                                <div class="flex gap-2">
                                    <Input
                                        v-model="filterForm.date_from"
                                        type="date"
                                        class="flex-1"
                                    />
                                    <Input
                                        v-model="filterForm.date_to"
                                        type="date"
                                        class="flex-1"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <div class="flex items-center gap-2">
                                <Button @click="applyFilters" :disabled="isLoading">
                                    <RefreshCw :class="['h-4 w-4 mr-2', isLoading && 'animate-spin']" />
                                    Apply Filters
                                </Button>
                                <Button variant="outline" @click="clearFilters" v-if="hasActiveFilters">
                                    Clear All
                                </Button>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ invoices.total }} total invoices found
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Main Content -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>
                                    Invoices
                                    <span v-if="invoices.total > 0" class="text-sm font-normal text-gray-500 ml-2">
                                        ({{ invoices.from }}-{{ invoices.to }} of {{ invoices.total }})
                                    </span>
                                </CardTitle>
                                <CardDescription v-if="selectedInvoices.length > 0">
                                    {{ selectedInvoices.length }} invoice(s) selected
                                </CardDescription>
                            </div>

                            <div class="flex items-center gap-2">
                                <!-- Bulk Actions -->
                                <DropdownMenu v-if="selectedInvoices.length > 0">
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline" size="sm">
                                            Bulk Actions
                                            <ChevronDown class="ml-2 h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="performBulkAction('send')">
                                            <Send class="mr-2 h-4 w-4" />
                                            Send Selected
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="performBulkAction('export')">
                                            <Download class="mr-2 h-4 w-4" />
                                            Export Selected
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem 
                                            @click="performBulkAction('delete')"
                                            class="text-red-600 dark:text-red-400"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete Selected
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>

                                <!-- View Toggle -->
                                <div class="flex items-center border rounded-lg">
                                    <Button
                                        variant={viewMode === 'table' ? 'default' : 'ghost'}
                                        size="sm"
                                        @click="viewMode = 'table'"
                                        class="rounded-r-none"
                                    >
                                        <List class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant={viewMode === 'grid' ? 'default' : 'ghost'}
                                        size="sm"
                                        @click="viewMode = 'grid'"
                                        class="rounded-l-none border-l"
                                    >
                                        <Grid class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="p-0">
                        <!-- Table View -->
                        <div v-if="viewMode === 'table'" class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">
                                            <Checkbox
                                                :checked="allSelected"
                                                @update:checked="allSelected = $event"
                                            />
                                        </TableHead>
                                        
                                        <TableHead>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="sortBy('invoice_number')"
                                                class="h-auto p-0 hover:bg-transparent"
                                            >
                                                Invoice #
                                                <component
                                                    :is="getSortIcon('invoice_number')"
                                                    class="ml-2 h-4 w-4"
                                                />
                                            </Button>
                                        </TableHead>

                                        <TableHead>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="sortBy('client_name')"
                                                class="h-auto p-0 hover:bg-transparent"
                                            >
                                                Client
                                                <component
                                                    :is="getSortIcon('client_name')"
                                                    class="ml-2 h-4 w-4"
                                                />
                                            </Button>
                                        </TableHead>

                                        <TableHead>Status</TableHead>

                                        <TableHead>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="sortBy('total')"
                                                class="h-auto p-0 hover:bg-transparent"
                                            >
                                                Amount
                                                <component
                                                    :is="getSortIcon('total')"
                                                    class="ml-2 h-4 w-4"
                                                />
                                            </Button>
                                        </TableHead>

                                        <TableHead>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="sortBy('due_date')"
                                                class="h-auto p-0 hover:bg-transparent"
                                            >
                                                Due Date
                                                <component
                                                    :is="getSortIcon('due_date')"
                                                    class="ml-2 h-4 w-4"
                                                />
                                            </Button>
                                        </TableHead>

                                        <TableHead class="w-20">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="invoice in invoices.data"
                                        :key="invoice.id"
                                        :class="[
                                            'hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                            isInvoiceSelected(invoice.id) && 'bg-blue-50 dark:bg-blue-900/20'
                                        ]"
                                    >
                                        <TableCell>
                                            <Checkbox
                                                :checked="isInvoiceSelected(invoice.id)"
                                                @update:checked="toggleInvoiceSelection(invoice.id)"
                                            />
                                        </TableCell>

                                        <TableCell>
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0">
                                                    <FileText class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <div>
                                                    <Link
                                                        :href="`/invoices/${invoice.id}`"
                                                        class="font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                                    >
                                                        {{ invoice.invoice_number }}
                                                    </Link>
                                                    <p class="text-sm text-gray-500">
                                                        {{ formatDate(invoice.created_at) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ invoice.client.name }}
                                                </p>
                                                <p class="text-sm text-gray-500">
                                                    {{ invoice.client.email }}
                                                </p>
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <Badge :class="getStatusConfig(invoice.status).color">
                                                <component
                                                    :is="getStatusConfig(invoice.status).icon"
                                                    class="mr-1 h-3 w-3"
                                                />
                                                {{ getStatusConfig(invoice.status).label }}
                                            </Badge>
                                        </TableCell>

                                        <TableCell>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ formatCurrency(invoice.total, invoice.currency) }}
                                                </p>
                                                <p v-if="invoice.amount_paid > 0" class="text-sm text-gray-500">
                                                    {{ formatCurrency(invoice.amount_paid) }} paid
                                                </p>
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div>
                                                <p class="text-sm text-gray-900 dark:text-white">
                                                    {{ formatDate(invoice.due_date) }}
                                                </p>
                                                <p v-if="invoice.is_overdue" class="text-sm text-red-600">
                                                    {{ invoice.days_overdue }} days overdue
                                                </p>
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="`/invoices/${invoice.id}`">
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    
                                                    <DropdownMenuItem
                                                        v-if="invoice.status === 'draft'"
                                                        as-child
                                                    >
                                                        <Link :href="`/invoices/${invoice.id}/edit`">
                                                            <Edit2 class="mr-2 h-4 w-4" />
                                                            Edit
                                                        </Link>
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem
                                                        v-if="invoice.status === 'draft'"
                                                        @click="sendInvoice(invoice)"
                                                    >
                                                        <Send class="mr-2 h-4 w-4" />
                                                        Send
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem @click="downloadPDF(invoice)">
                                                        <Download class="mr-2 h-4 w-4" />
                                                        Download PDF
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem @click="duplicateInvoice(invoice)">
                                                        <Copy class="mr-2 h-4 w-4" />
                                                        Duplicate
                                                    </DropdownMenuItem>

                                                    <DropdownMenuSeparator />
                                                    
                                                    <DropdownMenuItem
                                                        @click="deleteInvoice(invoice)"
                                                        class="text-red-600 dark:text-red-400"
                                                    >
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Grid View -->
                        <div v-else-if="viewMode === 'grid'" class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <Card
                                    v-for="invoice in invoices.data"
                                    :key="invoice.id"
                                    :class="[
                                        'cursor-pointer hover:shadow-lg transition-shadow',
                                        isInvoiceSelected(invoice.id) && 'ring-2 ring-blue-500'
                                    ]"
                                    @click="toggleInvoiceSelection(invoice.id)"
                                >
                                    <CardContent class="p-4">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <Checkbox
                                                    :checked="isInvoiceSelected(invoice.id)"
                                                    @click.stop="toggleInvoiceSelection(invoice.id)"
                                                />
                                                <FileText class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <Badge :class="getStatusConfig(invoice.status).color">
                                                {{ getStatusConfig(invoice.status).label }}
                                            </Badge>
                                        </div>

                                        <div class="mb-3">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ invoice.invoice_number }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                {{ invoice.client.name }}
                                            </p>
                                        </div>

                                        <div class="flex items-center justify-between text-sm">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ formatCurrency(invoice.total, invoice.currency) }}
                                                </p>
                                                <p class="text-gray-500">
                                                    Due {{ formatDate(invoice.due_date) }}
                                                </p>
                                            </div>
                                            <DropdownMenu @click.stop>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="`/invoices/${invoice.id}`">
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="downloadPDF(invoice)">
                                                        <Download class="mr-2 h-4 w-4" />
                                                        Download PDF
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!invoices.data.length" class="text-center py-12">
                            <FileText class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                No invoices found
                            </h3>
                            <p class="mt-2 text-gray-500">
                                {{ hasActiveFilters ? 'Try adjusting your filters' : 'Get started by creating your first invoice' }}
                            </p>
                            <div class="mt-6">
                                <Link v-if="!hasActiveFilters" href="/invoices/create">
                                    <Button>
                                        <Plus class="mr-2 h-4 w-4" />
                                        Create Invoice
                                    </Button>
                                </Link>
                                <Button v-else variant="outline" @click="clearFilters">
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pagination -->
                <div v-if="invoices.last_page > 1" class="flex items-center justify-between mt-6">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} results
                    </p>
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="invoices.current_page === 1"
                            @click="router.get('/invoices', { ...filterForm.data(), page: invoices.current_page - 1 })"
                        >
                            Previous
                        </Button>
                        
                        <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
                            Page {{ invoices.current_page }} of {{ invoices.last_page }}
                        </span>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="invoices.current_page === invoices.last_page"
                            @click="router.get('/invoices', { ...filterForm.data(), page: invoices.current_page + 1 })"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </TooltipProvider>
    </AppLayout>
</template>

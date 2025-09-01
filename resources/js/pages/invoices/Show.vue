<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit2,
    Send,
    Download,
    Copy,
    Trash2,
    Plus,
    MoreHorizontal,
    Calendar,
    User,
    Mail,
    Phone,
    MapPin,
    CreditCard,
    Eye,
    CheckCircle,
    Clock,
    AlertTriangle,
    Printer,
    Share,
    Receipt,
    DollarSign,
    FileText
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Payment {
    id: number;
    amount: number;
    payment_date: string;
    method: string;
    reference?: string;
    notes?: string;
    created_at: string;
}

interface Client {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    client: Client;
    issue_date: string;
    due_date: string;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    subtotal: number;
    tax_rate: number;
    tax_amount: number;
    discount: number;
    total: number;
    amount_paid: number;
    currency: string;
    notes?: string;
    terms?: string;
    items: InvoiceItem[];
    payments: Payment[];
    created_at: string;
    updated_at: string;
    sent_at?: string;
    paid_at?: string;
    is_overdue?: boolean;
    days_overdue?: number;
    remaining_balance: number;
}

interface Props {
    invoice: Invoice;
    can_edit?: boolean;
    can_delete?: boolean;
}

const props = defineProps<Props>();

// Reactive state
const showPaymentDialog = ref(false);
const showDeleteDialog = ref(false);
const isProcessing = ref(false);

// Payment form
const paymentForm = useForm({
    amount: props.invoice.remaining_balance,
    payment_date: new Date().toISOString().split('T')[0],
    method: 'bank_transfer',
    reference: '',
    notes: ''
});

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Invoices', href: '/invoices' },
    { title: props.invoice.invoice_number }
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
        icon: AlertTriangle,
        label: 'Overdue'
    },
    cancelled: { 
        color: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400', 
        icon: FileText,
        label: 'Cancelled'
    }
};

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'check', label: 'Check' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'credit_card', label: 'Credit Card' },
    { value: 'paypal', label: 'PayPal' },
    { value: 'other', label: 'Other' }
];

// Computed properties
const statusInfo = computed(() => statusConfig[props.invoice.status]);

const isEditable = computed(() => 
    props.invoice.status === 'draft' && props.can_edit
);

const canSend = computed(() => 
    props.invoice.status === 'draft'
);

const canRecordPayment = computed(() => 
    ['sent', 'overdue'].includes(props.invoice.status) && props.invoice.remaining_balance > 0
);

const progressPercentage = computed(() => {
    if (props.invoice.total === 0) return 0;
    return (props.invoice.amount_paid / props.invoice.total) * 100;
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
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatShortDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Actions
const sendInvoice = () => {
    isProcessing.value = true;
    router.post(`/invoices/${props.invoice.id}/send`, {}, {
        onFinish: () => {
            isProcessing.value = false;
        }
    });
};

const duplicateInvoice = () => {
    router.post(`/invoices/${props.invoice.id}/duplicate`);
};

const deleteInvoice = () => {
    router.delete(`/invoices/${props.invoice.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
        }
    });
};

const downloadPDF = () => {
    window.open(`/invoices/${props.invoice.id}/pdf`, '_blank');
};

const printInvoice = () => {
    window.print();
};

const recordPayment = () => {
    paymentForm.post(`/invoices/${props.invoice.id}/payments`, {
        onSuccess: () => {
            showPaymentDialog.value = false;
            paymentForm.reset();
            paymentForm.amount = props.invoice.remaining_balance;
            paymentForm.payment_date = new Date().toISOString().split('T')[0];
        }
    });
};

const deletePayment = (paymentId: number) => {
    if (confirm('Are you sure you want to delete this payment?')) {
        router.delete(`/payments/${paymentId}`);
    }
};

const shareInvoice = () => {
    if (navigator.share) {
        navigator.share({
            title: `Invoice ${props.invoice.invoice_number}`,
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        // You could show a toast notification here
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Invoice ${invoice.invoice_number}`" />

        <TooltipProvider>
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <!-- Header -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
                    <div class="flex items-center gap-4">
                        <Link href="/invoices">
                            <Button variant="outline" size="icon">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ invoice.invoice_number }}
                                </h1>
                                <Badge :class="statusInfo.color" class="text-sm">
                                    <component :is="statusInfo.icon" class="mr-1 h-4 w-4" />
                                    {{ statusInfo.label }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                                <span>Created {{ formatShortDate(invoice.created_at) }}</span>
                                <span v-if="invoice.sent_at">• Sent {{ formatShortDate(invoice.sent_at) }}</span>
                                <span v-if="invoice.paid_at">• Paid {{ formatShortDate(invoice.paid_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Quick Actions -->
                        <Tooltip v-if="canRecordPayment">
                            <TooltipTrigger as-child>
                                <Button @click="showPaymentDialog = true" class="bg-green-600 hover:bg-green-700">
                                    <CreditCard class="h-4 w-4 mr-2" />
                                    Record Payment
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Record a payment for this invoice</p>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip v-if="canSend">
                            <TooltipTrigger as-child>
                                <Button @click="sendInvoice" :disabled="isProcessing">
                                    <Send class="h-4 w-4 mr-2" />
                                    Send Invoice
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Send this invoice to the client</p>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip v-if="isEditable">
                            <TooltipTrigger as-child>
                                <Button variant="outline" as-child>
                                    <Link :href="`/invoices/${invoice.id}/edit`">
                                        <Edit2 class="h-4 w-4 mr-2" />
                                        Edit
                                    </Link>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Edit this invoice</p>
                            </TooltipContent>
                        </Tooltip>

                        <!-- More Actions -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" size="icon">
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48">
                                <DropdownMenuItem @click="downloadPDF">
                                    <Download class="mr-2 h-4 w-4" />
                                    Download PDF
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="printInvoice">
                                    <Printer class="mr-2 h-4 w-4" />
                                    Print
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="shareInvoice">
                                    <Share class="mr-2 h-4 w-4" />
                                    Share
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="duplicateInvoice">
                                    <Copy class="mr-2 h-4 w-4" />
                                    Duplicate
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem 
                                    v-if="can_delete"
                                    @click="showDeleteDialog = true"
                                    class="text-red-600 dark:text-red-400"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <!-- Alert for overdue invoices -->
                <Alert v-if="invoice.is_overdue" class="mb-6 border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/10">
                    <AlertTriangle class="h-4 w-4 text-red-600" />
                    <AlertTitle class="text-red-800 dark:text-red-200">Invoice Overdue</AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-300">
                        This invoice was due {{ formatShortDate(invoice.due_date) }} and is {{ invoice.days_overdue }} days overdue.
                    </AlertDescription>
                </Alert>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Invoice Details -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Invoice Details</CardTitle>
                                <CardDescription>Invoice information and line items</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Basic Info -->
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <Label class="text-sm font-medium text-gray-600 dark:text-gray-400">Issue Date</Label>
                                        <p class="text-sm text-gray-900 dark:text-white mt-1">
                                            {{ formatDate(invoice.issue_date) }}
                                        </p>
                                    </div>
                                    <div>
                                        <Label class="text-sm font-medium text-gray-600 dark:text-gray-400">Due Date</Label>
                                        <p class="text-sm text-gray-900 dark:text-white mt-1">
                                            {{ formatDate(invoice.due_date) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Line Items -->
                                <div>
                                    <Label class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3 block">Line Items</Label>
                                    <div class="border rounded-lg overflow-hidden">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead class="text-left">Description</TableHead>
                                                    <TableHead class="text-center w-20">Qty</TableHead>
                                                    <TableHead class="text-right w-24">Unit Price</TableHead>
                                                    <TableHead class="text-right w-24">Total</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow v-for="item in invoice.items" :key="item.id">
                                                    <TableCell class="font-medium">
                                                        {{ item.description }}
                                                    </TableCell>
                                                    <TableCell class="text-center">
                                                        {{ item.quantity }}
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        {{ formatCurrency(item.unit_price, invoice.currency) }}
                                                    </TableCell>
                                                    <TableCell class="text-right font-medium">
                                                        {{ formatCurrency(item.total, invoice.currency) }}
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>

                                <!-- Totals -->
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                            <span class="text-gray-900 dark:text-white">{{ formatCurrency(invoice.subtotal, invoice.currency) }}</span>
                                        </div>
                                        
                                        <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                                            <span class="text-gray-900 dark:text-white">-{{ formatCurrency(invoice.discount, invoice.currency) }}</span>
                                        </div>
                                        
                                        <div v-if="invoice.tax_rate > 0" class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Tax ({{ invoice.tax_rate }}%):</span>
                                            <span class="text-gray-900 dark:text-white">{{ formatCurrency(invoice.tax_amount, invoice.currency) }}</span>
                                        </div>
                                        
                                        <Separator />
                                        
                                        <div class="flex justify-between font-semibold text-lg">
                                            <span class="text-gray-900 dark:text-white">Total:</span>
                                            <span class="text-gray-900 dark:text-white">{{ formatCurrency(invoice.total, invoice.currency) }}</span>
                                        </div>

                                        <div v-if="invoice.amount_paid > 0" class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Amount Paid:</span>
                                            <span class="text-green-600">{{ formatCurrency(invoice.amount_paid, invoice.currency) }}</span>
                                        </div>

                                        <div v-if="invoice.remaining_balance > 0" class="flex justify-between font-medium">
                                            <span class="text-gray-900 dark:text-white">Balance Due:</span>
                                            <span class="text-red-600">{{ formatCurrency(invoice.remaining_balance, invoice.currency) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes and Terms -->
                                <div v-if="invoice.notes || invoice.terms" class="space-y-4">
                                    <div v-if="invoice.notes">
                                        <Label class="text-sm font-medium text-gray-600 dark:text-gray-400">Notes</Label>
                                        <p class="text-sm text-gray-900 dark:text-white mt-1 whitespace-pre-wrap">{{ invoice.notes }}</p>
                                    </div>
                                    <div v-if="invoice.terms">
                                        <Label class="text-sm font-medium text-gray-600 dark:text-gray-400">Terms & Conditions</Label>
                                        <p class="text-sm text-gray-900 dark:text-white mt-1 whitespace-pre-wrap">{{ invoice.terms }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Payment History -->
                        <Card v-if="invoice.payments.length > 0">
                            <CardHeader>
                                <CardTitle>Payment History</CardTitle>
                                <CardDescription>All payments received for this invoice</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div
                                        v-for="payment in invoice.payments"
                                        :key="payment.id"
                                        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="flex-shrink-0 p-2 bg-green-100 dark:bg-green-900/20 rounded-full">
                                                <Receipt class="h-5 w-5 text-green-600" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ formatCurrency(payment.amount, invoice.currency) }}
                                                </p>
                                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <span>{{ payment.method.replace('_', ' ').toUpperCase() }}</span>
                                                    <span>•</span>
                                                    <span>{{ formatShortDate(payment.payment_date) }}</span>
                                                    <span v-if="payment.reference">• Ref: {{ payment.reference }}</span>
                                                </div>
                                                <p v-if="payment.notes" class="text-sm text-gray-500 mt-1">
                                                    {{ payment.notes }}
                                                </p>
                                            </div>
                                        </div>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="deletePayment(payment.id)" class="text-red-600 dark:text-red-400">
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete Payment
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Client Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <User class="h-5 w-5" />
                                    Client Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Link 
                                        :href="`/clients/${invoice.client.id}`"
                                        class="font-semibold text-lg text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ invoice.client.name }}
                                    </Link>
                                </div>

                                <div class="space-y-2">
                                    <div v-if="invoice.client.email" class="flex items-center gap-2 text-sm">
                                        <Mail class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                        <a 
                                            :href="`mailto:${invoice.client.email}`"
                                            class="text-blue-600 dark:text-blue-400 hover:underline"
                                        >
                                            {{ invoice.client.email }}
                                        </a>
                                    </div>

                                    <div v-if="invoice.client.phone" class="flex items-center gap-2 text-sm">
                                        <Phone class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                        <a 
                                            :href="`tel:${invoice.client.phone}`"
                                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                                        >
                                            {{ invoice.client.phone }}
                                        </a>
                                    </div>

                                    <div v-if="invoice.client.address" class="flex items-start gap-2 text-sm">
                                        <MapPin class="h-4 w-4 text-gray-400 flex-shrink-0 mt-0.5" />
                                        <div class="text-gray-600 dark:text-gray-400">
                                            <div>{{ invoice.client.address }}</div>
                                            <div v-if="invoice.client.city || invoice.client.state || invoice.client.postal_code">
                                                {{ invoice.client.city }}{{ invoice.client.city && (invoice.client.state || invoice.client.postal_code) ? ', ' : '' }}
                                                {{ invoice.client.state }} {{ invoice.client.postal_code }}
                                            </div>
                                            <div v-if="invoice.client.country">{{ invoice.client.country }}</div>
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <div class="flex items-center justify-between pt-2">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/clients/${invoice.client.id}`">
                                            <Eye class="h-4 w-4 mr-2" />
                                            View Client
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Payment Progress -->
                        <Card v-if="invoice.amount_paid > 0">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <DollarSign class="h-5 w-5" />
                                    Payment Progress
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                        <span class="font-medium">{{ Math.round(progressPercentage) }}%</span>
                                    </div>
                                    
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div 
                                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            :style="{ width: `${progressPercentage}%` }"
                                        ></div>
                                    </div>

                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Paid</span>
                                            <span class="text-green-600 font-medium">
                                                {{ formatCurrency(invoice.amount_paid, invoice.currency) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Remaining</span>
                                            <span class="text-gray-900 dark:text-white font-medium">
                                                {{ formatCurrency(invoice.remaining_balance, invoice.currency) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Timeline/Activity -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Calendar class="h-5 w-5" />
                                    Activity Timeline
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <!-- Invoice Created -->
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-gray-400 mt-2"></div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">Invoice created</p>
                                            <p class="text-xs text-gray-500">{{ formatShortDate(invoice.created_at) }}</p>
                                        </div>
                                    </div>

                                    <!-- Invoice Sent -->
                                    <div v-if="invoice.sent_at" class="flex gap-3">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">Invoice sent</p>
                                            <p class="text-xs text-gray-500">{{ formatShortDate(invoice.sent_at) }}</p>
                                        </div>
                                    </div>

                                    <!-- Payments -->
                                    <div v-for="payment in invoice.payments" :key="`timeline-${payment.id}`" class="flex gap-3">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                Payment received: {{ formatCurrency(payment.amount, invoice.currency) }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ formatShortDate(payment.payment_date) }}</p>
                                        </div>
                                    </div>

                                    <!-- Invoice Paid -->
                                    <div v-if="invoice.paid_at" class="flex gap-3">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-green-600 mt-2"></div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">Invoice fully paid</p>
                                            <p class="text-xs text-gray-500">{{ formatShortDate(invoice.paid_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Record Payment Dialog -->
                <Dialog v-model:open="showPaymentDialog">
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Record Payment</DialogTitle>
                            <DialogDescription>
                                Record a payment for invoice {{ invoice.invoice_number }}
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="recordPayment" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="amount">Amount</Label>
                                    <Input
                                        id="amount"
                                        v-model="paymentForm.amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :max="invoice.remaining_balance"
                                        class="mt-1"
                                        required
                                    />
                                    <div v-if="paymentForm.errors.amount" class="text-sm text-red-600 mt-1">
                                        {{ paymentForm.errors.amount }}
                                    </div>
                                </div>

                                <div>
                                    <Label for="payment_date">Payment Date</Label>
                                    <Input
                                        id="payment_date"
                                        v-model="paymentForm.payment_date"
                                        type="date"
                                        class="mt-1"
                                        required
                                    />
                                </div>
                            </div>

                            <div>
                                <Label for="method">Payment Method</Label>
                                <Select v-model="paymentForm.method">
                                    <SelectTrigger class="mt-1">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                            {{ method.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label for="reference">Reference (optional)</Label>
                                <Input
                                    id="reference"
                                    v-model="paymentForm.reference"
                                    placeholder="Transaction ID, check number, etc."
                                    class="mt-1"
                                />
                            </div>

                            <div>
                                <Label for="notes">Notes (optional)</Label>
                                <Textarea
                                    id="notes"
                                    v-model="paymentForm.notes"
                                    placeholder="Additional payment notes..."
                                    class="mt-1"
                                />
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" @click="showPaymentDialog = false">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="paymentForm.processing">
                                    Record Payment
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                <!-- Delete Confirmation Dialog -->
                <Dialog v-model:open="showDeleteDialog">
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Delete Invoice</DialogTitle>
                            <DialogDescription>
                                Are you sure you want to delete invoice {{ invoice.invoice_number }}? This action cannot be undone.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant="outline" @click="showDeleteDialog = false">
                                Cancel
                            </Button>
                            <Button variant="destructive" @click="deleteInvoice">
                                Delete Invoice
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </TooltipProvider>
    </AppLayout>
</template>

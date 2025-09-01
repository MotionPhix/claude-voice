<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
    Eye
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
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Invoices', href: '/invoices' },
    { title: props.invoice.invoice_number, href: `/invoices/${props.invoice.id}` }
];

// State
const showPaymentDialog = ref(false);
const showPreviewDialog = ref(false);
const paymentForm = ref({
    amount: props.invoice.total - props.invoice.amount_paid,
    payment_date: new Date().toISOString().split('T')[0],
    method: 'bank_transfer',
    reference: '',
    notes: ''
});

// Status configuration
const statusConfig = {
    draft: { color: 'bg-gray-100 text-gray-800', label: 'Draft' },
    sent: { color: 'bg-blue-100 text-blue-800', label: 'Sent' },
    paid: { color: 'bg-green-100 text-green-800', label: 'Paid' },
    overdue: { color: 'bg-red-100 text-red-800', label: 'Overdue' },
    cancelled: { color: 'bg-gray-100 text-gray-600', label: 'Cancelled' }
};

const paymentMethods = {
    cash: 'Cash',
    check: 'Check',
    bank_transfer: 'Bank Transfer',
    credit_card: 'Credit Card',
    paypal: 'PayPal',
    other: 'Other'
};

// Computed properties
const remainingBalance = computed(() => props.invoice.total - props.invoice.amount_paid);
const canEdit = computed(() => props.invoice.status === 'draft');

// Helper functions
const formatCurrency = (amount: number, currency: string = 'USD'): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Actions
const handleEdit = () => {
    router.visit(`/invoices/${props.invoice.id}/edit`);
};

const handleDelete = () => {
    if (confirm(`Are you sure you want to delete invoice ${props.invoice.invoice_number}?`)) {
        router.delete(`/invoices/${props.invoice.id}`, {
            onSuccess: () => {
                router.visit('/invoices');
            }
        });
    }
};

const handleSendInvoice = () => {
    router.post(`/invoices/${props.invoice.id}/send`, {}, {
        onSuccess: () => {
            // Handle success
        }
    });
};

const handleDuplicate = () => {
    router.post(`/invoices/${props.invoice.id}/duplicate`, {}, {
        onSuccess: (page) => {
            const newInvoice = page.props.invoice as Invoice;
            router.visit(`/invoices/${newInvoice.id}/edit`);
        }
    });
};

const handleAddPayment = () => {
    router.post(`/invoices/${props.invoice.id}/payments`, paymentForm.value, {
        onSuccess: () => {
            showPaymentDialog.value = false;
            paymentForm.value = {
                amount: remainingBalance.value,
                payment_date: new Date().toISOString().split('T')[0],
                method: 'bank_transfer',
                reference: '',
                notes: ''
            };
        }
    });
};

const handleDownloadPDF = () => {
    window.open(`/invoices/${props.invoice.id}/pdf`, '_blank');
};

const handleShowPreview = () => {
    showPreviewDialog.value = true;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Invoice ${invoice.invoice_number}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/invoices">
                        <Button variant="outline" size="icon">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ invoice.invoice_number }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <Badge :class="statusConfig[invoice.status].color">
                                {{ statusConfig[invoice.status].label }}
                            </Badge>
                            <span class="text-sm text-muted-foreground">
                                Created {{ formatDate(invoice.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="handleShowPreview">
                        <Eye class="h-4 w-4 mr-2" />
                        Preview
                    </Button>

                    <Button variant="outline" @click="handleDownloadPDF">
                        <Download class="h-4 w-4 mr-2" />
                        Download PDF
                    </Button>

                    <Button
                        v-if="canEdit"
                        variant="outline"
                        @click="handleEdit"
                    >
                        <Edit2 class="h-4 w-4 mr-2" />
                        Edit
                    </Button>

                    <Button
                        v-if="invoice.status === 'draft'"
                        @click="handleSendInvoice"
                    >
                        <Send class="h-4 w-4 mr-2" />
                        Send Invoice
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon">
                                <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="handleDuplicate">
                                <Copy class="h-4 w-4 mr-2" />
                                Duplicate
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                @click="handleDelete"
                                class="text-red-600"
                                v-if="invoice.status !== 'paid'"
                            >
                                <Trash2 class="h-4 w-4 mr-2" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Invoice Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Issue Date</Label>
                                    <div class="flex items-center mt-1">
                                        <Calendar class="h-4 w-4 mr-2 text-muted-foreground" />
                                        <span>{{ formatDate(invoice.issue_date) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">Due Date</Label>
                                    <div class="flex items-center mt-1">
                                        <Calendar class="h-4 w-4 mr-2 text-muted-foreground" />
                                        <span>{{ formatDate(invoice.due_date) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="invoice.notes">
                                <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                                <p class="mt-1">{{ invoice.notes }}</p>
                            </div>

                            <div v-if="invoice.terms">
                                <Label class="text-sm font-medium text-muted-foreground">Terms</Label>
                                <p class="mt-1">{{ invoice.terms }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Client Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Client Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                        <User class="h-5 w-5 text-primary" />
                                    </div>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <h3 class="font-semibold">{{ invoice.client.name }}</h3>
                                    <div class="space-y-1 text-sm text-muted-foreground">
                                        <div class="flex items-center">
                                            <Mail class="h-4 w-4 mr-2" />
                                            <span>{{ invoice.client.email }}</span>
                                        </div>
                                        <div v-if="invoice.client.phone" class="flex items-center">
                                            <Phone class="h-4 w-4 mr-2" />
                                            <span>{{ invoice.client.phone }}</span>
                                        </div>
                                        <div v-if="invoice.client.address" class="flex items-start">
                                            <MapPin class="h-4 w-4 mr-2 mt-0.5" />
                                            <div>
                                                <div>{{ invoice.client.address }}</div>
                                                <div v-if="invoice.client.city || invoice.client.state || invoice.client.postal_code">
                                                    {{ invoice.client.city }}{{ invoice.client.city && (invoice.client.state || invoice.client.postal_code) ? ', ' : '' }}{{ invoice.client.state }} {{ invoice.client.postal_code }}
                                                </div>
                                                <div v-if="invoice.client.country">{{ invoice.client.country }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Invoice Items -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Items</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Description</TableHead>
                                            <TableHead class="text-center">Qty</TableHead>
                                            <TableHead class="text-right">Unit Price</TableHead>
                                            <TableHead class="text-right">Total</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="item in invoice.items" :key="item.id">
                                            <TableCell class="font-medium">{{ item.description }}</TableCell>
                                            <TableCell class="text-center">{{ item.quantity }}</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(item.unit_price, invoice.currency) }}</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(item.total, invoice.currency) }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>

                            <!-- Totals -->
                            <div class="flex justify-end mt-4">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Subtotal:</span>
                                        <span>{{ formatCurrency(invoice.subtotal, invoice.currency) }}</span>
                                    </div>
                                    <div v-if="invoice.discount > 0" class="flex justify-between text-sm">
                                        <span>Discount:</span>
                                        <span>-{{ formatCurrency(invoice.discount, invoice.currency) }}</span>
                                    </div>
                                    <div v-if="invoice.tax_amount > 0" class="flex justify-between text-sm">
                                        <span>Tax ({{ invoice.tax_rate }}%):</span>
                                        <span>{{ formatCurrency(invoice.tax_amount, invoice.currency) }}</span>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between font-semibold">
                                        <span>Total:</span>
                                        <span>{{ formatCurrency(invoice.total, invoice.currency) }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Payments -->
                    <Card v-if="invoice.payments.length > 0">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Payment History</CardTitle>
                                <Badge variant="secondary">
                                    {{ invoice.payments.length }} payment{{ invoice.payments.length !== 1 ? 's' : '' }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="payment in invoice.payments" :key="payment.id" class="flex items-center justify-between p-4 border rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                            <CreditCard class="h-4 w-4 text-green-600" />
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ formatCurrency(payment.amount, invoice.currency) }}</div>
                                            <div class="text-sm text-muted-foreground">
                                                {{ paymentMethods[payment.method] }} • {{ formatDate(payment.payment_date) }}
                                            </div>
                                            <div v-if="payment.reference" class="text-xs text-muted-foreground">
                                                Ref: {{ payment.reference }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Payment Status -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Total Amount:</span>
                                    <span class="font-medium">{{ formatCurrency(invoice.total, invoice.currency) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Amount Paid:</span>
                                    <span class="font-medium">{{ formatCurrency(invoice.amount_paid, invoice.currency) }}</span>
                                </div>
                                <Separator />
                                <div class="flex justify-between">
                                    <span class="font-medium">Balance Due:</span>
                                    <span class="font-semibold text-lg">{{ formatCurrency(remainingBalance, invoice.currency) }}</span>
                                </div>
                            </div>

                            <Dialog v-model:open="showPaymentDialog">
                                <DialogTrigger as-child>
                                    <Button
                                        v-if="remainingBalance > 0"
                                        class="w-full">
                                        <Plus class="h-4 w-4 mr-2" />
                                        Add Payment
                                    </Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-md">
                                    <DialogHeader>
                                        <DialogTitle>Add Payment</DialogTitle>
                                        <DialogDescription>
                                            Record a payment for this invoice.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div class="space-y-4">
                                        <div>
                                            <Label for="amount">Amount</Label>
                                            <Input
                                                id="amount"
                                                v-model.number="paymentForm.amount"
                                                type="number"
                                                step="0.01"
                                                :max="remainingBalance"
                                                class="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label for="payment_date">Payment Date</Label>
                                            <Input
                                                id="payment_date"
                                                v-model="paymentForm.payment_date"
                                                type="date"
                                                class="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label for="method">Payment Method</Label>
                                            <Select v-model="paymentForm.method">
                                                <SelectTrigger class="mt-1">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="(label, value) in paymentMethods" :key="value" :value="value">
                                                        {{ label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div>
                                            <Label for="reference">Reference (Optional)</Label>
                                            <Input
                                                id="reference"
                                                v-model="paymentForm.reference"
                                                placeholder="Transaction ID, Check #, etc."
                                                class="mt-1"
                                            />
                                        </div>
                                        <div>
                                            <Label for="notes">Notes (Optional)</Label>
                                            <Textarea
                                                id="notes"
                                                v-model="paymentForm.notes"
                                                placeholder="Additional payment notes..."
                                                class="mt-1"
                                            />
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button variant="outline" @click="showPaymentDialog = false">
                                            Cancel
                                        </Button>
                                        <Button @click="handleAddPayment">
                                            Add Payment
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" size="sm" class="w-full justify-start" @click="handleShowPreview">
                                <Eye class="h-4 w-4 mr-2" />
                                Preview Invoice
                            </Button>
                            <Button variant="outline" size="sm" class="w-full justify-start" @click="handleDownloadPDF">
                                <Download class="h-4 w-4 mr-2" />
                                Download PDF
                            </Button>
                            <Button variant="outline" size="sm" class="w-full justify-start" @click="handleDuplicate">
                                <Copy class="h-4 w-4 mr-2" />
                                Duplicate Invoice
                            </Button>
                            <Button
                                v-if="invoice.status === 'draft'"
                                variant="outline"
                                size="sm"
                                class="w-full justify-start"
                                @click="handleSendInvoice"
                            >
                                <Send class="h-4 w-4 mr-2" />
                                Send to Client
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Preview Dialog -->
        <Dialog v-model:open="showPreviewDialog">
            <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Invoice Preview</DialogTitle>
                    <DialogDescription>
                        Preview how your invoice will appear when sent to the client.
                    </DialogDescription>
                </DialogHeader>

                <!-- Invoice Preview Content -->
                <div class="bg-white p-8 rounded-lg border">
                    <!-- Company Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold mb-2">Your Company Name</h1>
                        <div class="text-sm text-gray-600">
                            <div>123 Business Street</div>
                            <div>City, State 12345</div>
                            <div>Phone: (555) 123-4567</div>
                            <div>Email: hello@company.com</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <!-- Bill To -->
                        <div>
                            <h3 class="font-semibold mb-2">Bill To:</h3>
                            <div class="text-sm">
                                <div class="font-medium">{{ invoice.client.name }}</div>
                                <div>{{ invoice.client.email }}</div>
                                <div v-if="invoice.client.phone">{{ invoice.client.phone }}</div>
                                <div v-if="invoice.client.address">
                                    <div>{{ invoice.client.address }}</div>
                                    <div v-if="invoice.client.city || invoice.client.state || invoice.client.postal_code">
                                        {{ invoice.client.city }}{{ invoice.client.city && (invoice.client.state || invoice.client.postal_code) ? ', ' : '' }}{{ invoice.client.state }} {{ invoice.client.postal_code }}
                                    </div>
                                    <div v-if="invoice.client.country">{{ invoice.client.country }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Info -->
                        <div class="text-right">
                            <h2 class="text-3xl font-bold mb-4">INVOICE</h2>
                            <div class="text-sm space-y-1">
                                <div><strong>Invoice #:</strong> {{ invoice.invoice_number }}</div>
                                <div><strong>Issue Date:</strong> {{ formatDate(invoice.issue_date) }}</div>
                                <div><strong>Due Date:</strong> {{ formatDate(invoice.due_date) }}</div>
                                <div class="mt-2">
                                    <Badge :class="statusConfig[invoice.status].color">
                                        {{ statusConfig[invoice.status].label }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-8">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b-2 border-gray-300">
                                    <th class="text-left py-2 font-semibold">Description</th>
                                    <th class="text-center py-2 font-semibold">Qty</th>
                                    <th class="text-right py-2 font-semibold">Unit Price</th>
                                    <th class="text-right py-2 font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in invoice.items" :key="item.id" class="border-b border-gray-200">
                                    <td class="py-3">{{ item.description }}</td>
                                    <td class="py-3 text-center">{{ item.quantity }}</td>
                                    <td class="py-3 text-right">{{ formatCurrency(item.unit_price, invoice.currency) }}</td>
                                    <td class="py-3 text-right">{{ formatCurrency(item.total, invoice.currency) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="flex justify-end mb-8">
                        <div class="w-64">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>{{ formatCurrency(invoice.subtotal, invoice.currency) }}</span>
                                </div>
                                <div v-if="invoice.discount > 0" class="flex justify-between">
                                    <span>Discount:</span>
                                    <span>-{{ formatCurrency(invoice.discount, invoice.currency) }}</span>
                                </div>
                                <div v-if="invoice.tax_amount > 0" class="flex justify-between">
                                    <span>Tax ({{ invoice.tax_rate }}%):</span>
                                    <span>{{ formatCurrency(invoice.tax_amount, invoice.currency) }}</span>
                                </div>
                                <div class="border-t pt-2 mt-2">
                                    <div class="flex justify-between font-bold text-lg">
                                        <span>Total:</span>
                                        <span>{{ formatCurrency(invoice.total, invoice.currency) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes and Terms -->
                    <div v-if="invoice.notes || invoice.terms" class="space-y-4 text-sm">
                        <div v-if="invoice.notes">
                            <h4 class="font-semibold mb-1">Notes:</h4>
                            <p class="text-gray-600">{{ invoice.notes }}</p>
                        </div>
                        <div v-if="invoice.terms">
                            <h4 class="font-semibold mb-1">Terms & Conditions:</h4>
                            <p class="text-gray-600">{{ invoice.terms }}</p>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showPreviewDialog = false">
                        Close
                    </Button>
                    <Button @click="handleDownloadPDF">
                        <Download class="h-4 w-4 mr-2" />
                        Download PDF
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

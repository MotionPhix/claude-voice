<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
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
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
    companyInfo?: {
        name?: string;
        address?: string;
        city?: string;
        state?: string;
        postal_code?: string;
        phone?: string;
        email?: string;
    };
}

const props = defineProps<Props>();

// Status configuration
const statusConfig = {
    draft: { color: 'bg-gray-100 text-gray-800', label: 'Draft' },
    sent: { color: 'bg-blue-100 text-blue-800', label: 'Sent' },
    paid: { color: 'bg-green-100 text-green-800', label: 'Paid' },
    overdue: { color: 'bg-red-100 text-red-800', label: 'Overdue' },
    cancelled: { color: 'bg-gray-100 text-gray-600', label: 'Cancelled' }
};

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

// Computed properties
const company = computed(() => ({
    name: props.companyInfo?.name || 'Your Company Name',
    address: props.companyInfo?.address || '123 Business Street',
    city: props.companyInfo?.city || 'City',
    state: props.companyInfo?.state || 'State',
    postal_code: props.companyInfo?.postal_code || '12345',
    phone: props.companyInfo?.phone || '(555) 123-4567',
    email: props.companyInfo?.email || 'hello@company.com'
}));
</script>

<template>
    <div class="bg-white p-8 rounded-lg border shadow-sm max-w-4xl mx-auto">
        <!-- Company Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-4">{{ company.name }}</h1>
            <div class="text-sm text-gray-600 space-y-1">
                <div>{{ company.address }}</div>
                <div>{{ company.city }}, {{ company.state }} {{ company.postal_code }}</div>
                <div class="flex gap-4 mt-2">
                    <span>Phone: {{ company.phone }}</span>
                    <span>Email: {{ company.email }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Bill To -->
            <div>
                <h3 class="font-semibold mb-3 text-gray-800">Bill To:</h3>
                <div class="text-sm space-y-1">
                    <div class="font-medium text-gray-900">{{ invoice.client.name }}</div>
                    <div class="text-gray-600">{{ invoice.client.email }}</div>
                    <div v-if="invoice.client.phone" class="text-gray-600">{{ invoice.client.phone }}</div>
                    <div v-if="invoice.client.address" class="text-gray-600 space-y-1">
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
                <h2 class="text-4xl font-bold mb-4 text-gray-900">INVOICE</h2>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Invoice #:</span>
                        <span class="font-semibold">{{ invoice.invoice_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Issue Date:</span>
                        <span>{{ formatDate(invoice.issue_date) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">Due Date:</span>
                        <span>{{ formatDate(invoice.due_date) }}</span>
                    </div>
                    <div class="flex justify-end mt-3">
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
                        <th class="text-left py-3 px-2 font-semibold text-gray-800">Description</th>
                        <th class="text-center py-3 px-2 font-semibold text-gray-800 w-20">Qty</th>
                        <th class="text-right py-3 px-2 font-semibold text-gray-800 w-28">Unit Price</th>
                        <th class="text-right py-3 px-2 font-semibold text-gray-800 w-28">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in invoice.items" :key="item.id" class="border-b border-gray-200">
                        <td class="py-4 px-2 text-gray-800">{{ item.description }}</td>
                        <td class="py-4 px-2 text-center text-gray-600">{{ item.quantity }}</td>
                        <td class="py-4 px-2 text-right text-gray-600">{{ formatCurrency(item.unit_price, invoice.currency) }}</td>
                        <td class="py-4 px-2 text-right font-medium text-gray-800">{{ formatCurrency(item.total, invoice.currency) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-72">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-1">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium">{{ formatCurrency(invoice.subtotal, invoice.currency) }}</span>
                    </div>
                    <div v-if="invoice.discount > 0" class="flex justify-between py-1">
                        <span class="text-gray-600">Discount:</span>
                        <span class="font-medium text-red-600">-{{ formatCurrency(invoice.discount, invoice.currency) }}</span>
                    </div>
                    <div v-if="invoice.tax_amount > 0" class="flex justify-between py-1">
                        <span class="text-gray-600">Tax ({{ invoice.tax_rate }}%):</span>
                        <span class="font-medium">{{ formatCurrency(invoice.tax_amount, invoice.currency) }}</span>
                    </div>
                    <div class="border-t-2 border-gray-300 pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="font-bold text-lg text-gray-800">Total:</span>
                            <span class="font-bold text-xl text-gray-900">{{ formatCurrency(invoice.total, invoice.currency) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div v-if="invoice.amount_paid > 0" class="mb-8 p-4 bg-green-50 border border-green-200 rounded-lg">
            <h4 class="font-semibold text-green-800 mb-2">Payment Information</h4>
            <div class="text-sm text-green-700 space-y-1">
                <div class="flex justify-between">
                    <span>Amount Paid:</span>
                    <span class="font-medium">{{ formatCurrency(invoice.amount_paid, invoice.currency) }}</span>
                </div>
                <div v-if="invoice.total - invoice.amount_paid > 0" class="flex justify-between">
                    <span>Balance Due:</span>
                    <span class="font-medium">{{ formatCurrency(invoice.total - invoice.amount_paid, invoice.currency) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes and Terms -->
        <div v-if="invoice.notes || invoice.terms" class="space-y-6 text-sm">
            <div v-if="invoice.notes">
                <h4 class="font-semibold mb-2 text-gray-800">Notes:</h4>
                <div class="text-gray-600 whitespace-pre-line leading-relaxed">{{ invoice.notes }}</div>
            </div>
            <div v-if="invoice.terms">
                <h4 class="font-semibold mb-2 text-gray-800">Terms & Conditions:</h4>
                <div class="text-gray-600 whitespace-pre-line leading-relaxed">{{ invoice.terms }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 pt-6 border-t border-gray-200 text-center text-xs text-gray-500">
            <p>Thank you for your business!</p>
            <p class="mt-1">{{ company.name }} • {{ company.email }} • {{ company.phone }}</p>
        </div>
    </div>
</template>

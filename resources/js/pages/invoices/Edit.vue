<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Save,
    Send,
    Plus,
    Trash2,
    Calculator,
    AlertTriangle
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

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

interface InvoiceItem {
    id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Invoice {
    id: number;
    invoice_number: string;
    client_id: number;
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
    clients: Client[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Invoices', href: '/invoices' },
    { title: props.invoice.invoice_number, href: `/invoices/${props.invoice.id}` },
    { title: 'Edit', href: `/invoices/${props.invoice.id}/edit` }
];

// Form state
const form = useForm({
    client_id: props.invoice.client_id.toString(),
    invoice_number: props.invoice.invoice_number,
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date,
    notes: props.invoice.notes || '',
    terms: props.invoice.terms || '',
    tax_rate: props.invoice.tax_rate,
    discount: (props.invoice.discount / props.invoice.subtotal * 100) || 0,
    currency: props.invoice.currency,
    items: props.invoice.items.map(item => ({
        id: item.id,
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
        total: item.total
    })) as InvoiceItem[]
});

// Client dialog state
const showClientDialog = ref(false);
const clientForm = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: ''
});

const currencies = [
    { value: 'USD', label: 'USD - US Dollar' },
    { value: 'EUR', label: 'EUR - Euro' },
    { value: 'GBP', label: 'GBP - British Pound' },
    { value: 'CAD', label: 'CAD - Canadian Dollar' },
    { value: 'AUD', label: 'AUD - Australian Dollar' },
    { value: 'ZAR', label: 'ZAR - South African Rand' }
];

// Computed properties
const selectedClient = computed(() => {
    return props.clients.find(client => client.id.toString() === form.client_id);
});

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + item.total, 0);
});

const discountAmount = computed(() => {
    return (subtotal.value * form.discount) / 100;
});

const taxableAmount = computed(() => {
    return subtotal.value - discountAmount.value;
});

const taxAmount = computed(() => {
    return (taxableAmount.value * form.tax_rate) / 100;
});

const total = computed(() => {
    return taxableAmount.value + taxAmount.value;
});

const canEdit = computed(() => props.invoice.status === 'draft');

// Helper functions
const formatCurrency = (amount: number, currency: string = 'USD'): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

const calculateItemTotal = (index: number) => {
    const item = form.items[index];
    item.total = item.quantity * item.unit_price;
};

const addItem = () => {
    form.items.push({
        description: '',
        quantity: 1,
        unit_price: 0,
        total: 0
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const createClient = () => {
    clientForm.post('/clients', {
        onSuccess: (page) => {
            const newClient = page.props.client as Client;
            props.clients.push(newClient);
            form.client_id = newClient.id.toString();
            showClientDialog.value = false;
            clientForm.reset();
        }
    });
};

const saveChanges = () => {
    form.put(`/invoices/${props.invoice.id}`, {
        onSuccess: () => {
            router.visit(`/invoices/${props.invoice.id}`);
        }
    });
};

const saveAndSend = () => {
    form.put(`/invoices/${props.invoice.id}?send=true`, {
        onSuccess: () => {
            router.visit(`/invoices/${props.invoice.id}`);
        }
    });
};

// Watch for item changes to recalculate totals
watch(() => form.items, () => {
    form.items.forEach((item, index) => {
        calculateItemTotal(index);
    });
}, { deep: true });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit Invoice ${invoice.invoice_number}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="`/invoices/${invoice.id}`">
                        <Button variant="outline" size="icon">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">Edit Invoice {{ invoice.invoice_number }}</h1>
                        <p class="text-muted-foreground" v-if="!canEdit">
                            This invoice cannot be edited because it has been sent or paid
                        </p>
                        <p class="text-muted-foreground" v-else>
                            Make changes to your invoice
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2" v-if="canEdit">
                    <Button variant="outline" @click="saveChanges" :disabled="form.processing">
                        <Save class="h-4 w-4 mr-2" />
                        Save Changes
                    </Button>
                    <Button @click="saveAndSend" :disabled="form.processing">
                        <Send class="h-4 w-4 mr-2" />
                        Save & Send
                    </Button>
                </div>
                <div v-else class="text-sm text-muted-foreground">
                    Invoice is {{ invoice.status }} and cannot be edited
                </div>
            </div>

            <!-- Read-only notice -->
            <div v-if="!canEdit" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center gap-2">
                    <AlertTriangle class="h-4 w-4 text-yellow-600" />
                    <p class="text-yellow-800 font-medium">This invoice cannot be edited</p>
                </div>
                <p class="text-yellow-700 text-sm mt-1">
                    Once an invoice is sent or paid, it can only be viewed. Create a new invoice or duplicate this one to make changes.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Invoice Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                            <CardDescription>Basic information about the invoice</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="invoice_number">Invoice Number</Label>
                                    <Input
                                        id="invoice_number"
                                        v-model="form.invoice_number"
                                        :disabled="!canEdit"
                                        class="mt-1"
                                    />
                                    <div v-if="form.errors.invoice_number" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.invoice_number }}
                                    </div>
                                </div>
                                <div>
                                    <Label for="currency">Currency</Label>
                                    <Select v-model="form.currency" :disabled="!canEdit">
                                        <SelectTrigger class="mt-1">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="currency in currencies" :key="currency.value" :value="currency.value">
                                                {{ currency.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="issue_date">Issue Date</Label>
                                    <Input
                                        id="issue_date"
                                        v-model="form.issue_date"
                                        type="date"
                                        :disabled="!canEdit"
                                        class="mt-1"
                                    />
                                    <div v-if="form.errors.issue_date" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.issue_date }}
                                    </div>
                                </div>
                                <div>
                                    <Label for="due_date">Due Date</Label>
                                    <Input
                                        id="due_date"
                                        v-model="form.due_date"
                                        type="date"
                                        :disabled="!canEdit"
                                        class="mt-1"
                                    />
                                    <div v-if="form.errors.due_date" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.due_date }}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Client Information -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Client Information</CardTitle>
                                    <CardDescription>Select or create a client for this invoice</CardDescription>
                                </div>
                                <Dialog v-model:open="showClientDialog" v-if="canEdit">
                                    <DialogTrigger as-child>
                                        <Button variant="outline" size="sm">
                                            <Plus class="h-4 w-4 mr-2" />
                                            New Client
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent class="sm:max-w-md">
                                        <DialogHeader>
                                            <DialogTitle>Create New Client</DialogTitle>
                                            <DialogDescription>
                                                Add a new client to your database.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <div class="space-y-4">
                                            <div>
                                                <Label for="client_name">Name</Label>
                                                <Input
                                                    id="client_name"
                                                    v-model="clientForm.name"
                                                    placeholder="Client name"
                                                    class="mt-1"
                                                />
                                            </div>
                                            <div>
                                                <Label for="client_email">Email</Label>
                                                <Input
                                                    id="client_email"
                                                    v-model="clientForm.email"
                                                    type="email"
                                                    placeholder="client@example.com"
                                                    class="mt-1"
                                                />
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button variant="outline" @click="showClientDialog = false">
                                                Cancel
                                            </Button>
                                            <Button @click="createClient" :disabled="clientForm.processing">
                                                Create Client
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <Label for="client_id">Select Client</Label>
                                <Select v-model="form.client_id" :disabled="!canEdit">
                                    <SelectTrigger class="mt-1">
                                        <SelectValue placeholder="Choose a client..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                            {{ client.name }} - {{ client.email }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <Card v-if="canEdit">
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" size="sm" class="w-full" @click="saveChanges" :disabled="form.processing">
                                <Save class="h-4 w-4 mr-2" />
                                Save Changes
                            </Button>
                            <Button size="sm" class="w-full" @click="saveAndSend" :disabled="form.processing">
                                <Send class="h-4 w-4 mr-2" />
                                Save & Send to Client
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Save,
    Send,
    Plus,
    Trash2,
    Calculator
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
    currency?: string;
}

interface Currency {
    value: string;
    label: string;
    symbol: string;
}

interface InvoiceItem {
    id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Props {
    clients: Client[];
    currencies: Currency[];
    defaultCurrency: string;
    invoice_number: string;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Invoices', href: '/invoices' },
    { title: 'Create Invoice', href: '/invoices/create' }
];

// Form state
const form = useForm({
    client_id: '',
    invoice_number: props.invoice_number,
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    terms: '',
    tax_rate: 0,
    discount: 0,
    currency: props.defaultCurrency,
    items: [
        {
            description: '',
            quantity: 1,
            unit_price: 0,
            total: 0
        }
    ] as InvoiceItem[]
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
    country: '',
    currency: props.defaultCurrency
});

const currencies = ref(props.currencies);

const clients = ref([...props.clients]);

// Computed properties
const hasClients = computed(() => clients.value.length > 0);

const selectedClient = computed(() => {
    return clients.value.find(client => client.id.toString() === form.client_id);
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

const canSaveInvoice = computed(() => {
    return hasClients.value && form.client_id && form.items.length > 0 && 
           form.items.some(item => item.description && item.quantity > 0 && item.unit_price >= 0);
});

// Set default due date (30 days from issue date)
watch(() => form.issue_date, (newDate) => {
    if (newDate && !form.due_date) {
        const issueDate = new Date(newDate);
        const dueDate = new Date(issueDate);
        dueDate.setDate(issueDate.getDate() + 30);
        form.due_date = dueDate.toISOString().split('T')[0];
    }
});

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
            clients.value.push(newClient);
            form.client_id = newClient.id.toString();
            showClientDialog.value = false;
            clientForm.reset();
        }
    });
};

const saveDraft = () => {
    if (!canSaveInvoice.value) {
        return;
    }
    
    form.post('/invoices', {
        onSuccess: (page) => {
            const invoice = page.props.invoice;
            router.visit(`/invoices/${invoice.id}`);
        }
    });
};

const saveAndSend = () => {
    if (!canSaveInvoice.value) {
        return;
    }
    
    form.post('/invoices?send=true', {
        onSuccess: (page) => {
            const invoice = page.props.invoice;
            router.visit(`/invoices/${invoice.id}`);
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
        <Head title="Create Invoice" />

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
                        <h1 class="text-3xl font-bold tracking-tight">Create Invoice</h1>
                        <p class="text-muted-foreground">Create a new invoice for your client</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="saveDraft" :disabled="form.processing || !canSaveInvoice">
                        <Save class="h-4 w-4 mr-2" />
                        Save Draft
                    </Button>
                    <Button @click="saveAndSend" :disabled="form.processing || !canSaveInvoice">
                        <Send class="h-4 w-4 mr-2" />
                        Save & Send
                    </Button>
                </div>
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
                                        class="mt-1"
                                    />
                                    <div v-if="form.errors.invoice_number" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.invoice_number }}
                                    </div>
                                </div>
                                <div>
                                    <Label for="currency">Currency</Label>
                                    <Select v-model="form.currency">
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
                                <Dialog v-model:open="showClientDialog">
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
                                            <div>
                                                <Label for="client_phone">Phone</Label>
                                                <Input
                                                    id="client_phone"
                                                    v-model="clientForm.phone"
                                                    placeholder="Phone number"
                                                    class="mt-1"
                                                />
                                            </div>
                                            <div>
                                                <Label for="client_address">Address</Label>
                                                <Textarea
                                                    id="client_address"
                                                    v-model="clientForm.address"
                                                    placeholder="Street address"
                                                    class="mt-1"
                                                />
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <Label for="client_city">City</Label>
                                                    <Input
                                                        id="client_city"
                                                        v-model="clientForm.city"
                                                        placeholder="City"
                                                        class="mt-1"
                                                    />
                                                </div>
                                                <div>
                                                    <Label for="client_state">State</Label>
                                                    <Input
                                                        id="client_state"
                                                        v-model="clientForm.state"
                                                        placeholder="State"
                                                        class="mt-1"
                                                    />
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <Label for="client_postal_code">Postal Code</Label>
                                                    <Input
                                                        id="client_postal_code"
                                                        v-model="clientForm.postal_code"
                                                        placeholder="12345"
                                                        class="mt-1"
                                                    />
                                                </div>
                                                <div>
                                                    <Label for="client_country">Country</Label>
                                                    <Input
                                                        id="client_country"
                                                        v-model="clientForm.country"
                                                        placeholder="Country"
                                                        class="mt-1"
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <Label for="client_currency">Default Currency</Label>
                                                <Select v-model="clientForm.currency">
                                                    <SelectTrigger class="mt-1">
                                                        <SelectValue placeholder="Select currency..." />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="currency in currencies" :key="currency.value" :value="currency.value">
                                                            {{ currency.label }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
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
                            <div v-if="hasClients">
                                <Label for="client_id">Select Client</Label>
                                <Select v-model="form.client_id">
                                    <SelectTrigger class="mt-1 w-full">
                                        <SelectValue placeholder="Choose a client..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="client in clients" :key="client.id" :value="client.id.toString()">
                                            {{ client.name }} - {{ client.email }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.client_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.client_id }}
                                </div>
                            </div>

                            <!-- No clients state -->
                            <div v-else class="text-center py-8 px-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <div class="text-gray-500 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No clients found</h3>
                                <p class="text-gray-600 mb-4">You need to create at least one client before you can create an invoice.</p>
                                <Button @click="showClientDialog = true" class="inline-flex items-center">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Create Your First Client
                                </Button>
                            </div>

                            <!-- Selected Client Preview -->
                            <div v-if="selectedClient" class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium mb-2">{{ selectedClient.name }}</h4>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <div>{{ selectedClient.email }}</div>
                                    <div v-if="selectedClient.phone">{{ selectedClient.phone }}</div>
                                    <div v-if="selectedClient.address">
                                        <div>{{ selectedClient.address }}</div>
                                        <div v-if="selectedClient.city || selectedClient.state || selectedClient.postal_code">
                                            {{ selectedClient.city }}{{ selectedClient.city && (selectedClient.state || selectedClient.postal_code) ? ', ' : '' }}{{ selectedClient.state }} {{ selectedClient.postal_code }}
                                        </div>
                                        <div v-if="selectedClient.country">{{ selectedClient.country }}</div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Invoice Items -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Invoice Items</CardTitle>
                                    <CardDescription>Add items or services to your invoice</CardDescription>
                                </div>
                                <Button variant="outline" size="sm" @click="addItem">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Add Item
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 gap-4 items-end">
                                    <div class="col-span-5">
                                        <Label v-if="index === 0" for="description">Description</Label>
                                        <Input
                                            v-model="item.description"
                                            placeholder="Description of item or service"
                                            :class="{ 'mt-1': index === 0 }"
                                        />
                                    </div>
                                    <div class="col-span-2">
                                        <Label v-if="index === 0" for="quantity">Quantity</Label>
                                        <Input
                                            v-model.number="item.quantity"
                                            type="number"
                                            min="1"
                                            step="1"
                                            :class="{ 'mt-1': index === 0 }"
                                        />
                                    </div>
                                    <div class="col-span-2">
                                        <Label v-if="index === 0" for="unit_price">Unit Price</Label>
                                        <Input
                                            v-model.number="item.unit_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :class="{ 'mt-1': index === 0 }"
                                        />
                                    </div>
                                    <div class="col-span-2">
                                        <Label v-if="index === 0">Total</Label>
                                        <div class="h-10 px-3 py-2 border border-input rounded-md bg-gray-50 flex items-center">
                                            {{ formatCurrency(item.total, form.currency) }}
                                        </div>
                                    </div>
                                    <div class="col-span-1">
                                        <Button
                                            v-if="form.items.length > 1"
                                            variant="outline"
                                            size="icon"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Notes and Terms -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                            <CardDescription>Add notes or terms to your invoice</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label for="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    placeholder="Any additional notes for the client..."
                                    class="mt-1"
                                />
                            </div>
                            <div>
                                <Label for="terms">Terms & Conditions</Label>
                                <Textarea
                                    id="terms"
                                    v-model="form.terms"
                                    placeholder="Payment terms and conditions..."
                                    class="mt-1"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Tax and Discount -->
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                <Calculator class="h-5 w-5 mr-2 inline" />
                                Tax & Discount
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label for="discount">Discount (%)</Label>
                                <Input
                                    id="discount"
                                    v-model.number="form.discount"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="mt-1"
                                />
                            </div>
                            <div>
                                <Label for="tax_rate">Tax Rate (%)</Label>
                                <Input
                                    id="tax_rate"
                                    v-model.number="form.tax_rate"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="mt-1"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Invoice Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span>{{ formatCurrency(subtotal, form.currency) }}</span>
                            </div>
                            <div v-if="form.discount > 0" class="flex justify-between text-sm">
                                <span>Discount ({{ form.discount }}%):</span>
                                <span>-{{ formatCurrency(discountAmount, form.currency) }}</span>
                            </div>
                            <div v-if="form.tax_rate > 0" class="flex justify-between text-sm">
                                <span>Tax ({{ form.tax_rate }}%):</span>
                                <span>{{ formatCurrency(taxAmount, form.currency) }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total:</span>
                                <span>{{ formatCurrency(total, form.currency) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" size="sm" class="w-full" @click="saveDraft" :disabled="form.processing || !canSaveInvoice">
                                <Save class="h-4 w-4 mr-2" />
                                Save as Draft
                            </Button>
                            <Button size="sm" class="w-full" @click="saveAndSend" :disabled="form.processing || !canSaveInvoice">
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

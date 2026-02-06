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
import InvoicePreview from '@/components/InvoicePreview.vue';
import DatePicker from '@/components/DatePicker.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldInput,
    NumberFieldIncrement,
} from '@/components/ui/number-field';
import { ModalLink } from '@inertiaui/modal-vue';
import { usePermissions } from '@/composables/usePermissions';

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

// Permission checks
const { canCreateInvoices, canSendInvoices } = usePermissions();

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
    return canCreateInvoices && hasClients.value && form.client_id && form.items.length > 0 &&
           form.items.some(item => item.description && item.quantity > 0 && item.unit_price >= 0);
});

const canSaveAndSend = computed(() => {
    return canSaveInvoice.value && canSendInvoices;
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
                    <Link
                      :as="Button" :href="route('invoices.index')"
                      variant="outline" size="icon">
                      <ArrowLeft class="h-4 w-4" />
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

                    <Button @click="saveAndSend" :disabled="form.processing || !canSaveAndSend">
                        <Send class="h-4 w-4 mr-2" />
                        Save & Send
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Main Content - Left Column -->
                <div class="space-y-6">
                    <!-- Invoice Details -->
                    <Card>
                        <template #header>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invoice Details</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Basic information about the invoice</p>
                            </div>
                        </template>

                        <div class="space-y-4">
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
                                        <SelectTrigger class="mt-1 w-full">
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
                                    <DatePicker
                                        v-model="form.issue_date"
                                        placeholder="Select issue date"
                                        class="mt-1"
                                    />
                                    <InputError :message="form.errors.issue_date" class="mt-1" />
                                </div>
                                <div>
                                    <Label for="due_date">Due Date</Label>
                                    <DatePicker
                                        v-model="form.due_date"
                                        placeholder="Select due date"
                                        :min="form.issue_date"
                                        class="mt-1"
                                    />
                                    <InputError :message="form.errors.due_date" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Client Information -->
                    <Card>
                        <template #header>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Client Information</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select or create a client for this invoice</p>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :as="ModalLink"
                                    :href="route('clients.create')">
                                    <Plus class="h-4 w-4 mr-2" />
                                    New Client
                                </Button>
                            </div>
                        </template>
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
                                <InputError :message="form.errors.client_id" class="mt-1" />
                            </div>

                            <!-- No clients state -->
                            <div v-else class="text-center py-8 px-4 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700">
                                <div class="text-gray-500 dark:text-gray-400 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No clients found</h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">You need to create at least one client before you can create an invoice.</p>
                                <ModalLink
                                    :href="route('clients.create')"
                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-gray-950 dark:focus-visible:ring-gray-300 bg-gray-900 text-gray-50 hover:bg-gray-900/90 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-50/90 h-10 px-4 py-2">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Create Your First Client
                                </ModalLink>
                            </div>

                            <!-- Selected Client Preview -->
                            <div v-if="selectedClient" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ selectedClient.name }}</h4>
                                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
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
                    </Card>

                    <!-- Invoice Items -->
                    <Card>
                        <template #header>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Invoice Items</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add items or services to your invoice</p>
                                </div>
                                <Button variant="outline" size="sm" @click="addItem">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Add Item
                                </Button>
                            </div>
                        </template>
                            <div class="space-y-6">
                                <div v-for="(item, index) in form.items" :key="index" class="space-y-3 pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0">
                                    <!-- Description (Full Width) -->
                                    <div>
                                        <Label v-if="index === 0" for="description">Description</Label>
                                        <Textarea
                                            v-model="item.description"
                                            placeholder="Description of item or service"
                                            :class="{ 'mt-1': index === 0 }"
                                        />
                                    </div>

                                    <!-- Quantity, Unit Price, Total, and Delete Button -->
                                    <div class="grid grid-cols-12 gap-4 items-end">
                                        <div class="col-span-3">
                                            <Label v-if="index === 0" for="quantity">Quantity</Label>
                                            <NumberField
                                                v-model="item.quantity"
                                                :min="1"
                                                :class="{ 'mt-1': index === 0 }"
                                                @update:model-value="calculateItemTotal(index)"
                                            >
                                                <NumberFieldContent>
                                                    <NumberFieldDecrement />
                                                    <NumberFieldInput />
                                                    <NumberFieldIncrement />
                                                </NumberFieldContent>
                                            </NumberField>
                                        </div>
                                        <div class="col-span-4">
                                            <Label v-if="index === 0" for="unit_price">Unit Price</Label>
                                            <NumberField
                                                v-model="item.unit_price"
                                                :min="0"
                                                :format-options="{ style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }"
                                                :class="{ 'mt-1': index === 0 }"
                                                @update:model-value="calculateItemTotal(index)"
                                            >
                                                <NumberFieldContent>
                                                    <NumberFieldDecrement />
                                                    <NumberFieldInput />
                                                    <NumberFieldIncrement />
                                                </NumberFieldContent>
                                            </NumberField>
                                        </div>
                                        <div class="col-span-4">
                                            <Label v-if="index === 0">Total</Label>
                                            <div class="h-10 px-3 py-2 border border-input rounded-md bg-gray-50 dark:bg-gray-800 flex items-center text-gray-900 dark:text-gray-100">
                                                {{ formatCurrency(item.total, form.currency) }}
                                            </div>
                                        </div>
                                        <div class="col-span-1">
                                            <Button
                                                v-if="form.items.length > 1"
                                                variant="outline"
                                                size="icon"
                                                @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-700">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </Card>

                    <!-- Notes and Terms -->
                    <Card>
                        <template #header>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Additional Information</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add notes or terms to your invoice</p>
                            </div>
                        </template>
                        <div class="space-y-4">
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
                        </div>
                    </Card>

                    <!-- Tax and Discount -->
                    <Card>
                        <template #header>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                    <Calculator class="h-5 w-5 mr-2" />
                                    Tax & Discount
                                </h3>
                            </div>
                        </template>
                        <div class="space-y-4">
                            <div>
                                <Label for="discount">Discount (%)</Label>
                                <NumberField
                                    v-model="form.discount"
                                    :min="0"
                                    :max="100"
                                    :format-options="{ style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }"
                                    class="mt-1"
                                >
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                            </div>
                            <div>
                                <Label for="tax_rate">Tax Rate (%)</Label>
                                <NumberField
                                    v-model="form.tax_rate"
                                    :min="0"
                                    :max="100"
                                    :format-options="{ style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }"
                                    class="mt-1"
                                >
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Right Column - Live Preview -->
                <div class="sticky top-6 h-fit">
                    <InvoicePreview
                        :invoice-number="form.invoice_number"
                        :client="selectedClient"
                        :issue-date="form.issue_date"
                        :due-date="form.due_date"
                        :currency="form.currency"
                        :currency-symbol="currencies.find(c => c.value === form.currency)?.symbol || '$'"
                        :items="form.items"
                        :tax-rate="form.tax_rate"
                        :discount="discountAmount"
                        :notes="form.notes"
                        :terms="form.terms"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

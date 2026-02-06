<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Plus,
    Edit2,
    Trash2,
    Power,
    PowerOff,
    Star,
    RefreshCw,
    Check,
    X,
    Search,
    Filter,
    MoreHorizontal,
    CheckCircle,
    AlertCircle,
    Globe,
    DollarSign
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Card from '@/components/custom/Card.vue';
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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

interface Currency {
    id: number;
    code: string;
    name: string;
    symbol: string;
    exchange_rate: number;
    is_base: boolean;
    is_active: boolean;
    last_updated_at: string;
    created_at: string;
    invoices_count?: number;
    clients_count?: number;
}

interface Props {
    currencies: Currency[];
}

const props = defineProps<Props>();

// Reactive state
const selectedCurrencies = ref<number[]>([]);
const searchQuery = ref('');
const showInactiveOnly = ref(false);
const showAddDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showSetBaseDialog = ref(false);
const currencyToEdit = ref<Currency | null>(null);
const currencyToDelete = ref<Currency | null>(null);
const currencyToSetBase = ref<Currency | null>(null);
const isLoading = ref(false);

// Forms
const addForm = useForm({
    code: '',
    name: '',
    symbol: '',
    exchange_rate: 1,
    is_active: true,
});

const editForm = useForm({
    code: '',
    name: '',
    symbol: '',
    exchange_rate: 1,
    is_active: true,
});

const breadcrumbs = [
    { title: 'Settings', href: '/settings' },
    { title: 'Currencies', href: '/settings/currencies' }
];

// Computed properties
const filteredCurrencies = computed(() => {
    let filtered = props.currencies;

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(currency => 
            currency.name.toLowerCase().includes(query) ||
            currency.code.toLowerCase().includes(query) ||
            currency.symbol.includes(query)
        );
    }

    // Status filter
    if (showInactiveOnly.value) {
        filtered = filtered.filter(currency => !currency.is_active);
    }

    return filtered;
});

const allSelected = computed({
    get: () => {
        return filteredCurrencies.value.length > 0 && 
               selectedCurrencies.value.length === filteredCurrencies.value.length;
    },
    set: (value: boolean) => {
        if (value) {
            selectedCurrencies.value = filteredCurrencies.value.map(currency => currency.id);
        } else {
            selectedCurrencies.value = [];
        }
    }
});

const baseCurrency = computed(() => {
    return props.currencies.find(currency => currency.is_base);
});

const activeCurrenciesCount = computed(() => {
    return props.currencies.filter(currency => currency.is_active).length;
});

// Helper functions
const formatExchangeRate = (rate: number): string => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 6
    }).format(rate);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isCurrencySelected = (currencyId: number): boolean => {
    return selectedCurrencies.value.includes(currencyId);
};

const toggleCurrencySelection = (currencyId: number): void => {
    const index = selectedCurrencies.value.indexOf(currencyId);
    if (index > -1) {
        selectedCurrencies.value.splice(index, 1);
    } else {
        selectedCurrencies.value.push(currencyId);
    }
};

// Actions
const openAddDialog = () => {
    addForm.reset();
    showAddDialog.value = true;
};

const openEditDialog = (currency: Currency) => {
    currencyToEdit.value = currency;
    editForm.reset();
    editForm.code = currency.code;
    editForm.name = currency.name;
    editForm.symbol = currency.symbol;
    editForm.exchange_rate = currency.exchange_rate;
    editForm.is_active = currency.is_active;
    showEditDialog.value = true;
};

const openDeleteDialog = (currency: Currency) => {
    currencyToDelete.value = currency;
    showDeleteDialog.value = true;
};

const openSetBaseDialog = (currency: Currency) => {
    currencyToSetBase.value = currency;
    showSetBaseDialog.value = true;
};

const submitAddForm = () => {
    addForm.post('/settings/currencies', {
        onSuccess: () => {
            showAddDialog.value = false;
            selectedCurrencies.value = [];
        }
    });
};

const submitEditForm = () => {
    if (!currencyToEdit.value) return;
    
    editForm.put(`/settings/currencies/${currencyToEdit.value.id}`, {
        onSuccess: () => {
            showEditDialog.value = false;
            currencyToEdit.value = null;
        }
    });
};

const toggleCurrencyStatus = (currency: Currency) => {
    router.patch(`/settings/currencies/${currency.id}/toggle-status`);
};

const setBaseCurrency = () => {
    if (!currencyToSetBase.value) return;
    
    router.patch(`/settings/currencies/${currencyToSetBase.value.id}/set-base`, {}, {
        onSuccess: () => {
            showSetBaseDialog.value = false;
            currencyToSetBase.value = null;
        }
    });
};

const deleteCurrency = () => {
    if (!currencyToDelete.value) return;
    
    router.delete(`/settings/currencies/${currencyToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            currencyToDelete.value = null;
            selectedCurrencies.value = [];
        }
    });
};

const updateExchangeRates = () => {
    isLoading.value = true;
    router.patch('/settings/currencies/update-rates', {}, {
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const performBulkAction = (action: 'activate' | 'deactivate') => {
    if (selectedCurrencies.value.length === 0) return;
    
    router.patch('/settings/currencies/bulk-toggle-status', {
        currency_ids: selectedCurrencies.value,
        action: action
    }, {
        onSuccess: () => {
            selectedCurrencies.value = [];
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Currency Settings" />

        <TooltipProvider>
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Currency Settings
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Manage currencies, exchange rates, and set your base currency
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button 
                            variant="outline" 
                            @click="updateExchangeRates"
                            :disabled="isLoading"
                        >
                            <RefreshCw :class="['h-4 w-4 mr-2', isLoading && 'animate-spin']" />
                            Update Rates
                        </Button>
                        <Button @click="openAddDialog">
                            <Plus class="h-4 w-4 mr-2" />
                            Add Currency
                        </Button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <Card>
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Total Currencies
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ currencies.length }}
                                    </p>
                                </div>
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                                    <Globe class="h-5 w-5 text-blue-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Active Currencies
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ activeCurrenciesCount }}
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
                                        Base Currency
                                    </p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ baseCurrency?.code || 'None' }}
                                    </p>
                                </div>
                                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-full">
                                    <DollarSign class="h-5 w-5 text-yellow-600" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content -->
                <Card>
                    <CardHeader>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <CardTitle>
                                    Currencies
                                    <span v-if="filteredCurrencies.length > 0" class="text-sm font-normal text-gray-500 ml-2">
                                        ({{ filteredCurrencies.length }} total)
                                    </span>
                                </CardTitle>
                                <CardDescription v-if="selectedCurrencies.length > 0">
                                    {{ selectedCurrencies.length }} currency(ies) selected
                                </CardDescription>
                            </div>

                            <div class="flex items-center gap-3">
                                <!-- Search -->
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input
                                        v-model="searchQuery"
                                        placeholder="Search currencies..."
                                        class="pl-9 w-64"
                                    />
                                </div>

                                <!-- Filter Toggle -->
                                <div class="flex items-center space-x-2">
                                    <Checkbox 
                                        id="inactive-only" 
                                        v-model:checked="showInactiveOnly"
                                    />
                                    <Label for="inactive-only" class="text-sm">
                                        Show inactive only
                                    </Label>
                                </div>

                                <!-- Bulk Actions -->
                                <DropdownMenu v-if="selectedCurrencies.length > 0">
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="outline">
                                            Bulk Actions
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="performBulkAction('activate')">
                                            <Power class="mr-2 h-4 w-4" />
                                            Activate Selected
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="performBulkAction('deactivate')">
                                            <PowerOff class="mr-2 h-4 w-4" />
                                            Deactivate Selected
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">
                                            <Checkbox
                                                :checked="allSelected"
                                                @update:checked="allSelected = $event"
                                            />
                                        </TableHead>
                                        <TableHead>Currency</TableHead>
                                        <TableHead>Symbol</TableHead>
                                        <TableHead>Exchange Rate</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Last Updated</TableHead>
                                        <TableHead class="w-20">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="currency in filteredCurrencies"
                                        :key="currency.id"
                                        :class="[
                                            'hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                            isCurrencySelected(currency.id) && 'bg-blue-50 dark:bg-blue-900/20'
                                        ]"
                                    >
                                        <TableCell>
                                            <Checkbox
                                                :checked="isCurrencySelected(currency.id)"
                                                @update:checked="toggleCurrencySelection(currency.id)"
                                            />
                                        </TableCell>

                                        <TableCell>
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0">
                                                    <Star v-if="currency.is_base" class="h-4 w-4 text-yellow-500 fill-current" />
                                                    <Globe v-else class="h-4 w-4 text-gray-400" />
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900 dark:text-white">
                                                        {{ currency.code }}
                                                        <Badge v-if="currency.is_base" variant="secondary" class="ml-2">
                                                            Base
                                                        </Badge>
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ currency.name }}
                                                    </p>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <span class="text-lg font-mono">
                                                {{ currency.symbol }}
                                            </span>
                                        </TableCell>

                                        <TableCell>
                                            <span class="font-mono">
                                                {{ currency.is_base ? '1.000000' : formatExchangeRate(currency.exchange_rate) }}
                                            </span>
                                        </TableCell>

                                        <TableCell>
                                            <Badge
                                                :variant="currency.is_active ? 'default' : 'secondary'"
                                                :class="currency.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'"
                                            >
                                                <CheckCircle v-if="currency.is_active" class="mr-1 h-3 w-3" />
                                                <AlertCircle v-else class="mr-1 h-3 w-3" />
                                                {{ currency.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </TableCell>

                                        <TableCell>
                                            <span class="text-sm text-gray-500">
                                                {{ formatDate(currency.last_updated_at || currency.created_at) }}
                                            </span>
                                        </TableCell>

                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem @click="openEditDialog(currency)">
                                                        <Edit2 class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem 
                                                        v-if="!currency.is_base"
                                                        @click="openSetBaseDialog(currency)"
                                                    >
                                                        <Star class="mr-2 h-4 w-4" />
                                                        Set as Base
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem @click="toggleCurrencyStatus(currency)">
                                                        <Power v-if="!currency.is_active" class="mr-2 h-4 w-4" />
                                                        <PowerOff v-else class="mr-2 h-4 w-4" />
                                                        {{ currency.is_active ? 'Deactivate' : 'Activate' }}
                                                    </DropdownMenuItem>

                                                    <DropdownMenuSeparator />

                                                    <DropdownMenuItem
                                                        v-if="!currency.is_base"
                                                        @click="openDeleteDialog(currency)"
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

                        <!-- Empty State -->
                        <div v-if="!filteredCurrencies.length" class="text-center py-12">
                            <Globe class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                No currencies found
                            </h3>
                            <p class="mt-2 text-gray-500">
                                {{ searchQuery || showInactiveOnly ? 'Try adjusting your search or filters' : 'Get started by adding your first currency' }}
                            </p>
                            <div class="mt-6">
                                <Button v-if="!searchQuery && !showInactiveOnly" @click="openAddDialog">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Currency
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Add Currency Dialog -->
                <Dialog v-model:open="showAddDialog">
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Add New Currency</DialogTitle>
                            <DialogDescription>
                                Add a new currency to your system.
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="submitAddForm" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="add-code">Currency Code</Label>
                                    <Input
                                        id="add-code"
                                        v-model="addForm.code"
                                        placeholder="USD"
                                        maxlength="3"
                                        class="uppercase"
                                        :class="{ 'border-red-300': addForm.errors.code }"
                                        required
                                    />
                                    <p v-if="addForm.errors.code" class="text-sm text-red-600 mt-1">
                                        {{ addForm.errors.code }}
                                    </p>
                                </div>

                                <div>
                                    <Label for="add-symbol">Symbol</Label>
                                    <Input
                                        id="add-symbol"
                                        v-model="addForm.symbol"
                                        placeholder="$"
                                        maxlength="10"
                                        :class="{ 'border-red-300': addForm.errors.symbol }"
                                        required
                                    />
                                    <p v-if="addForm.errors.symbol" class="text-sm text-red-600 mt-1">
                                        {{ addForm.errors.symbol }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <Label for="add-name">Currency Name</Label>
                                <Input
                                    id="add-name"
                                    v-model="addForm.name"
                                    placeholder="US Dollar"
                                    maxlength="100"
                                    :class="{ 'border-red-300': addForm.errors.name }"
                                    required
                                />
                                <p v-if="addForm.errors.name" class="text-sm text-red-600 mt-1">
                                    {{ addForm.errors.name }}
                                </p>
                            </div>

                            <div>
                                <Label for="add-rate">Exchange Rate (to {{ baseCurrency?.code }})</Label>
                                <Input
                                    id="add-rate"
                                    v-model.number="addForm.exchange_rate"
                                    type="number"
                                    step="0.000001"
                                    min="0.000001"
                                    max="999999.999999"
                                    placeholder="1.000000"
                                    :class="{ 'border-red-300': addForm.errors.exchange_rate }"
                                    required
                                />
                                <p v-if="addForm.errors.exchange_rate" class="text-sm text-red-600 mt-1">
                                    {{ addForm.errors.exchange_rate }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="add-active" 
                                    v-model:checked="addForm.is_active"
                                />
                                <Label for="add-active">
                                    Active
                                </Label>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <Button type="button" variant="outline" @click="showAddDialog = false">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="addForm.processing">
                                    Add Currency
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <!-- Edit Currency Dialog -->
                <Dialog v-model:open="showEditDialog">
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Edit Currency</DialogTitle>
                            <DialogDescription>
                                Update currency information.
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="submitEditForm" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="edit-code">Currency Code</Label>
                                    <Input
                                        id="edit-code"
                                        v-model="editForm.code"
                                        placeholder="USD"
                                        maxlength="3"
                                        class="uppercase"
                                        :class="{ 'border-red-300': editForm.errors.code }"
                                        :disabled="currencyToEdit?.is_base"
                                        required
                                    />
                                    <p v-if="editForm.errors.code" class="text-sm text-red-600 mt-1">
                                        {{ editForm.errors.code }}
                                    </p>
                                </div>

                                <div>
                                    <Label for="edit-symbol">Symbol</Label>
                                    <Input
                                        id="edit-symbol"
                                        v-model="editForm.symbol"
                                        placeholder="$"
                                        maxlength="10"
                                        :class="{ 'border-red-300': editForm.errors.symbol }"
                                        required
                                    />
                                    <p v-if="editForm.errors.symbol" class="text-sm text-red-600 mt-1">
                                        {{ editForm.errors.symbol }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <Label for="edit-name">Currency Name</Label>
                                <Input
                                    id="edit-name"
                                    v-model="editForm.name"
                                    placeholder="US Dollar"
                                    maxlength="100"
                                    :class="{ 'border-red-300': editForm.errors.name }"
                                    required
                                />
                                <p v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">
                                    {{ editForm.errors.name }}
                                </p>
                            </div>

                            <div>
                                <Label for="edit-rate">Exchange Rate (to {{ baseCurrency?.code }})</Label>
                                <Input
                                    id="edit-rate"
                                    v-model.number="editForm.exchange_rate"
                                    type="number"
                                    step="0.000001"
                                    min="0.000001"
                                    max="999999.999999"
                                    placeholder="1.000000"
                                    :class="{ 'border-red-300': editForm.errors.exchange_rate }"
                                    :disabled="currencyToEdit?.is_base"
                                    required
                                />
                                <p v-if="editForm.errors.exchange_rate" class="text-sm text-red-600 mt-1">
                                    {{ editForm.errors.exchange_rate }}
                                </p>
                                <p v-if="currencyToEdit?.is_base" class="text-sm text-gray-500 mt-1">
                                    Base currency exchange rate is always 1.000000
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox 
                                    id="edit-active" 
                                    v-model:checked="editForm.is_active"
                                    :disabled="currencyToEdit?.is_base"
                                />
                                <Label for="edit-active">
                                    Active
                                </Label>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <Button type="button" variant="outline" @click="showEditDialog = false">
                                    Cancel
                                </Button>
                                <Button type="submit" :disabled="editForm.processing">
                                    Update Currency
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <!-- Set Base Currency Dialog -->
                <AlertDialog v-model:open="showSetBaseDialog">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Set Base Currency</AlertDialogTitle>
                            <AlertDialogDescription>
                                Are you sure you want to set <strong>{{ currencyToSetBase?.name }} ({{ currencyToSetBase?.code }})</strong> as the base currency?
                                This will update all exchange rates relative to this currency.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click="setBaseCurrency">
                                Set as Base
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>

                <!-- Delete Currency Dialog -->
                <AlertDialog v-model:open="showDeleteDialog">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Delete Currency</AlertDialogTitle>
                            <AlertDialogDescription>
                                Are you sure you want to delete <strong>{{ currencyToDelete?.name }} ({{ currencyToDelete?.code }})</strong>?
                                This action cannot be undone.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction @click="deleteCurrency" class="bg-red-600 hover:bg-red-700">
                                Delete Currency
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </TooltipProvider>
    </AppLayout>
</template>

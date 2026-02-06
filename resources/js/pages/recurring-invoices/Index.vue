<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Search, Edit, Trash2, Eye, Play, Pause, RefreshCw } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/composables/usePermissions';

interface RecurringInvoice {
  id: number;
  invoice_number: string;
  client: {
    id: number;
    name: string;
  };
  total: number;
  frequency: string;
  is_active: boolean;
  next_invoice_date: string;
  last_invoice_date?: string;
  invoices_count: number;
  created_at: string;
}

interface Props {
  recurringInvoices: {
    data: RecurringInvoice[];
    links: any;
    meta: any;
  };
  search?: string;
}

const props = defineProps<Props>();

const { canCreateInvoices, canEditInvoices, canDeleteInvoices } = usePermissions();

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const formatFrequency = (frequency: string) => {
  const frequencies: Record<string, string> = {
    weekly: 'Weekly',
    monthly: 'Monthly',
    quarterly: 'Quarterly',
    yearly: 'Yearly'
  };
  return frequencies[frequency] || frequency;
};
</script>

<template>
  <Head title="Recurring Invoices" />

  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Recurring Invoices</h1>
          <p class="text-muted-foreground">Automate your recurring billing</p>
        </div>

        <Link v-if="canCreateInvoices" :href="route('recurring-invoices.create')">
          <Button>
            <Plus class="h-4 w-4 mr-2" />
            New Recurring Invoice
          </Button>
        </Link>
      </div>

      <!-- Filters -->
      <Card padding="md">
        <div class="flex items-center space-x-4">
          <div class="relative flex-1 max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input
              :value="search"
              placeholder="Search recurring invoices..."
              class="pl-9"
            />
          </div>
        </div>
      </Card>

      <!-- Recurring Invoices Table -->
      <Card>
        <template #header>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">All Recurring Invoices</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Manage your recurring billing schedules
          </p>
        </template>

        <div class="overflow-hidden rounded-md border">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-muted/50">
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Invoice
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Client
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Amount
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Frequency
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Next Invoice
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Generated
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Status
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="invoice in recurringInvoices.data" :key="invoice.id" class="border-b">
                  <td class="p-4">
                    <div class="font-medium">{{ invoice.invoice_number }}</div>
                    <div class="text-sm text-muted-foreground">
                      Created {{ new Date(invoice.created_at).toLocaleDateString() }}
                    </div>
                  </td>
                  <td class="p-4">
                    <div class="font-medium">{{ invoice.client.name }}</div>
                  </td>
                  <td class="p-4">
                    <div class="font-medium">{{ formatCurrency(invoice.total) }}</div>
                  </td>
                  <td class="p-4">
                    <Badge variant="outline">{{ formatFrequency(invoice.frequency) }}</Badge>
                  </td>
                  <td class="p-4">
                    <div class="text-sm">
                      {{ new Date(invoice.next_invoice_date).toLocaleDateString() }}
                    </div>
                  </td>
                  <td class="p-4">
                    <div class="text-sm font-medium">{{ invoice.invoices_count }}</div>
                    <div v-if="invoice.last_invoice_date" class="text-xs text-muted-foreground">
                      Last: {{ new Date(invoice.last_invoice_date).toLocaleDateString() }}
                    </div>
                  </td>
                  <td class="p-4">
                    <Badge :variant="invoice.is_active ? 'default' : 'secondary'">
                      {{ invoice.is_active ? 'Active' : 'Paused' }}
                    </Badge>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center space-x-2">
                      <Link :href="route('recurring-invoices.show', invoice.uuid)">
                        <Button variant="ghost" size="sm">
                          <Eye class="h-4 w-4" />
                        </Button>
                      </Link>
                      <Link v-if="canEditInvoices" :href="route('recurring-invoices.edit', invoice.uuid)">
                        <Button variant="ghost" size="sm">
                          <Edit class="h-4 w-4" />
                        </Button>
                      </Link>
                      <Button v-if="canEditInvoices" variant="ghost" size="sm">
                        <Play v-if="!invoice.is_active" class="h-4 w-4" />
                        <Pause v-else class="h-4 w-4" />
                      </Button>
                      <Button v-if="canCreateInvoices" variant="ghost" size="sm">
                        <RefreshCw class="h-4 w-4" />
                      </Button>
                      <Button v-if="canDeleteInvoices" variant="ghost" size="sm">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        <!-- Empty State -->
        <div v-if="!recurringInvoices.data.length" class="text-center py-12">
          <div class="text-muted-foreground">No recurring invoices found</div>
          <Link v-if="canCreateInvoices" :href="route('recurring-invoices.create')" class="mt-4 inline-block">
            <Button>
              <Plus class="h-4 w-4 mr-2" />
              Create your first recurring invoice
            </Button>
          </Link>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>
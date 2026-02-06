<template>
  <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <!-- Preview Header -->
    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Preview</h3>
        <div class="flex items-center gap-2">
          <Button variant="ghost" size="sm" @click="$emit('toggle-preview')">
            <Eye v-if="!hidePreview" class="h-4 w-4" />
            <EyeOff v-else class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>

    <!-- Invoice Preview Content -->
    <div class="p-8" style="min-height: 600px;">
      <!-- Company Header -->
      <div class="flex justify-between items-start mb-8">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
              <FileText class="h-6 w-6 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ organizationName }}</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ organizationEmail }}</p>
            </div>
          </div>
        </div>
        <div class="text-right">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Invoice</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ invoiceNumber || 'Draft' }}</p>
        </div>
      </div>

      <!-- Invoice Details -->
      <div class="grid grid-cols-2 gap-8 mb-8">
        <!-- Bill To -->
        <div>
          <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Bill To</h3>
          <div v-if="client" class="text-sm">
            <p class="font-medium text-gray-900 dark:text-gray-100">{{ client.name }}</p>
            <p class="text-gray-600 dark:text-gray-400">{{ client.email }}</p>
            <p v-if="client.address" class="text-gray-600 dark:text-gray-400">{{ client.address }}</p>
            <p v-if="client.city || client.state" class="text-gray-600 dark:text-gray-400">
              {{ [client.city, client.state, client.postal_code].filter(Boolean).join(', ') }}
            </p>
          </div>
          <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">No client selected</p>
        </div>

        <!-- Invoice Info -->
        <div class="text-right">
          <div class="space-y-2 text-sm">
            <div class="flex justify-end gap-4">
              <span class="text-gray-500 dark:text-gray-400">Issue Date:</span>
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatDate(issueDate) }}</span>
            </div>
            <div class="flex justify-end gap-4">
              <span class="text-gray-500 dark:text-gray-400">Due Date:</span>
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatDate(dueDate) }}</span>
            </div>
            <div v-if="currency" class="flex justify-end gap-4">
              <span class="text-gray-500 dark:text-gray-400">Currency:</span>
              <span class="font-medium text-gray-900 dark:text-gray-100">{{ currency }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="mb-8">
        <table class="w-full">
          <thead>
            <tr class="border-b-2 border-gray-300 dark:border-gray-600">
              <th class="text-left py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Description</th>
              <th class="text-right py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Qty</th>
              <th class="text-right py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Unit Price</th>
              <th class="text-right py-3 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, index) in items"
              :key="index"
              class="border-b border-gray-200 dark:border-gray-700"
            >
              <td class="py-3 text-sm text-gray-900 dark:text-gray-100">
                {{ item.description || 'Item description' }}
              </td>
              <td class="py-3 text-sm text-right text-gray-900 dark:text-gray-100">{{ item.quantity || 0 }}</td>
              <td class="py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                {{ currencySymbol }}{{ formatCurrency(item.unit_price) }}
              </td>
              <td class="py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                {{ currencySymbol }}{{ formatCurrency(item.total) }}
              </td>
            </tr>
            <tr v-if="items.length === 0">
              <td colspan="4" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500 italic">
                No items added
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="flex justify-end mb-8">
        <div class="w-64 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ currencySymbol }}{{ formatCurrency(subtotal) }}</span>
          </div>
          <div v-if="discount > 0" class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Discount:</span>
            <span class="font-medium text-gray-900 dark:text-gray-100">-{{ currencySymbol }}{{ formatCurrency(discount) }}</span>
          </div>
          <div v-if="taxRate > 0" class="flex justify-between text-sm">
            <span class="text-gray-600 dark:text-gray-400">Tax ({{ taxRate }}%):</span>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ currencySymbol }}{{ formatCurrency(taxAmount) }}</span>
          </div>
          <div class="border-t-2 border-gray-300 dark:border-gray-600 pt-2 flex justify-between">
            <span class="font-semibold text-gray-900 dark:text-gray-100">Total:</span>
            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ currencySymbol }}{{ formatCurrency(total) }}</span>
          </div>
        </div>
      </div>

      <!-- Notes & Terms -->
      <div v-if="notes || terms" class="space-y-4">
        <div v-if="notes">
          <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Notes</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ notes }}</p>
        </div>
        <div v-if="terms">
          <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Terms & Conditions</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ terms }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { FileText, Eye, EyeOff } from 'lucide-vue-next'

interface InvoiceItem {
  description: string
  quantity: number | string
  unit_price: number | string
  total: number | string
}

interface Client {
  name: string
  email: string
  address?: string
  city?: string
  state?: string
  postal_code?: string
}

interface Props {
  invoiceNumber?: string
  client?: Client | null
  issueDate?: string
  dueDate?: string
  currency?: string
  currencySymbol?: string
  items?: InvoiceItem[]
  taxRate?: number
  discount?: number
  notes?: string
  terms?: string
  organizationName?: string
  organizationEmail?: string
  hidePreview?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  invoiceNumber: '',
  client: null,
  issueDate: '',
  dueDate: '',
  currency: 'USD',
  currencySymbol: '$',
  items: () => [],
  taxRate: 0,
  discount: 0,
  notes: '',
  terms: '',
  organizationName: 'InvoiceHub',
  organizationEmail: 'contact@invoicehub.com',
  hidePreview: false,
})

defineEmits(['toggle-preview'])

const subtotal = computed(() => {
  return props.items.reduce((sum, item) => {
    const itemTotal = typeof item.total === 'string' ? parseFloat(item.total) : (item.total || 0)
    return sum + itemTotal
  }, 0)
})

const taxAmount = computed(() => {
  return (subtotal.value - props.discount) * (props.taxRate / 100)
})

const total = computed(() => {
  return subtotal.value - props.discount + taxAmount.value
})

const formatCurrency = (value: number | string | undefined | null) => {
  const numValue = typeof value === 'string' ? parseFloat(value) : (value || 0)
  return numValue.toFixed(2)
}

const formatDate = (date: string) => {
  if (!date) return '---'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

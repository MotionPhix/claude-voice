<template>
  <div class="relative" ref="searchContainer">
    <div class="relative">
      <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
      <Input
        v-model="query"
        type="search"
        placeholder="Search invoices, clients, payments..."
        class="w-64 pl-9"
        @focus="showResults = true"
        @input="handleSearch"
        @keydown.down.prevent="navigateResults('down')"
        @keydown.up.prevent="navigateResults('up')"
        @keydown.enter.prevent="selectResult"
        @keydown.esc="closeResults"
      />
    </div>

    <!-- Search Results Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showResults && (hasResults || isLoading)"
        class="absolute top-full mt-2 w-96 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 z-50"
      >
        <!-- Loading State -->
        <div v-if="isLoading" class="p-4 text-center">
          <Loader2 class="h-5 w-5 animate-spin mx-auto text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Searching...</p>
        </div>

        <!-- Results -->
        <div v-else-if="hasResults" class="max-h-96 overflow-y-auto">
          <!-- Invoices -->
          <div v-if="results.invoices.length > 0" class="border-b border-gray-200 dark:border-gray-700 p-2">
            <div class="px-2 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
              Invoices
            </div>
            <Link
              v-for="(invoice, index) in results.invoices"
              :key="`invoice-${invoice.id}`"
              :href="route('invoices.show', invoice.uuid)"
              :class="[
                'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                selectedIndex === getResultIndex('invoice', index)
                  ? 'bg-gray-100 dark:bg-gray-700'
                  : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
              ]"
              @click="closeResults"
            >
              <FileText class="h-4 w-4 text-gray-400 flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-900 dark:text-gray-100">{{ invoice.number }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ invoice.client_name }}</div>
              </div>
              <Badge :variant="getInvoiceStatusVariant(invoice.status)">
                {{ invoice.status }}
              </Badge>
            </Link>
          </div>

          <!-- Clients -->
          <div v-if="results.clients.length > 0" class="border-b border-gray-200 dark:border-gray-700 p-2">
            <div class="px-2 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
              Clients
            </div>
            <Link
              v-for="(client, index) in results.clients"
              :key="`client-${client.id}`"
              :href="route('clients.show', client.uuid)"
              :class="[
                'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                selectedIndex === getResultIndex('client', index)
                  ? 'bg-gray-100 dark:bg-gray-700'
                  : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
              ]"
              @click="closeResults"
            >
              <Users class="h-4 w-4 text-gray-400 flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ client.email }}</div>
              </div>
            </Link>
          </div>

          <!-- Payments -->
          <div v-if="results.payments.length > 0" class="p-2">
            <div class="px-2 py-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
              Payments
            </div>
            <Link
              v-for="(payment, index) in results.payments"
              :key="`payment-${payment.id}`"
              :href="route('invoices.show', payment.invoice_id)"
              :class="[
                'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                selectedIndex === getResultIndex('payment', index)
                  ? 'bg-gray-100 dark:bg-gray-700'
                  : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
              ]"
              @click="closeResults"
            >
              <Wallet class="h-4 w-4 text-gray-400 flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-900 dark:text-gray-100">${{ payment.amount }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ payment.invoice_number }}</div>
              </div>
            </Link>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="p-8 text-center">
          <Search class="h-8 w-8 mx-auto text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No results found</p>
        </div>

        <!-- Footer -->
        <div v-if="hasResults" class="border-t border-gray-200 dark:border-gray-700 px-4 py-2">
          <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>{{ totalResults }} result{{ totalResults !== 1 ? 's' : '' }}</span>
            <div class="flex items-center gap-2">
              <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 rounded">↑</kbd>
              <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 dark:bg-gray-700 rounded">↓</kbd>
              <span>to navigate</span>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Search, FileText, Users, Wallet, Loader2 } from 'lucide-vue-next'
import { debounce } from 'lodash'
import axios from 'axios'

interface SearchResults {
  invoices: Array<{
    id: number
    number: string
    client_name: string
    status: string
  }>
  clients: Array<{
    id: number
    name: string
    email: string
  }>
  payments: Array<{
    id: number
    amount: string
    invoice_id: number
    invoice_number: string
  }>
}

const query = ref('')
const showResults = ref(false)
const isLoading = ref(false)
const selectedIndex = ref(0)
const searchContainer = ref<HTMLElement | null>(null)

const results = ref<SearchResults>({
  invoices: [],
  clients: [],
  payments: [],
})

const hasResults = computed(() => {
  return results.value.invoices.length > 0 ||
         results.value.clients.length > 0 ||
         results.value.payments.length > 0
})

const totalResults = computed(() => {
  return results.value.invoices.length +
         results.value.clients.length +
         results.value.payments.length
})

const getResultIndex = (type: string, index: number) => {
  let offset = 0
  if (type === 'client') {
    offset = results.value.invoices.length
  } else if (type === 'payment') {
    offset = results.value.invoices.length + results.value.clients.length
  }
  return offset + index
}

const handleSearch = debounce(async () => {
  if (query.value.trim().length < 2) {
    results.value = { invoices: [], clients: [], payments: [] }
    return
  }

  isLoading.value = true
  try {
    const response = await axios.get('/api/search', {
      params: { q: query.value }
    })
    // The API returns the data already grouped
    results.value = {
      invoices: response.data.invoices || [],
      clients: response.data.clients || [],
      payments: response.data.payments || [],
    }
    selectedIndex.value = 0
  } catch (error) {
    console.error('Search error:', error)
    results.value = { invoices: [], clients: [], payments: [] }
  } finally {
    isLoading.value = false
  }
}, 300)

const navigateResults = (direction: 'up' | 'down') => {
  const max = totalResults.value - 1
  if (direction === 'down') {
    selectedIndex.value = Math.min(selectedIndex.value + 1, max)
  } else {
    selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
  }
}

const selectResult = () => {
  let currentIndex = 0

  for (const invoice of results.value.invoices) {
    if (currentIndex === selectedIndex.value) {
      router.visit(route('invoices.show', invoice.uuid))
      closeResults()
      return
    }
    currentIndex++
  }

  for (const client of results.value.clients) {
    if (currentIndex === selectedIndex.value) {
      router.visit(route('clients.show', client.uuid))
      closeResults()
      return
    }
    currentIndex++
  }

  for (const payment of results.value.payments) {
    if (currentIndex === selectedIndex.value) {
      router.visit(route('invoices.show', payment.invoice_id))
      closeResults()
      return
    }
    currentIndex++
  }
}

const closeResults = () => {
  showResults.value = false
}

const getInvoiceStatusVariant = (status: string) => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    paid: 'default',
    sent: 'secondary',
    draft: 'secondary',
    overdue: 'destructive',
  }
  return variants[status] || 'secondary'
}

// Click outside to close
const handleClickOutside = (event: MouseEvent) => {
  if (searchContainer.value && !searchContainer.value.contains(event.target as Node)) {
    closeResults()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

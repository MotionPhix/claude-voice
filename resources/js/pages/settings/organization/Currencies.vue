<template>
  <Head title="Currency Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Currencies</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Manage currencies and exchange rates for invoicing
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Button variant="outline" @click="updateExchangeRates" :disabled="updatingRates">
          <RefreshCw :class="['mr-2 h-4 w-4', updatingRates && 'animate-spin']" />
          Update Rates
        </Button>
        <Button @click="showAddModal = true">
          <Plus class="mr-2 h-4 w-4" />
          Add Currency
        </Button>
      </div>
    </div>

    <!-- Active Currencies Section -->
    <SettingsSection
      title="Active Currencies"
      description="Currencies available for invoicing"
    >
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Currency</TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Symbol</TableHead>
              <TableHead>Exchange Rate</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="currency in activeCurrencies" :key="currency.id">
              <TableCell class="font-medium">
                {{ currency.name }}
                <Star v-if="currency.is_base" class="inline ml-1 h-3 w-3 text-yellow-500" />
              </TableCell>
              <TableCell>{{ currency.code }}</TableCell>
              <TableCell>{{ currency.symbol }}</TableCell>
              <TableCell>{{ formatExchangeRate(currency.exchange_rate) }}</TableCell>
              <TableCell>
                <Badge variant="default">Active</Badge>
              </TableCell>
              <TableCell class="text-right">
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="sm">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="editCurrency(currency)">
                      <Edit2 class="mr-2 h-4 w-4" />
                      Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem v-if="!currency.is_base" @click="setAsBase(currency.id)">
                      <Star class="mr-2 h-4 w-4" />
                      Set as Base
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="toggleStatus(currency.id, false)">
                      <PowerOff class="mr-2 h-4 w-4" />
                      Deactivate
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="!currency.is_base" />
                    <DropdownMenuItem v-if="!currency.is_base" @click="deleteCurrency(currency.id)" class="text-red-600">
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
    </SettingsSection>

    <!-- Inactive Currencies Section -->
    <SettingsSection
      v-if="inactiveCurrencies.length > 0"
      title="Inactive Currencies"
      description="Currencies that are currently disabled"
    >
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Currency</TableHead>
              <TableHead>Code</TableHead>
              <TableHead>Symbol</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="currency in inactiveCurrencies" :key="currency.id">
              <TableCell class="font-medium">{{ currency.name }}</TableCell>
              <TableCell>{{ currency.code }}</TableCell>
              <TableCell>{{ currency.symbol }}</TableCell>
              <TableCell>
                <Badge variant="secondary">Inactive</Badge>
              </TableCell>
              <TableCell class="text-right">
                <Button variant="outline" size="sm" @click="toggleStatus(currency.id, true)">
                  <Power class="mr-2 h-4 w-4" />
                  Activate
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </SettingsSection>

    <!-- Add/Edit Currency Modal -->
    <Dialog v-model:open="showAddModal">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            :title="editingCurrency ? 'Edit Currency' : 'Add Currency'"
            :description="editingCurrency ? 'Update currency details' : 'Add a new currency to your organization'"
            :icon="DollarSign"
            :show-close-button="true"
            :on-close="closeModal"
          />

          <ModalScrollable>
            <form @submit.prevent="saveCurrency" class="space-y-4">
              <div>
                <Label for="code">Currency Code</Label>
                <Input
                  id="code"
                  v-model="currencyForm.code"
                  type="text"
                  maxlength="3"
                  placeholder="USD"
                  required
                  :disabled="!!editingCurrency"
                  class="mt-1 uppercase"
                />
                <InputError :message="currencyForm.errors.code" class="mt-2" />
              </div>

              <div>
                <Label for="name">Currency Name</Label>
                <Input
                  id="name"
                  v-model="currencyForm.name"
                  type="text"
                  placeholder="US Dollar"
                  required
                  class="mt-1"
                />
                <InputError :message="currencyForm.errors.name" class="mt-2" />
              </div>

              <div>
                <Label for="symbol">Symbol</Label>
                <Input
                  id="symbol"
                  v-model="currencyForm.symbol"
                  type="text"
                  placeholder="$"
                  required
                  class="mt-1"
                />
                <InputError :message="currencyForm.errors.symbol" class="mt-2" />
              </div>

              <div>
                <Label for="exchange_rate">Exchange Rate (to base currency)</Label>
                <Input
                  id="exchange_rate"
                  v-model.number="currencyForm.exchange_rate"
                  type="number"
                  step="0.000001"
                  placeholder="1.000000"
                  required
                  class="mt-1"
                />
                <InputError :message="currencyForm.errors.exchange_rate" class="mt-2" />
              </div>
            </form>
          </ModalScrollable>

          <ModalFooter>
            <Button variant="outline" @click="closeModal">
              Cancel
            </Button>
            <Button @click="saveCurrency" :disabled="currencyForm.processing">
              <Loader2 v-if="currencyForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              {{ editingCurrency ? 'Update' : 'Add' }} Currency
            </Button>
          </ModalFooter>
        </ModalRoot>
      </DialogContent>
    </Dialog>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import InputError from '@/components/InputError.vue'
import ModalRoot from '@/components/custom/ModalRoot.vue'
import ModalHeader from '@/components/custom/ModalHeader.vue'
import ModalScrollable from '@/components/custom/ModalScrollable.vue'
import ModalFooter from '@/components/custom/ModalFooter.vue'
import { Plus, Edit2, Trash2, Power, PowerOff, Star, RefreshCw, MoreHorizontal, DollarSign, Loader2 } from 'lucide-vue-next'

interface Currency {
  id: number
  code: string
  name: string
  symbol: string
  exchange_rate: number
  is_base: boolean
  is_active: boolean
}

interface Props {
  currencies: Currency[]
}

const props = defineProps<Props>()

const showAddModal = ref(false)
const editingCurrency = ref<Currency | null>(null)
const updatingRates = ref(false)

const activeCurrencies = computed(() => props.currencies.filter(c => c.is_active))
const inactiveCurrencies = computed(() => props.currencies.filter(c => !c.is_active))

const currencyForm = useForm({
  code: '',
  name: '',
  symbol: '',
  exchange_rate: 1.000000,
})

const editCurrency = (currency: Currency) => {
  editingCurrency.value = currency
  currencyForm.code = currency.code
  currencyForm.name = currency.name
  currencyForm.symbol = currency.symbol
  currencyForm.exchange_rate = currency.exchange_rate
  showAddModal.value = true
}

const closeModal = () => {
  showAddModal.value = false
  editingCurrency.value = null
  currencyForm.reset()
}

const saveCurrency = () => {
  if (editingCurrency.value) {
    currencyForm.put(route('settings.organization.currencies.update', editingCurrency.value.uuid), {
      preserveScroll: true,
      onSuccess: closeModal,
    })
  } else {
    currencyForm.post(route('settings.organization.currencies.store'), {
      preserveScroll: true,
      onSuccess: closeModal,
    })
  }
}

const toggleStatus = (currencyId: number, isActive: boolean) => {
  router.patch(route('settings.organization.currencies.toggle-status', currencyId), {
    is_active: isActive,
  }, {
    preserveScroll: true,
  })
}

const setAsBase = (currencyId: number) => {
  router.patch(route('settings.organization.currencies.set-base', currencyId), {}, {
    preserveScroll: true,
  })
}

const deleteCurrency = (currencyId: number) => {
  if (confirm('Are you sure you want to delete this currency?')) {
    router.delete(route('settings.organization.currencies.destroy', currencyId), {
      preserveScroll: true,
    })
  }
}

const updateExchangeRates = () => {
  updatingRates.value = true
  router.patch(route('settings.organization.currencies.update-rates'), {}, {
    preserveScroll: true,
    onFinish: () => {
      updatingRates.value = false
    },
  })
}

const formatExchangeRate = (rate: number) => {
  return rate.toFixed(6)
}
</script>

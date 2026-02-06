<template>
  <Head title="Billing History" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Billing History</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        View and download your subscription invoices
      </p>
    </div>

    <!-- Billing Invoices Section -->
    <SettingsSection
      title="Invoice History"
      description="All your subscription billing invoices"
    >
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Invoice</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in billingInvoices" :key="invoice.id">
              <TableCell class="font-medium">{{ invoice.number }}</TableCell>
              <TableCell>{{ formatDate(invoice.date) }}</TableCell>
              <TableCell>${{ invoice.amount.toFixed(2) }}</TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(invoice.status)">
                  {{ invoice.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex items-center justify-end gap-2">
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="viewInvoice(invoice.id)"
                  >
                    <Eye class="h-4 w-4" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="downloadInvoice(invoice.id)"
                  >
                    <Download class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <div v-if="billingInvoices.length === 0" class="text-center py-12">
          <Receipt class="mx-auto h-12 w-12 text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No invoices yet</p>
        </div>
      </div>
    </SettingsSection>

    <!-- Payment Summary Section -->
    <SettingsSection
      title="Payment Summary"
      description="Overview of your billing"
    >
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-2">
            <DollarSign class="h-4 w-4" />
            <span class="text-sm">Total Paid</span>
          </div>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            ${{ summary.totalPaid.toFixed(2) }}
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-2">
            <Calendar class="h-4 w-4" />
            <span class="text-sm">Next Payment</span>
          </div>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            {{ formatDate(summary.nextPaymentDate) }}
          </p>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-2">
            <TrendingUp class="h-4 w-4" />
            <span class="text-sm">Next Amount</span>
          </div>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            ${{ summary.nextPaymentAmount.toFixed(2) }}
          </p>
        </div>
      </div>
    </SettingsSection>

    <!-- Invoice Preferences Section -->
    <SettingsSection
      title="Invoice Preferences"
      description="Customize how you receive billing invoices"
    >
      <form @submit.prevent="savePreferences" class="space-y-4">
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <CheckboxInput
              id="email_invoices"
              name="email_invoices"
              v-model="preferencesForm.email_invoices"
            />
          </div>
          <div class="ml-3 text-sm">
            <Label for="email_invoices" class="font-medium text-gray-900 dark:text-gray-100">
              Email Invoices
            </Label>
            <p class="text-gray-500 dark:text-gray-400">Receive billing invoices via email</p>
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex items-center h-5">
            <CheckboxInput
              id="payment_reminders"
              name="payment_reminders"
              v-model="preferencesForm.payment_reminders"
            />
          </div>
          <div class="ml-3 text-sm">
            <Label for="payment_reminders" class="font-medium text-gray-900 dark:text-gray-100">
              Payment Reminders
            </Label>
            <p class="text-gray-500 dark:text-gray-400">Get reminded before payment is due</p>
          </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
          <Button
            type="submit"
            :disabled="preferencesForm.processing"
          >
            <Loader2 v-if="preferencesForm.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save Preferences
          </Button>
        </div>
      </form>
    </SettingsSection>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import CheckboxInput from '@/components/CheckboxInput.vue'
import { Label } from '@/components/ui/label'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Receipt, Eye, Download, DollarSign, Calendar, TrendingUp, Loader2 } from 'lucide-vue-next'

interface BillingInvoice {
  id: string
  number: string
  date: string
  amount: number
  status: 'paid' | 'pending' | 'failed'
}

interface Props {
  billingInvoices: BillingInvoice[]
  summary: {
    totalPaid: number
    nextPaymentDate: string
    nextPaymentAmount: number
  }
  preferences: {
    email_invoices: boolean
    payment_reminders: boolean
  }
}

const props = defineProps<Props>()

const preferencesForm = useForm({
  email_invoices: props.preferences?.email_invoices ?? true,
  payment_reminders: props.preferences?.payment_reminders ?? true,
})

const savePreferences = () => {
  preferencesForm.put(route('settings.billing.invoices.preferences'), {
    preserveScroll: true,
  })
}

const viewInvoice = (invoiceId: string) => {
  router.visit(route('settings.billing.invoices.show', invoiceId))
}

const downloadInvoice = (invoiceId: string) => {
  window.open(route('settings.billing.invoices.download', invoiceId), '_blank')
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const getStatusVariant = (status: string) => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    paid: 'default',
    pending: 'secondary',
    failed: 'destructive',
  }
  return variants[status] || 'secondary'
}
</script>

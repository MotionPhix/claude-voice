<template>
  <Head title="Payment Methods" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment Methods</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Manage your payment methods for subscription billing
        </p>
      </div>
      <Button @click="showAddModal = true">
        <Plus class="mr-2 h-4 w-4" />
        Add Payment Method
      </Button>
    </div>

    <!-- Payment Methods Section -->
    <SettingsSection
      title="Saved Payment Methods"
      description="Your stored payment methods for billing"
    >
      <div class="space-y-4">
        <div
          v-for="method in paymentMethods"
          :key="method.id"
          class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-800">
              <CreditCard class="h-6 w-6 text-gray-600 dark:text-gray-400" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ method.brand }} •••• {{ method.last4 }}
                </p>
                <Badge v-if="method.is_default" variant="default" class="text-xs">Default</Badge>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Expires {{ method.exp_month }}/{{ method.exp_year }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button
              v-if="!method.is_default"
              variant="outline"
              size="sm"
              @click="setDefault(method.id)"
            >
              Set as Default
            </Button>
            <Button
              variant="ghost"
              size="sm"
              @click="deleteMethod(method.id)"
            >
              <Trash2 class="h-4 w-4 text-red-600 dark:text-red-400" />
            </Button>
          </div>
        </div>

        <div v-if="paymentMethods.length === 0" class="text-center py-12 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
          <Wallet class="mx-auto h-12 w-12 text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No payment methods added yet</p>
        </div>
      </div>
    </SettingsSection>

    <!-- PayChangu Integration Section -->
    <SettingsSection
      title="PayChangu Integration"
      description="Pay your subscription using PayChangu"
    >
      <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4">
          <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900">
            <Smartphone class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">PayChangu</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Mobile Money & Bank Transfer
            </p>
          </div>
        </div>
        <Badge :variant="paychanguEnabled ? 'default' : 'secondary'">
          {{ paychanguEnabled ? 'Enabled' : 'Available' }}
        </Badge>
      </div>
    </SettingsSection>

    <!-- Billing Information Section -->
    <SettingsSection
      title="Billing Information"
      description="Update your billing address and details"
    >
      <form @submit.prevent="updateBillingInfo" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <Label for="billing_name">Name on Invoice</Label>
            <Input
              id="billing_name"
              v-model="billingForm.name"
              type="text"
              class="mt-1"
            />
          </div>

          <div>
            <Label for="billing_email">Billing Email</Label>
            <Input
              id="billing_email"
              v-model="billingForm.email"
              type="email"
              class="mt-1"
            />
          </div>

          <div>
            <Label for="tax_id">Tax ID / VAT Number</Label>
            <Input
              id="tax_id"
              v-model="billingForm.tax_id"
              type="text"
              placeholder="Optional"
              class="mt-1"
            />
          </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 gap-3">
          <Button
            type="button"
            variant="outline"
            @click="billingForm.reset()"
            :disabled="billingForm.processing"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="billingForm.processing"
          >
            <Loader2 v-if="billingForm.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save Changes
          </Button>
        </div>
      </form>
    </SettingsSection>

    <!-- Add Payment Method Modal -->
    <Dialog v-model:open="showAddModal">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="Add Payment Method"
            description="Add a new card for billing"
            :icon="CreditCard"
            :show-close-button="true"
            :on-close="() => showAddModal = false"
          />

          <ModalScrollable>
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
              <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                Payment method integration will be available soon. You'll be able to add credit/debit cards securely.
              </p>
            </div>
          </ModalScrollable>

          <ModalFooter>
            <Button variant="outline" @click="showAddModal = false">
              Close
            </Button>
          </ModalFooter>
        </ModalRoot>
      </DialogContent>
    </Dialog>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import ModalRoot from '@/components/custom/ModalRoot.vue'
import ModalHeader from '@/components/custom/ModalHeader.vue'
import ModalScrollable from '@/components/custom/ModalScrollable.vue'
import ModalFooter from '@/components/custom/ModalFooter.vue'
import { Plus, CreditCard, Trash2, Wallet, Smartphone, Loader2 } from 'lucide-vue-next'

interface PaymentMethod {
  id: string
  brand: string
  last4: string
  exp_month: string
  exp_year: string
  is_default: boolean
}

interface Props {
  paymentMethods: PaymentMethod[]
  paychanguEnabled: boolean
  billingInfo: {
    name?: string
    email?: string
    tax_id?: string
  }
}

const props = defineProps<Props>()

const showAddModal = ref(false)

const billingForm = useForm({
  name: props.billingInfo?.name || '',
  email: props.billingInfo?.email || '',
  tax_id: props.billingInfo?.tax_id || '',
})

const updateBillingInfo = () => {
  billingForm.put(route('settings.billing.payment-methods.update-info'), {
    preserveScroll: true,
  })
}

const setDefault = (methodId: string) => {
  router.patch(route('settings.billing.payment-methods.set-default', methodId), {}, {
    preserveScroll: true,
  })
}

const deleteMethod = (methodId: string) => {
  if (confirm('Are you sure you want to remove this payment method?')) {
    router.delete(route('settings.billing.payment-methods.destroy', methodId), {
      preserveScroll: true,
    })
  }
}
</script>

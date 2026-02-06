<template>
  <Head title="Integrations" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Integrations</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Connect third-party services and manage API access
      </p>
    </div>

    <!-- PayChangu Integration Section -->
    <SettingsSection
      title="PayChangu Payment Gateway"
      description="Accept payments from customers using PayChangu"
    >
      <div class="space-y-4">
        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900">
              <CreditCard class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">PayChangu</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ paychanguConnected ? 'Connected' : 'Not connected' }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Badge :variant="paychanguConnected ? 'default' : 'secondary'">
              {{ paychanguConnected ? 'Active' : 'Inactive' }}
            </Badge>
            <Button
              variant="outline"
              size="sm"
              @click="showPayChanguModal = true"
            >
              {{ paychanguConnected ? 'Configure' : 'Connect' }}
            </Button>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- API Access Section -->
    <SettingsSection
      title="API Access"
      description="Generate and manage API keys for integrations"
    >
      <div class="space-y-4">
        <div
          v-for="key in apiKeys"
          :key="key.id"
          class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700"
        >
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ key.name }}</h3>
              <Badge variant="outline" class="text-xs">{{ key.prefix }}...</Badge>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Created {{ formatDate(key.created_at) }} · Last used {{ key.last_used_at ? formatDate(key.last_used_at) : 'Never' }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="ghost"
              size="sm"
              @click="revokeApiKey(key.id)"
            >
              <Trash2 class="h-4 w-4 text-red-600 dark:text-red-400" />
            </Button>
          </div>
        </div>

        <div v-if="apiKeys.length === 0" class="text-center py-8 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
          <Key class="mx-auto h-12 w-12 text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No API keys yet</p>
        </div>

        <Button @click="showApiKeyModal = true" variant="outline" class="w-full sm:w-auto">
          <Plus class="mr-2 h-4 w-4" />
          Create API Key
        </Button>
      </div>
    </SettingsSection>

    <!-- Webhooks Section -->
    <SettingsSection
      title="Webhooks"
      description="Configure webhooks to receive real-time notifications"
    >
      <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-start gap-4">
          <Webhook class="h-5 w-5 text-gray-400 mt-0.5" />
          <div class="flex-1">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
              Webhook Configuration
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Webhook management will be available soon. You'll be able to subscribe to events like invoice.sent, payment.received, and more.
            </p>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- PayChangu Modal -->
    <Dialog v-model:open="showPayChanguModal">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="PayChangu Configuration"
            description="Configure your PayChangu payment gateway"
            :icon="CreditCard"
            :show-close-button="true"
            :on-close="() => showPayChanguModal = false"
          />

          <ModalScrollable>
            <form @submit.prevent="savePayChangu" class="space-y-4">
              <div>
                <Label for="public_key">Public Key</Label>
                <Input
                  id="public_key"
                  v-model="paychanguForm.public_key"
                  type="text"
                  placeholder="pub-xxx..."
                  class="mt-1"
                />
                <InputError :message="paychanguForm.errors.public_key" class="mt-2" />
              </div>

              <div>
                <Label for="secret_key">Secret Key</Label>
                <Input
                  id="secret_key"
                  v-model="paychanguForm.secret_key"
                  type="password"
                  placeholder="sec-xxx..."
                  class="mt-1"
                />
                <InputError :message="paychanguForm.errors.secret_key" class="mt-2" />
              </div>

              <div>
                <Label for="webhook_secret">Webhook Secret</Label>
                <Input
                  id="webhook_secret"
                  v-model="paychanguForm.webhook_secret"
                  type="password"
                  placeholder="whsec_xxx..."
                  class="mt-1"
                />
                <InputError :message="paychanguForm.errors.webhook_secret" class="mt-2" />
              </div>

              <div class="rounded-lg bg-blue-50 dark:bg-blue-900/10 p-3">
                <p class="text-xs text-blue-700 dark:text-blue-300">
                  Get your API keys from <a href="https://dashboard.paychangu.com" target="_blank" class="underline">PayChangu Dashboard</a>
                </p>
              </div>
            </form>
          </ModalScrollable>

          <ModalFooter>
            <Button
              variant="outline"
              @click="showPayChanguModal = false"
            >
              Cancel
            </Button>
            <Button
              @click="savePayChangu"
              :disabled="paychanguForm.processing"
            >
              <Loader2 v-if="paychanguForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Save Configuration
            </Button>
          </ModalFooter>
        </ModalRoot>
      </DialogContent>
    </Dialog>

    <!-- Create API Key Modal -->
    <Dialog v-model:open="showApiKeyModal">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="Create API Key"
            description="Generate a new API key for integrations"
            :icon="Key"
            :show-close-button="true"
            :on-close="() => showApiKeyModal = false"
          />

          <ModalScrollable>
            <form @submit.prevent="createApiKey" class="space-y-4">
              <div>
                <Label for="key_name">Key Name</Label>
                <Input
                  id="key_name"
                  v-model="apiKeyForm.name"
                  type="text"
                  placeholder="My Integration"
                  required
                  class="mt-1"
                />
                <InputError :message="apiKeyForm.errors.name" class="mt-2" />
              </div>
            </form>
          </ModalScrollable>

          <ModalFooter>
            <Button
              variant="outline"
              @click="showApiKeyModal = false"
            >
              Cancel
            </Button>
            <Button
              @click="createApiKey"
              :disabled="apiKeyForm.processing"
            >
              <Loader2 v-if="apiKeyForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Create Key
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
import InputError from '@/components/InputError.vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import ModalRoot from '@/components/custom/ModalRoot.vue'
import ModalHeader from '@/components/custom/ModalHeader.vue'
import ModalScrollable from '@/components/custom/ModalScrollable.vue'
import ModalFooter from '@/components/custom/ModalFooter.vue'
import { CreditCard, Key, Webhook, Plus, Trash2, Loader2 } from 'lucide-vue-next'

interface ApiKey {
  id: number
  name: string
  prefix: string
  created_at: string
  last_used_at: string | null
}

interface Props {
  paychangu?: {
    public_key?: string
    secret_key?: string
    webhook_secret?: string
  }
  apiKeys: ApiKey[]
}

const props = defineProps<Props>()

const showPayChanguModal = ref(false)
const showApiKeyModal = ref(false)
const paychanguConnected = ref(!!props.paychangu?.public_key)

const paychanguForm = useForm({
  public_key: props.paychangu?.public_key || '',
  secret_key: '',
  webhook_secret: '',
})

const apiKeyForm = useForm({
  name: '',
})

const savePayChangu = () => {
  paychanguForm.post(route('settings.organization.integrations.paychangu'), {
    preserveScroll: true,
    onSuccess: () => {
      showPayChanguModal.value = false
      paychanguConnected.value = true
    },
  })
}

const createApiKey = () => {
  apiKeyForm.post(route('settings.organization.integrations.api-keys.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showApiKeyModal.value = false
      apiKeyForm.reset()
    },
  })
}

const revokeApiKey = (keyId: number) => {
  if (confirm('Are you sure you want to revoke this API key?')) {
    router.delete(route('settings.organization.integrations.api-keys.destroy', keyId), {
      preserveScroll: true,
    })
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}
</script>

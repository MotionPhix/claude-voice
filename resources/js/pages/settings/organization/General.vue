<template>
  <Head title="Organization Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Organization</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Manage your organization's basic information and contact details
      </p>
    </div>

    <!-- Organization Logo Section -->
    <SettingsSection
      title="Organization Logo"
      description="Upload your organization logo for invoices and branding"
    >
      <div class="flex items-center gap-6">
        <div class="relative">
          <img
            :src="form.logo || `https://ui-avatars.com/api/?name=${encodeURIComponent(form.name)}&background=6366f1&color=fff&size=128`"
            :alt="form.name"
            class="h-20 w-20 rounded-lg object-cover border-2 border-gray-200 dark:border-gray-700"
          />
        </div>
        <div class="flex-1">
          <Button variant="outline" size="sm">
            <Upload class="mr-2 h-4 w-4" />
            Upload Logo
          </Button>
          <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            PNG, JPG or SVG. Recommended size: 200x200px.
          </p>
        </div>
      </div>
    </SettingsSection>

    <!-- Basic Information Section -->
    <SettingsSection
      title="Basic Information"
      description="Your organization's name and contact details"
    >
      <form @submit.prevent="updateOrganization" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <Label for="name">Organization Name <span class="text-red-500">*</span></Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1"
            />
            <InputError :message="form.errors.name" class="mt-2" />
          </div>

          <div>
            <Label for="email">Email Address</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1"
            />
            <InputError :message="form.errors.email" class="mt-2" />
          </div>

          <div>
            <Label for="phone">Phone Number</Label>
            <Input
              id="phone"
              v-model="form.phone"
              type="text"
              class="mt-1"
            />
            <InputError :message="form.errors.phone" class="mt-2" />
          </div>

          <div class="sm:col-span-2">
            <Label for="website">Website</Label>
            <Input
              id="website"
              v-model="form.website"
              type="url"
              placeholder="https://example.com"
              class="mt-1"
            />
            <InputError :message="form.errors.website" class="mt-2" />
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
          <p v-if="form.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400">
            Settings saved successfully
          </p>
          <div class="flex items-center gap-3 ml-auto">
            <Button
              type="button"
              variant="outline"
              @click="form.reset()"
              :disabled="form.processing"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="form.processing"
            >
              <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
              Save Changes
            </Button>
          </div>
        </div>
      </form>
    </SettingsSection>

    <!-- Address Section -->
    <SettingsSection
      title="Business Address"
      description="Your organization's physical address for invoices"
    >
      <form @submit.prevent="updateOrganization" class="space-y-6">
        <div class="grid grid-cols-1 gap-6">
          <div>
            <Label for="address">Street Address</Label>
            <Textarea
              id="address"
              v-model="form.address"
              rows="2"
              class="mt-1"
            />
            <InputError :message="form.errors.address" class="mt-2" />
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
              <Label for="city">City</Label>
              <Input
                id="city"
                v-model="form.city"
                type="text"
                class="mt-1"
              />
              <InputError :message="form.errors.city" class="mt-2" />
            </div>

            <div>
              <Label for="state">State/Region</Label>
              <Input
                id="state"
                v-model="form.state"
                type="text"
                class="mt-1"
              />
              <InputError :message="form.errors.state" class="mt-2" />
            </div>

            <div>
              <Label for="postal_code">Postal Code</Label>
              <Input
                id="postal_code"
                v-model="form.postal_code"
                type="text"
                class="mt-1"
              />
              <InputError :message="form.errors.postal_code" class="mt-2" />
            </div>
          </div>

          <div>
            <Label for="country">Country</Label>
            <Input
              id="country"
              v-model="form.country"
              type="text"
              class="mt-1"
            />
            <InputError :message="form.errors.country" class="mt-2" />
          </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 gap-3">
          <Button
            type="button"
            variant="outline"
            @click="form.reset()"
            :disabled="form.processing"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save Address
          </Button>
        </div>
      </form>
    </SettingsSection>

    <!-- Invoice Template Section -->
    <SettingsSection
      title="Invoice Template"
      description="Choose the template design for your invoices"
    >
      <form @submit.prevent="updateInvoiceTemplate" class="space-y-6">
        <div>
          <Label for="invoice_template">Selected Template</Label>
          <Select v-model="templateForm.invoice_template_id">
            <SelectTrigger id="invoice_template" class="mt-1">
              <SelectValue placeholder="Select a template" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="template in templates"
                :key="template.id"
                :value="template.id"
              >
                <div class="flex items-center gap-2">
                  <FileText class="h-4 w-4" />
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-medium">{{ template.name }}</p>
                      <span
                        v-if="template.is_free"
                        class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-0.5 rounded"
                      >
                        Free
                      </span>
                      <span
                        v-else
                        class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded"
                      >
                        ${{ template.price }}
                      </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ template.description }}</p>
                  </div>
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
          <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Choose the invoice template design for your organization. Premium templates will be available for purchase soon.
          </p>
          <InputError :message="templateForm.errors.invoice_template_id" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
          <p v-if="templateForm.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400">
            Template updated successfully
          </p>
          <div class="flex items-center gap-3 ml-auto">
            <Button
              type="button"
              variant="outline"
              @click="templateForm.reset()"
              :disabled="templateForm.processing"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="templateForm.processing"
            >
              <Loader2 v-if="templateForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Save Template
            </Button>
          </div>
        </div>
      </form>
    </SettingsSection>

    <!-- Delete Organization Section (Owners Only) -->
    <SettingsSection
      v-if="isOwner"
      title="Delete Organization"
      description="Permanently delete this organization and all associated data"
    >
      <div class="rounded-lg bg-red-50 dark:bg-red-900/10 p-4">
        <div class="flex">
          <AlertTriangle class="h-5 w-5 text-red-400" />
          <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
              Danger Zone
            </h3>
            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
              <p>
                Deleting this organization will permanently remove all invoices, clients, payments, and team members. This action cannot be undone.
              </p>
            </div>
            <div class="mt-4">
              <Button
                variant="destructive"
                size="sm"
                @click="confirmingDeletion = true"
              >
                Delete Organization
              </Button>
            </div>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- Delete Confirmation Modal -->
    <Dialog v-model:open="confirmingDeletion">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="Delete Organization"
            description="This action cannot be undone"
            :icon="AlertTriangle"
            icon-variant="danger"
            :show-close-button="true"
            :on-close="() => confirmingDeletion = false"
          />

          <ModalScrollable>
            <div class="space-y-4">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Type <strong class="font-semibold text-gray-900 dark:text-gray-100">{{ organization.name }}</strong> to confirm deletion.
              </p>

              <div>
                <Label for="delete_confirm">Organization Name</Label>
                <Input
                  id="delete_confirm"
                  v-model="deleteForm.confirmation"
                  type="text"
                  placeholder="Type organization name"
                  class="mt-1"
                />
              </div>
            </div>
          </ModalScrollable>

          <ModalFooter>
            <Button
              variant="outline"
              @click="confirmingDeletion = false"
            >
              Cancel
            </Button>
            <Button
              variant="destructive"
              @click="deleteOrganization"
              :disabled="deleteForm.confirmation !== organization.name || deleteForm.processing"
            >
              <Loader2 v-if="deleteForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Delete Permanently
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
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from '@/components/InputError.vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import ModalRoot from '@/components/custom/ModalRoot.vue'
import ModalHeader from '@/components/custom/ModalHeader.vue'
import ModalScrollable from '@/components/custom/ModalScrollable.vue'
import ModalFooter from '@/components/custom/ModalFooter.vue'
import { Upload, AlertTriangle, Loader2, FileText } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

interface Template {
  id: number
  uuid: string
  name: string
  slug: string
  description: string
  view_path: string
  is_free: boolean
  price: number
  is_active: boolean
}

interface Props {
  organization: {
    id: number
    name: string
    email?: string
    phone?: string
    website?: string
    address?: string
    city?: string
    state?: string
    postal_code?: string
    country?: string
    logo?: string
    invoice_template_id?: number | null
  }
  templates: Template[]
}

const props = defineProps<Props>()
const { isOwner } = usePermissions()

const form = useForm({
  name: props.organization.name,
  email: props.organization.email,
  phone: props.organization.phone,
  website: props.organization.website,
  address: props.organization.address,
  city: props.organization.city,
  state: props.organization.state,
  postal_code: props.organization.postal_code,
  country: props.organization.country,
  logo: props.organization.logo,
})

const templateForm = useForm({
  invoice_template_id: props.organization.invoice_template_id,
})

const confirmingDeletion = ref(false)
const deleteForm = useForm({
  confirmation: '',
})

const updateOrganization = () => {
  form.put(route('settings.organization.general.update'), {
    preserveScroll: true,
  })
}

const updateInvoiceTemplate = () => {
  templateForm.put(route('settings.organization.general.update'), {
    preserveScroll: true,
  })
}

const deleteOrganization = () => {
  deleteForm.delete(route('settings.organization.general.destroy'), {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(route('dashboard'))
    },
  })
}
</script>

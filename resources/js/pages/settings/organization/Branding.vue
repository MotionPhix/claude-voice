<template>
  <Head title="Branding Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Branding</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Customize your brand colors, invoice templates, and email styling
      </p>
    </div>

    <!-- Brand Colors Section -->
    <SettingsSection
      title="Brand Colors"
      description="Choose colors that match your brand identity"
    >
      <form @submit.prevent="saveColors" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <Label for="primary_color">Primary Color</Label>
            <div class="mt-1 flex items-center gap-3">
              <input
                id="primary_color"
                v-model="colorsForm.primary_color"
                type="color"
                class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600"
              />
              <Input
                v-model="colorsForm.primary_color"
                type="text"
                placeholder="#6366f1"
                class="flex-1"
              />
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Used for buttons and highlights
            </p>
          </div>

          <div>
            <Label for="secondary_color">Secondary Color</Label>
            <div class="mt-1 flex items-center gap-3">
              <input
                id="secondary_color"
                v-model="colorsForm.secondary_color"
                type="color"
                class="h-10 w-20 rounded border border-gray-300 dark:border-gray-600"
              />
              <Input
                v-model="colorsForm.secondary_color"
                type="text"
                placeholder="#8b5cf6"
                class="flex-1"
              />
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Used for accents and secondary elements
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 gap-3">
          <Button
            type="button"
            variant="outline"
            @click="colorsForm.reset()"
            :disabled="colorsForm.processing"
          >
            Reset
          </Button>
          <Button
            type="submit"
            :disabled="colorsForm.processing"
          >
            <Loader2 v-if="colorsForm.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save Colors
          </Button>
        </div>
      </form>
    </SettingsSection>

    <!-- Invoice Template Section -->
    <SettingsSection
      title="Invoice Template"
      description="Customize how your invoices look"
    >
      <div class="space-y-6">
        <div>
          <Label>Template Style</Label>
          <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <button
              v-for="template in templates"
              :key="template.id"
              @click="templateForm.template = template.id"
              :class="[
                templateForm.template === template.id
                  ? 'ring-2 ring-indigo-500 border-indigo-500'
                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                'relative flex flex-col items-center rounded-lg border-2 p-4 transition-all'
              ]"
            >
              <div class="w-full h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded mb-3 flex items-center justify-center">
                <component :is="template.icon" class="h-12 w-12 text-gray-400" />
              </div>
              <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ template.name }}</span>
              <Check
                v-if="templateForm.template === template.id"
                class="absolute top-2 right-2 h-5 w-5 text-indigo-600"
              />
            </button>
          </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
          <Button @click="saveTemplate" :disabled="templateForm.processing">
            <Loader2 v-if="templateForm.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save Template
          </Button>
        </div>
      </div>
    </SettingsSection>

    <!-- Email Branding Section -->
    <SettingsSection
      title="Email Branding"
      description="Customize email templates sent to clients"
    >
      <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-start gap-4">
          <Mail class="h-5 w-5 text-gray-400 mt-0.5" />
          <div class="flex-1">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
              Email Customization
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Advanced email template customization will be available soon. For now, emails will use your brand colors automatically.
            </p>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- Invoice Footer Section -->
    <SettingsSection
      title="Invoice Footer"
      description="Add custom text to the bottom of your invoices"
    >
      <form @submit.prevent="saveFooter" class="space-y-6">
        <div>
          <Label for="footer_text">Footer Text</Label>
          <Textarea
            id="footer_text"
            v-model="footerForm.footer_text"
            rows="4"
            placeholder="Thank you for your business!"
            class="mt-1"
          />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            This text will appear at the bottom of all invoices
          </p>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 gap-3">
          <Button
            type="button"
            variant="outline"
            @click="footerForm.reset()"
            :disabled="footerForm.processing"
          >
            Reset
          </Button>
          <Button
            type="submit"
            :disabled="footerForm.processing"
          >
            <Loader2 v-if="footerForm.processing" class="mr-2 h-4 w-4 animate-spin" />
            Save Footer
          </Button>
        </div>
      </form>
    </SettingsSection>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { FileText, Layers, Box, Mail, Check, Loader2 } from 'lucide-vue-next'

interface Props {
  branding: {
    primary_color?: string
    secondary_color?: string
    template?: string
    footer_text?: string
  }
}

const props = defineProps<Props>()

const templates = [
  { id: 'modern', name: 'Modern', icon: FileText },
  { id: 'classic', name: 'Classic', icon: Layers },
  { id: 'minimal', name: 'Minimal', icon: Box },
]

const colorsForm = useForm({
  primary_color: props.branding?.primary_color || '#6366f1',
  secondary_color: props.branding?.secondary_color || '#8b5cf6',
})

const templateForm = useForm({
  template: props.branding?.template || 'modern',
})

const footerForm = useForm({
  footer_text: props.branding?.footer_text || '',
})

const saveColors = () => {
  colorsForm.put(route('settings.organization.branding.update-colors'), {
    preserveScroll: true,
  })
}

const saveTemplate = () => {
  templateForm.put(route('settings.organization.branding.update-template'), {
    preserveScroll: true,
  })
}

const saveFooter = () => {
  footerForm.put(route('settings.organization.branding.update-footer'), {
    preserveScroll: true,
  })
}
</script>

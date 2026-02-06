<template>
  <Head title="Notification Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notifications</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Manage how you receive notifications and updates
      </p>
    </div>

    <!-- Email Notifications Section -->
    <SettingsSection
      title="Email Notifications"
      description="Choose what email notifications you want to receive"
    >
      <form @submit.prevent="saveNotifications" class="space-y-4">
        <div class="space-y-4">
          <div v-for="notification in emailNotifications" :key="notification.key" class="flex items-start">
            <div class="flex items-center h-5">
              <CheckboxInput
                :id="notification.key"
                :name="notification.key"
                v-model="form[notification.key]"
              />
            </div>
            <div class="ml-3 text-sm">
              <Label :for="notification.key" class="font-medium text-gray-900 dark:text-gray-100">
                {{ notification.label }}
              </Label>
              <p class="text-gray-500 dark:text-gray-400">{{ notification.description }}</p>
            </div>
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
            Save Preferences
          </Button>
        </div>
      </form>
    </SettingsSection>

    <!-- System Notifications Section -->
    <SettingsSection
      title="System Notifications"
      description="Manage browser and system notifications"
    >
      <div class="space-y-4">
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <CheckboxInput id="browser_notifications" name="browser_notifications" v-model="browserNotifications" />
          </div>
          <div class="ml-3 text-sm">
            <Label for="browser_notifications" class="font-medium text-gray-900 dark:text-gray-100">
              Browser Notifications
            </Label>
            <p class="text-gray-500 dark:text-gray-400">Receive notifications in your browser when you're online</p>
          </div>
        </div>
      </div>
    </SettingsSection>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import CheckboxInput from '@/components/CheckboxInput.vue'
import { Label } from '@/components/ui/label'
import { Loader2 } from 'lucide-vue-next'

interface Props {
  preferences: {
    invoice_sent: boolean
    payment_received: boolean
    invoice_overdue: boolean
    weekly_summary: boolean
    marketing_emails: boolean
  }
}

const props = defineProps<Props>()

const emailNotifications = [
  {
    key: 'invoice_sent',
    label: 'Invoice Sent',
    description: 'Get notified when an invoice is sent to a client',
  },
  {
    key: 'payment_received',
    label: 'Payment Received',
    description: 'Get notified when a payment is received',
  },
  {
    key: 'invoice_overdue',
    label: 'Invoice Overdue',
    description: 'Get notified when an invoice becomes overdue',
  },
  {
    key: 'weekly_summary',
    label: 'Weekly Summary',
    description: 'Receive a weekly summary of your invoices and payments',
  },
  {
    key: 'marketing_emails',
    label: 'Marketing Emails',
    description: 'Receive emails about new features and updates',
  },
]

const form = useForm({
  invoice_sent: props.preferences?.invoice_sent ?? true,
  payment_received: props.preferences?.payment_received ?? true,
  invoice_overdue: props.preferences?.invoice_overdue ?? true,
  weekly_summary: props.preferences?.weekly_summary ?? false,
  marketing_emails: props.preferences?.marketing_emails ?? false,
})

const browserNotifications = ref(false)

const saveNotifications = () => {
  form.put(route('settings.account.notifications.update'), {
    preserveScroll: true,
  })
}
</script>

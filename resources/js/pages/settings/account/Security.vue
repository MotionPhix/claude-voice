<template>
  <Head title="Security Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Security</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Manage your password and account security
      </p>
    </div>

    <!-- Change Password Section -->
    <SettingsSection
      title="Password"
      description="Update your password to keep your account secure"
    >
      <form @submit.prevent="updatePassword" class="space-y-6">
        <div class="space-y-4">
          <div>
            <Label for="current_password">Current Password</Label>
            <Input
              id="current_password"
              v-model="passwordForm.current_password"
              type="password"
              autocomplete="current-password"
              class="mt-1"
            />
            <InputError :message="passwordForm.errors.current_password" class="mt-2" />
          </div>

          <div>
            <Label for="password">New Password</Label>
            <Input
              id="password"
              v-model="passwordForm.password"
              type="password"
              autocomplete="new-password"
              class="mt-1"
            />
            <InputError :message="passwordForm.errors.password" class="mt-2" />
          </div>

          <div>
            <Label for="password_confirmation">Confirm New Password</Label>
            <Input
              id="password_confirmation"
              v-model="passwordForm.password_confirmation"
              type="password"
              autocomplete="new-password"
              class="mt-1"
            />
            <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
          <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400">
            Password updated successfully
          </p>
          <div class="flex items-center gap-3 ml-auto">
            <Button
              type="button"
              variant="outline"
              @click="passwordForm.reset()"
              :disabled="passwordForm.processing"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="passwordForm.processing"
            >
              <Loader2 v-if="passwordForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Update Password
            </Button>
          </div>
        </div>
      </form>
    </SettingsSection>

    <!-- Two-Factor Authentication Section (Future) -->
    <SettingsSection
      title="Two-Factor Authentication"
      description="Add an extra layer of security to your account"
    >
      <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
        <div class="flex items-start">
          <ShieldCheck class="h-5 w-5 text-gray-400 mt-0.5" />
          <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
              Coming Soon
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Two-factor authentication will be available soon to help protect your account.
            </p>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- Active Sessions Section -->
    <SettingsSection
      title="Active Sessions"
      description="Manage and sign out of your active sessions on other browsers and devices"
    >
      <div class="space-y-4">
        <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
              <Monitor class="h-5 w-5 text-gray-400" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">This Device</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Current session</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-200">
              Active
            </span>
          </div>
        </div>

        <Button variant="outline" size="sm" class="w-full sm:w-auto">
          <LogOut class="mr-2 h-4 w-4" />
          Sign out all other sessions
        </Button>
      </div>
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
import InputError from '@/components/InputError.vue'
import { ShieldCheck, Monitor, LogOut, Loader2 } from 'lucide-vue-next'

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const updatePassword = () => {
  passwordForm.put(route('settings.account.security.update'), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
    },
  })
}
</script>

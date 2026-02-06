<template>
  <Head title="Profile Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Profile</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Update your personal information and profile picture
      </p>
    </div>

    <!-- Profile Photo Section -->
    <SettingsSection
      title="Profile Photo"
      description="Update your profile photo to personalize your account"
    >
      <div class="flex items-center gap-6">
        <div class="relative">
          <img
            :src="form.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(form.name)}&background=6366f1&color=fff`"
            :alt="form.name"
            class="h-20 w-20 rounded-full object-cover"
          />
        </div>
        <div class="flex-1">
          <Button variant="outline" size="sm">
            <Upload class="mr-2 h-4 w-4" />
            Change Photo
          </Button>
          <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            JPG, PNG or GIF. Max size of 2MB.
          </p>
        </div>
      </div>
    </SettingsSection>

    <!-- Personal Information Section -->
    <SettingsSection
      title="Personal Information"
      description="Update your name and email address"
    >
      <form @submit.prevent="updateProfile" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <Label for="name">Full Name</Label>
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
              required
              class="mt-1"
            />
            <InputError :message="form.errors.email" class="mt-2" />
          </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
          <p v-if="form.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400">
            Profile updated successfully
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

    <!-- Delete Account Section -->
    <SettingsSection
      title="Delete Account"
      description="Permanently delete your account and all associated data"
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
                Once you delete your account, there is no going back. Please be certain.
              </p>
            </div>
            <div class="mt-4">
              <Button
                variant="destructive"
                size="sm"
                @click="confirmingUserDeletion = true"
              >
                Delete Account
              </Button>
            </div>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- Delete Account Modal -->
    <Dialog v-model:open="confirmingUserDeletion">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="Delete Account"
            description="Are you sure you want to delete your account?"
            :icon="AlertTriangle"
            icon-variant="danger"
            :show-close-button="true"
            :on-close="() => confirmingUserDeletion = false"
          />

          <ModalScrollable>
            <div class="space-y-4">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
              </p>

              <div>
                <Label for="password">Password</Label>
                <Input
                  id="password"
                  v-model="deleteForm.password"
                  type="password"
                  placeholder="Enter your password"
                  class="mt-1"
                  @keyup.enter="deleteAccount"
                />
                <InputError :message="deleteForm.errors.password" class="mt-2" />
              </div>
            </div>
          </ModalScrollable>

          <ModalFooter>
            <Button
              variant="outline"
              @click="confirmingUserDeletion = false"
            >
              Cancel
            </Button>
            <Button
              variant="destructive"
              @click="deleteAccount"
              :disabled="deleteForm.processing"
            >
              <Loader2 v-if="deleteForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Delete Account
            </Button>
          </ModalFooter>
        </ModalRoot>
      </DialogContent>
    </Dialog>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import ModalRoot from '@/components/custom/ModalRoot.vue'
import ModalHeader from '@/components/custom/ModalHeader.vue'
import ModalScrollable from '@/components/custom/ModalScrollable.vue'
import ModalFooter from '@/components/custom/ModalFooter.vue'
import { Upload, AlertTriangle, Loader2 } from 'lucide-vue-next'

interface Props {
  user: {
    name: string
    email: string
    avatar?: string
  }
}

const props = defineProps<Props>()

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  avatar: props.user.avatar,
})

const confirmingUserDeletion = ref(false)
const deleteForm = useForm({
  password: '',
})

const updateProfile = () => {
  form.patch(route('settings.account.profile.update'), {
    preserveScroll: true,
  })
}

const deleteAccount = () => {
  deleteForm.delete(route('settings.account.profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => {
      confirmingUserDeletion.value = false
    },
    onError: () => {
      deleteForm.reset('password')
    },
  })
}
</script>

<template>
  <Head title="Team Settings" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Team</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Manage team members and their permissions
        </p>
      </div>
      <Button v-if="canManageMembers" @click="showInviteModal = true">
        <UserPlus class="mr-2 h-4 w-4" />
        Invite Member
      </Button>
    </div>

    <!-- Team Members Section -->
    <SettingsSection
      title="Team Members"
      description="People who have access to this organization"
    >
      <div class="space-y-4">
        <div
          v-for="member in members"
          :key="member.id"
          class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
              <img
                :src="member.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}&background=6366f1&color=fff`"
                :alt="member.name"
                class="h-10 w-10 rounded-full"
              />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ member.name }}
                <span v-if="member.id === $page.props.auth.user.id" class="ml-2 text-xs text-gray-500 dark:text-gray-400">(You)</span>
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ member.email }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Badge :variant="getRoleVariant(member.pivot.role)">
              {{ member.pivot.role_label || member.pivot.role }}
            </Badge>
            <Button
              v-if="canManageMembers && member.id !== $page.props.auth.user.id"
              variant="ghost"
              size="sm"
              @click="confirmRemoveMember(member)"
            >
              <Trash2 class="h-4 w-4 text-red-600 dark:text-red-400" />
            </Button>
          </div>
        </div>

        <div v-if="members.length === 0" class="text-center py-8">
          <Users class="mx-auto h-12 w-12 text-gray-400" />
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No team members yet</p>
        </div>
      </div>
    </SettingsSection>

    <!-- Pending Invitations Section -->
    <SettingsSection
      v-if="pendingInvitations?.length > 0"
      title="Pending Invitations"
      description="Invitations that haven't been accepted yet"
    >
      <div class="space-y-4">
        <div
          v-for="invitation in pendingInvitations"
          :key="invitation.id"
          class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
              <Mail class="h-10 w-10 text-gray-400" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ invitation.email }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Invited {{ formatDate(invitation.created_at) }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Badge variant="secondary">
              {{ invitation.role_label || invitation.role }}
            </Badge>
            <Button
              v-if="canManageMembers"
              variant="ghost"
              size="sm"
              @click="cancelInvitation(invitation.id)"
            >
              <X class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>
    </SettingsSection>

    <!-- Invite Member Modal -->
    <Dialog v-model:open="showInviteModal">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="Invite Team Member"
            description="Send an invitation to join your organization"
            :icon="UserPlus"
            :show-close-button="true"
            :on-close="() => showInviteModal = false"
          />

          <ModalScrollable>
            <form @submit.prevent="sendInvitation" class="space-y-4">
              <div>
                <Label for="email">Email Address</Label>
                <Input
                  id="email"
                  v-model="inviteForm.email"
                  type="email"
                  placeholder="member@example.com"
                  required
                  class="mt-1"
                />
                <InputError :message="inviteForm.errors.email" class="mt-2" />
              </div>

              <div>
                <Label for="role">Role</Label>
                <Select v-model="inviteForm.role" class="mt-1">
                  <SelectTrigger>
                    <SelectValue placeholder="Select a role" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="member">Member</SelectItem>
                    <SelectItem value="manager">Manager</SelectItem>
                    <SelectItem value="accountant">Accountant</SelectItem>
                    <SelectItem value="admin">Admin</SelectItem>
                  </SelectContent>
                </Select>
                <InputError :message="inviteForm.errors.role" class="mt-2" />
              </div>
            </form>
          </ModalScrollable>

          <ModalFooter>
            <Button
              variant="outline"
              @click="showInviteModal = false"
            >
              Cancel
            </Button>
            <Button
              @click="sendInvitation"
              :disabled="inviteForm.processing"
            >
              <Loader2 v-if="inviteForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Send Invitation
            </Button>
          </ModalFooter>
        </ModalRoot>
      </DialogContent>
    </Dialog>

    <!-- Remove Member Confirmation Modal -->
    <Dialog v-model:open="showRemoveModal">
      <DialogContent>
        <ModalRoot>
          <ModalHeader
            title="Remove Team Member"
            description="Are you sure you want to remove this member?"
            :icon="AlertTriangle"
            icon-variant="danger"
            :show-close-button="true"
            :on-close="() => showRemoveModal = false"
          />

          <ModalScrollable>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ memberToRemove?.name }} will lose access to this organization and all associated data.
            </p>
          </ModalScrollable>

          <ModalFooter>
            <Button
              variant="outline"
              @click="showRemoveModal = false"
            >
              Cancel
            </Button>
            <Button
              variant="destructive"
              @click="removeMember"
              :disabled="removeForm.processing"
            >
              <Loader2 v-if="removeForm.processing" class="mr-2 h-4 w-4 animate-spin" />
              Remove Member
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from '@/components/InputError.vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import ModalRoot from '@/components/custom/ModalRoot.vue'
import ModalHeader from '@/components/custom/ModalHeader.vue'
import ModalScrollable from '@/components/custom/ModalScrollable.vue'
import ModalFooter from '@/components/custom/ModalFooter.vue'
import { UserPlus, Users, Mail, X, Trash2, AlertTriangle, Loader2 } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

interface Member {
  id: number
  name: string
  email: string
  avatar?: string
  pivot: {
    role: string
    role_label?: string
  }
}

interface Invitation {
  id: number
  email: string
  role: string
  role_label?: string
  created_at: string
}

interface Props {
  members: Member[]
  pendingInvitations?: Invitation[]
}

const props = defineProps<Props>()
const { canManageMembers } = usePermissions()

const showInviteModal = ref(false)
const showRemoveModal = ref(false)
const memberToRemove = ref<Member | null>(null)

const inviteForm = useForm({
  email: '',
  role: 'member',
})

const removeForm = useForm({})

const sendInvitation = () => {
  inviteForm.post(route('settings.organization.team.invite'), {
    preserveScroll: true,
    onSuccess: () => {
      showInviteModal.value = false
      inviteForm.reset()
    },
  })
}

const confirmRemoveMember = (member: Member) => {
  memberToRemove.value = member
  showRemoveModal.value = true
}

const removeMember = () => {
  if (!memberToRemove.value) return

  removeForm.delete(route('settings.organization.team.remove', memberToRemove.value.uuid), {
    preserveScroll: true,
    onSuccess: () => {
      showRemoveModal.value = false
      memberToRemove.value = null
    },
  })
}

const cancelInvitation = (invitationId: number) => {
  router.delete(route('settings.organization.team.cancel-invitation', invitationId), {
    preserveScroll: true,
  })
}

const getRoleVariant = (role: string) => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    owner: 'default',
    admin: 'default',
    manager: 'secondary',
    accountant: 'secondary',
    member: 'outline',
  }
  return variants[role.toLowerCase()] || 'outline'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}
</script>

<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Crown, Shield, Users, Calculator, User } from 'lucide-vue-next'

interface Props {
  role: 'owner' | 'admin' | 'manager' | 'accountant' | 'user'
  showIcon?: boolean
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  showIcon: true,
  size: 'md'
})

const roleConfig = {
  owner: {
    label: 'Owner',
    color: 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
    icon: Crown
  },
  admin: {
    label: 'Admin',
    color: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
    icon: Shield
  },
  manager: {
    label: 'Manager',
    color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
    icon: Users
  },
  accountant: {
    label: 'Accountant',
    color: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
    icon: Calculator
  },
  user: {
    label: 'User',
    color: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    icon: User
  }
}

const config = computed(() => roleConfig[props.role])

const iconSize = computed(() => {
  switch (props.size) {
    case 'sm': return 'h-3 w-3'
    case 'lg': return 'h-5 w-5'
    default: return 'h-4 w-4'
  }
})

const textSize = computed(() => {
  switch (props.size) {
    case 'sm': return 'text-xs'
    case 'lg': return 'text-sm'
    default: return 'text-xs'
  }
})
</script>

<template>
  <Badge
    :class="[
      config.color,
      textSize,
      'inline-flex items-center gap-1 font-medium'
    ]"
  >
    <component
      v-if="showIcon"
      :is="config.icon"
      :class="[iconSize, 'flex-shrink-0']"
    />
    {{ config.label }}
  </Badge>
</template>
<script setup lang="ts">
import { Button } from '@/components/ui/button'

defineProps<{
  isSaving: boolean
}>()

defineEmits<{
  save: []
  'toggle-preview': []
}>()
</script>

<template>
  <div class="border-b border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
    <div class="flex items-center justify-between">
      <div class="flex gap-2">
        <Button variant="outline" size="sm" @click="$emit('toggle-preview')">
          Toggle Preview
        </Button>
      </div>
      <Button size="sm" :disabled="isSaving" @click="$emit('save')">
        {{ isSaving ? 'Saving...' : 'Save Design' }}
      </Button>
    </div>
  </div>
</template>

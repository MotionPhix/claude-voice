<script setup lang="ts">
import { computed } from 'vue'

defineProps<{
  modelValue: string
  mode: 'design' | 'preview'
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()

const canvasClasses = computed(() => {
  return {
    'bg-white dark:bg-gray-900': true,
    'p-8': true,
  }
})
</script>

<template>
  <div class="bg-gray-100 p-4 dark:bg-gray-950">
    <div :class="canvasClasses" class="mx-auto max-w-4xl rounded-lg shadow-lg">
      <div v-if="mode === 'design'">
        <div
          contenteditable="true"
          :innerText="modelValue"
          @input="(e) => $emit('update:modelValue', (e.target as HTMLElement).innerText)"
          class="min-h-96 outline-none"
        />
      </div>
      <div v-else>
        <!-- Preview mode -->
        <div v-html="modelValue" class="prose dark:prose-invert" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { InvoiceTemplate } from '@/types'

defineProps<{
  template: InvoiceTemplate
}>()
</script>

<template>
  <div class="p-12">
    <div v-if="!template.design" class="text-center text-gray-500 dark:text-gray-400">
      <p>No design yet. Go to edit mode to create your template design.</p>
    </div>
    <div
      v-else
      v-html="template.design"
      class="prose dark:prose-invert mx-auto max-w-2xl"
    />
  </div>
</template>

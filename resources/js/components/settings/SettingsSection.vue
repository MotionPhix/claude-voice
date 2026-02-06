<template>
  <Card :padding="padding" class="mb-6">
    <template v-if="title || description" #header>
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
            {{ title }}
          </h3>
          <p v-if="description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ description }}
          </p>
        </div>
        <slot name="header-actions" />
      </div>
    </template>

    <slot />

    <template v-if="$slots.footer" #footer>
      <slot name="footer" />
    </template>
  </Card>
</template>

<script setup lang="ts">
import Card from '@/components/custom/Card.vue'

interface Props {
  title?: string
  description?: string
  padding?: 'none' | 'sm' | 'md' | 'lg'
}

withDefaults(defineProps<Props>(), {
  padding: 'lg'
})
</script>

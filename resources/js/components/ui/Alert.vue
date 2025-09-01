<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  variant?: 'default' | 'destructive'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default'
})

const alertClasses = computed(() => {
  const baseClasses = 'relative w-full rounded-lg border p-4'
  
  const variantClasses = {
    default: 'bg-background text-foreground border-border',
    destructive: 'border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950/10 dark:text-red-400'
  }

  return cn(
    baseClasses,
    variantClasses[props.variant],
    props.class
  )
})
</script>

<template>
  <div :class="alertClasses" role="alert">
    <slot />
  </div>
</template>

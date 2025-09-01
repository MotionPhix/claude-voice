<script setup lang="ts">
import { computed } from 'vue';

interface ProgressProps {
  value: number;
  max?: number;
  className?: string;
}

const props = withDefaults(defineProps<ProgressProps>(), {
  max: 100
});

const percentage = computed(() => {
  return Math.min(Math.max(props.value, 0), props.max);
});

const progressPercentage = computed(() => {
  return (percentage.value / props.max) * 100;
});
</script>

<template>
  <div
    :class="[
      'relative w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800',
      className
    ]"
    role="progressbar"
    :aria-valuenow="value"
    :aria-valuemax="max"
  >
    <div
      class="h-2 w-full flex-1 bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-300 ease-in-out"
      :style="{ width: `${progressPercentage}%` }"
    />
  </div>
</template>

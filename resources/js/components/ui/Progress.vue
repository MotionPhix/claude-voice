<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  value?: number;
  max?: number;
  class?: string;
  indicatorClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  value: 0,
  max: 100,
  class: '',
  indicatorClass: ''
});

const percentage = computed(() => {
  return Math.min(100, Math.max(0, (props.value / props.max) * 100));
});
</script>

<template>
  <div
    :class="[
      'relative h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800',
      props.class
    ]"
  >
    <div
      :class="[
        'h-full w-full flex-1 bg-indigo-600 transition-all duration-300 ease-in-out',
        props.indicatorClass
      ]"
      :style="{ transform: `translateX(-${100 - percentage}%)` }"
    />
  </div>
</template>

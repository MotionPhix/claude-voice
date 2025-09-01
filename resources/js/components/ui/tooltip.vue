<!-- Tooltip.vue -->
<script setup lang="ts">
import { ref, computed } from 'vue';

interface Props {
  delayDuration?: number;
}

const props = withDefaults(defineProps<Props>(), {
  delayDuration: 700
});

const isVisible = ref(false);
const timeoutId = ref<NodeJS.Timeout | null>(null);

const showTooltip = () => {
  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
  }
  
  timeoutId.value = setTimeout(() => {
    isVisible.value = true;
  }, props.delayDuration);
};

const hideTooltip = () => {
  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
    timeoutId.value = null;
  }
  isVisible.value = false;
};
</script>

<template>
  <div class="relative inline-block">
    <div
      @mouseenter="showTooltip"
      @mouseleave="hideTooltip"
      @focus="showTooltip"
      @blur="hideTooltip"
    >
      <slot />
    </div>
    
    <Teleport to="body">
      <div
        v-if="isVisible"
        class="absolute z-50 px-3 py-1.5 text-sm text-white bg-gray-900 rounded-md shadow-lg dark:bg-gray-700 pointer-events-none"
        :style="{ top: '0px', left: '0px' }"
      >
        <slot name="content" />
        <div class="absolute w-2 h-2 bg-gray-900 dark:bg-gray-700 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2"></div>
      </div>
    </Teleport>
  </div>
</template>

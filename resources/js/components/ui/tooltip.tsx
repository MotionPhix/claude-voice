<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface TooltipProps {
  content: string;
  side?: 'top' | 'bottom' | 'left' | 'right';
  sideOffset?: number;
  delayDuration?: number;
}

const props = withDefaults(defineProps<TooltipProps>(), {
  side: 'top',
  sideOffset: 4,
  delayDuration: 400
});

const show = ref(false);
const trigger = ref<HTMLElement>();
const tooltip = ref<HTMLElement>();

const showTooltip = () => {
  show.value = true;
};

const hideTooltip = () => {
  show.value = false;
};

const tooltipClasses = computed(() => {
  return cn(
    'absolute z-50 overflow-hidden rounded-md bg-gray-900 px-3 py-1.5 text-xs text-white shadow-md',
    'animate-in fade-in-0 zoom-in-95 duration-200',
    'data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95',
    {
      'bottom-full left-1/2 transform -translate-x-1/2 mb-2': props.side === 'top',
      'top-full left-1/2 transform -translate-x-1/2 mt-2': props.side === 'bottom',
      'right-full top-1/2 transform -translate-y-1/2 mr-2': props.side === 'left',
      'left-full top-1/2 transform -translate-y-1/2 ml-2': props.side === 'right',
    }
  );
});
</script>

<template>
  <div class="relative inline-block">
    <div 
      ref="trigger"
      @mouseenter="showTooltip"
      @mouseleave="hideTooltip"
      @focus="showTooltip"
      @blur="hideTooltip"
    >
      <slot />
    </div>
    <Teleport to="body">
      <div
        v-if="show"
        ref="tooltip"
        :class="tooltipClasses"
        role="tooltip"
      >
        {{ content }}
        <div
          :class="[
            'absolute w-2 h-2 bg-gray-900 transform rotate-45',
            {
              'top-full left-1/2 -translate-x-1/2 -translate-y-1/2': side === 'top',
              'bottom-full left-1/2 -translate-x-1/2 translate-y-1/2': side === 'bottom',
              'top-1/2 left-full -translate-y-1/2 -translate-x-1/2': side === 'left',
              'top-1/2 right-full -translate-y-1/2 translate-x-1/2': side === 'right',
            }
          ]"
        />
      </div>
    </Teleport>
  </div>
</template>

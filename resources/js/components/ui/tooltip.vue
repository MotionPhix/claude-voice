<script setup lang="ts">
import { ref } from 'vue';

interface TooltipProps {
  content: string;
  side?: 'top' | 'bottom' | 'left' | 'right';
}

const props = withDefaults(defineProps<TooltipProps>(), {
  side: 'top'
});

const show = ref(false);
</script>

<template>
  <div class="relative inline-block">
    <div 
      @mouseenter="show = true"
      @mouseleave="show = false"
      @focus="show = true"
      @blur="show = false"
    >
      <slot />
    </div>
    
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="show"
        :class="[
          'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded-md shadow-lg whitespace-nowrap',
          {
            'bottom-full left-1/2 transform -translate-x-1/2 mb-2': side === 'top',
            'top-full left-1/2 transform -translate-x-1/2 mt-2': side === 'bottom',
            'right-full top-1/2 transform -translate-y-1/2 mr-2': side === 'left',
            'left-full top-1/2 transform -translate-y-1/2 ml-2': side === 'right',
          }
        ]"
        role="tooltip"
      >
        {{ content }}
        <!-- Arrow -->
        <div
          :class="[
            'absolute w-2 h-2 bg-gray-900 transform rotate-45',
            {
              'top-full left-1/2 -translate-x-1/2 -mt-1': side === 'top',
              'bottom-full left-1/2 -translate-x-1/2 -mb-1': side === 'bottom',
              'top-1/2 left-full -translate-y-1/2 -ml-1': side === 'left',
              'top-1/2 right-full -translate-y-1/2 -mr-1': side === 'right',
            }
          ]"
        />
      </div>
    </Transition>
  </div>
</template>

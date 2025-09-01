<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface PopoverProps {
  open?: boolean;
  placement?: 'top' | 'bottom' | 'left' | 'right';
}

const props = withDefaults(defineProps<PopoverProps>(), {
  open: false,
  placement: 'bottom'
});

const emit = defineEmits<{
  'update:open': [value: boolean];
}>();

const triggerRef = ref<HTMLElement>();
const contentRef = ref<HTMLElement>();

const handleTriggerClick = () => {
  emit('update:open', !props.open);
};

const handleOutsideClick = (event: MouseEvent) => {
  if (
    triggerRef.value &&
    contentRef.value &&
    !triggerRef.value.contains(event.target as Node) &&
    !contentRef.value.contains(event.target as Node)
  ) {
    emit('update:open', false);
  }
};

onMounted(() => {
  document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick);
});
</script>

<template>
  <div class="relative">
    <!-- Trigger -->
    <div ref="triggerRef" @click="handleTriggerClick">
      <slot name="trigger" />
    </div>

    <!-- Content -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="open"
          ref="contentRef"
          :class="[
            'absolute z-50 min-w-[200px] overflow-hidden rounded-md border bg-white p-1 text-gray-950 shadow-md',
            'dark:bg-gray-950 dark:text-gray-50 dark:border-gray-800',
            {
              'bottom-full mb-2': placement === 'top',
              'top-full mt-2': placement === 'bottom',
              'right-full mr-2': placement === 'left',
              'left-full ml-2': placement === 'right',
            }
          ]"
        >
          <slot />
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Check, Minus } from 'lucide-vue-next';

interface CheckboxProps {
  checked?: boolean;
  indeterminate?: boolean;
  disabled?: boolean;
  className?: string;
}

const props = withDefaults(defineProps<CheckboxProps>(), {
  checked: false,
  indeterminate: false,
  disabled: false
});

const emit = defineEmits<{
  'update:checked': [value: boolean];
}>();

const handleClick = () => {
  if (!props.disabled) {
    emit('update:checked', !props.checked);
  }
};

const checkboxClasses = computed(() => {
  return [
    'peer h-4 w-4 shrink-0 rounded-sm border border-gray-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    {
      'bg-blue-600 border-blue-600 text-white': props.checked || props.indeterminate,
      'bg-white dark:bg-gray-900 dark:border-gray-600': !props.checked && !props.indeterminate,
      'cursor-pointer': !props.disabled,
      'cursor-not-allowed opacity-50': props.disabled
    },
    props.className
  ];
});
</script>

<template>
  <div class="flex items-center">
    <button
      type="button"
      :class="checkboxClasses"
      :disabled="disabled"
      :aria-checked="indeterminate ? 'mixed' : checked"
      role="checkbox"
      @click="handleClick"
    >
      <Check
        v-if="checked && !indeterminate"
        class="h-3 w-3 text-white"
      />
      <Minus
        v-else-if="indeterminate"
        class="h-3 w-3 text-white"
      />
    </button>
  </div>
</template>

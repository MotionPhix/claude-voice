<script setup lang="ts">
import { computed, ref, useAttrs, watch } from 'vue';
import { Check, Minus } from 'lucide-vue-next';

// Generate a unique ID if one is not provided via props or $attrs
const uuid = `checkbox-${Math.random().toString(36).substring(2, 9)}`;

// Define prop types with better type safety
interface Props {
  modelValue?: any | Array<any>;
  value?: any;
  label?: string;
  disabled?: boolean;
  indeterminate?: boolean;
  name?: string;
  id?: string;
  checked?: boolean;
  'aria-label'?: string;
  'aria-labelledby'?: string;
}

const props = withDefaults(defineProps<Props>(), {
  value: true,
  label: '',
  disabled: false,
  indeterminate: false,
  checked: false,
});

// Define emitted events with proper typing
const emit = defineEmits<{
  'update:modelValue': [value: any];
  'change': [event: Event];
  'focus': [event: FocusEvent];
  'blur': [event: FocusEvent];
}>();

// Inherit attributes like `id` and `name` from parent
const attrs = useAttrs();

// Determine the final `id` for the native input, prioritizing the prop
const inputId = computed<string>(() => props.id || attrs.id as string || uuid);

// Track internal checked state for non-v-model usage
const internalChecked = ref(props.checked);

// Watch for changes to the checked prop and update internal state
watch(() => props.checked, (newValue) => {
  internalChecked.value = newValue;
});

// Determine if the component is checked
const isCheckmarked = computed(() => {
  // If using v-model, derive from modelValue
  if (props.modelValue !== undefined) {
    if (Array.isArray(props.modelValue)) {
      return props.modelValue.includes(props.value);
    }
    return props.modelValue === props.value || props.modelValue === true;
  }

  // Non-v-model usage - use internal state
  return internalChecked.value;
});

// Handle the change event
const handleChange = (event: Event) => {
  if (props.disabled) return;

  const target = event.target as HTMLInputElement;

  if (props.modelValue !== undefined) {
    // v-model usage
    if (Array.isArray(props.modelValue)) {
      const newArray = [...props.modelValue];
      if (target.checked) {
        newArray.push(props.value);
      } else {
        const index = newArray.indexOf(props.value);
        if (index > -1) {
          newArray.splice(index, 1);
        }
      }
      emit('update:modelValue', newArray);
    } else {
      emit('update:modelValue', target.checked ? (props.value === true ? true : props.value) : false);
    }
  } else {
    // Non-v-model usage - update internal state
    internalChecked.value = target.checked;
  }

  // Always emit a native-like `change` event for non-v-model usage or side effects
  emit('change', event);
};

// Handle focus events
const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};

// Handle clicks on the custom checkbox area
const toggle = () => {
  if (props.disabled) return;

  // Use the native input to trigger a change, which will update the v-model or internal state
  const nativeInput = document.getElementById(inputId.value) as HTMLInputElement;
  if (nativeInput) {
    nativeInput.click(); // Use click() instead of manually toggling to ensure all events fire
  }
};
</script>

<template>
  <div class="flex items-center">
    <!-- The native input is hidden but used for accessibility and form submission -->
    <input
      type="checkbox"
      :id="inputId"
      :name="name"
      :checked="isCheckmarked"
      :value="value"
      :disabled="disabled"
      :aria-label="$attrs['aria-label']"
      :aria-labelledby="$attrs['aria-labelledby']"
      class="sr-only peer"
      v-bind="{ ...$attrs, 'aria-label': undefined, 'aria-labelledby': undefined }"
      @change="handleChange"
      @focus="handleFocus"
      @blur="handleBlur"
    />

    <!-- The custom, visible checkbox -->
    <div
      class="relative flex h-5 w-5 items-center justify-center rounded-sm border cursor-pointer transition-colors duration-150 ease-in-out focus-visible:outline-2 focus-visible:outline-blue-600 focus-visible:outline-offset-2 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500 peer-focus-visible:ring-offset-2"
      :class="{
        'border-gray-300 bg-white hover:border-gray-400 dark:border-neutral-700 dark:bg-neutral-800 dark:hover:border-neutral-600': !isCheckmarked && !indeterminate,
        'border-blue-600 bg-blue-600 hover:bg-blue-700 dark:border-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600': isCheckmarked || indeterminate,
        'opacity-50 pointer-events-none cursor-not-allowed': disabled,
      }"
      @click="toggle"
      tabindex="-1"
      role="presentation"
    >
      <Transition
        enter-from-class="opacity-0 scale-75"
        leave-to-class="opacity-0 scale-75"
        enter-active-class="transition-all duration-150 ease-out"
        leave-active-class="transition-all duration-150 ease-in"
      >
        <span v-if="indeterminate" class="flex items-center">
          <Minus :size="16" color="#fff" stroke-width="3" />
        </span>
        <span v-else-if="isCheckmarked" class="flex items-center">
          <Check :size="16" color="#fff" stroke-width="3" />
        </span>
      </Transition>
    </div>

    <!-- The label -->
    <label
      v-if="label"
      :for="inputId"
      class="text-sm text-gray-500 ms-3 dark:text-neutral-400 cursor-pointer select-none"
      :class="{ 'cursor-not-allowed opacity-50': disabled }"
    >
      {{ label }}
    </label>
  </div>
</template>

<!-- TooltipProvider.vue -->
<script setup lang="ts">
interface Props {
  delayDuration?: number;
  disableHoverableContent?: boolean;
  disableClosingTrigger?: boolean;
}

withDefaults(defineProps<Props>(), {
  delayDuration: 700,
  disableHoverableContent: false,
  disableClosingTrigger: false,
});
</script>

<template>
  <div>
    <slot />
  </div>
</template>

// Simple compatibility exports for existing imports
export const TooltipProvider = { 
  setup() {
    return {};
  },
  template: '<div><slot /></div>'
};

export const TooltipTrigger = {
  setup() {
    return {};
  },
  template: '<div><slot /></div>'
};

export const TooltipContent = {
  props: ['content'],
  setup(props: { content: string }) {
    return { content: props.content };
  },
  template: '<div class="tooltip-content">{{ content }}<slot /></div>'
};

export { default as Tooltip } from './tooltip.vue';

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { ref } from 'vue'

defineProps<{
  dynamicFields: string[]
}>()

defineEmits<{
  'add-element': [type: string]
  'select-field': [field: string]
}>()

const expandedSections = ref({
  elements: true,
  fields: true,
})

const elements = [
  { id: 'text', label: 'Text', icon: '¶' },
  { id: 'heading', label: 'Heading', icon: 'H' },
  { id: 'table', label: 'Table', icon: '▦' },
  { id: 'image', label: 'Image', icon: '🖼' },
  { id: 'divider', label: 'Divider', icon: '─' },
]

const toggleSection = (section: string) => {
  expandedSections.value[section as keyof typeof expandedSections.value] = 
    !expandedSections.value[section as keyof typeof expandedSections.value]
}
</script>

<template>
  <div class="overflow-y-auto bg-gray-50 dark:bg-gray-950">
    <!-- Elements Section -->
    <div class="border-b border-gray-200 dark:border-gray-700">
      <button
        @click="toggleSection('elements')"
        class="w-full px-4 py-3 text-left font-semibold text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-900"
      >
        Elements {{ expandedSections.elements ? '▼' : '▶' }}
      </button>
      <div v-if="expandedSections.elements" class="space-y-2 border-t border-gray-200 p-3 dark:border-gray-700">
        <button
          v-for="element in elements"
          :key="element.id"
          @click="$emit('add-element', element.id)"
          class="flex w-full items-center gap-2 rounded px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
        >
          <span>{{ element.icon }}</span>
          <span>{{ element.label }}</span>
        </button>
      </div>
    </div>

    <!-- Dynamic Fields Section -->
    <div>
      <button
        @click="toggleSection('fields')"
        class="w-full px-4 py-3 text-left font-semibold text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-900"
      >
        Dynamic Fields {{ expandedSections.fields ? '▼' : '▶' }}
      </button>
      <div v-if="expandedSections.fields" class="space-y-2 border-t border-gray-200 p-3 dark:border-gray-700">
        <button
          v-for="field in dynamicFields"
          :key="field"
          @click="$emit('select-field', field)"
          class="block w-full rounded bg-yellow-100 px-3 py-2 text-left text-xs font-mono text-yellow-900 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-100 dark:hover:bg-yellow-800"
        >
          [{{ field }}]
        </button>
      </div>
    </div>
  </div>
</template>

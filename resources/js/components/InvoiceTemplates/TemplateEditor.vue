<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { ref, computed } from 'vue'
import type { InvoiceTemplate } from '@/types'
import EditorToolbar from './EditorToolbar.vue'
import EditorCanvas from './EditorCanvas.vue'
import EditorSidebar from './EditorSidebar.vue'

const props = defineProps<{
  template: InvoiceTemplate
}>()

const emit = defineEmits<{
  save: [design: string, dynamicFields: string[]]
}>()

const editorMode = ref<'design' | 'preview'>('design')
const isSaving = ref(false)
const selectedElement = ref<string | null>(null)
const dynamicFields = ref<string[]>([
  'client.name',
  'client.email',
  'client.phone',
  'organization.name',
  'organization.email',
  'invoiceNumber',
  'issueDate',
  'dueDate',
  'items',
  'subtotal',
  'tax',
  'total',
])

const canvasContent = ref(props.template.design || '')

const handleSave = async () => {
  isSaving.value = true
  try {
    emit('save', canvasContent.value, dynamicFields.value)
  } finally {
    isSaving.value = false
  }
}

const handleAddElement = (type: string) => {
  // Add element to canvas
  const element = createElement(type)
  canvasContent.value += element
}

const createElement = (type: string): string => {
  const elements: Record<string, string> = {
    text: '<p class="text-gray-900">Click to edit text</p>',
    heading: '<h1 class="text-2xl font-bold text-gray-900">Heading</h1>',
    table: '<table class="w-full border"><tr><td class="border p-2">Column 1</td><td class="border p-2">Column 2</td></tr></table>',
    field: '<span class="inline-block rounded bg-yellow-100 px-2 py-1 font-mono text-sm text-yellow-900">[field]</span>',
    image: '<img src="" alt="Image" class="max-w-full" />',
    divider: '<hr class="my-4 border-gray-300" />',
  }
  return elements[type] || ''
}
</script>

<template>
  <div class="flex h-full overflow-hidden">
    <!-- Sidebar -->
    <EditorSidebar
      class="w-64 border-r border-gray-200 dark:border-gray-700"
      :dynamic-fields="dynamicFields"
      @add-element="handleAddElement"
      @select-field="(field) => selectedElement = field"
    />

    <!-- Main Editor -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Toolbar -->
      <EditorToolbar
        :is-saving="isSaving"
        @save="handleSave"
        @toggle-preview="editorMode = editorMode === 'design' ? 'preview' : 'design'"
      />

      <!-- Canvas -->
      <EditorCanvas
        v-model="canvasContent"
        :mode="editorMode"
        class="flex-1 overflow-auto"
      />
    </div>
  </div>
</template>

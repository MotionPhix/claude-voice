<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { InvoiceTemplate } from '@/types'
import TemplateEditor from '@/components/InvoiceTemplates/TemplateEditor.vue'

defineProps<{
  template: InvoiceTemplate
}>()

const form = useForm({
  name: '',
  description: '',
  design: null as string | null,
  dynamic_fields: [] as string[],
})

const handleSaveDesign = (design: string, dynamicFields: string[]) => {
  form.design = design
  form.dynamic_fields = dynamicFields
  form.put(route('invoice-templates.update', template.id), {
    onSuccess: () => {
      // Show success message
    },
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Edit Invoice Template" />

    <div class="flex h-screen flex-col overflow-hidden">
      <!-- Header -->
      <div class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ template.name }}</h1>
          <div class="flex gap-3">
            <Button variant="outline" @click="$router.back()">Back</Button>
          </div>
        </div>
      </div>

      <!-- Editor -->
      <div class="flex-1 overflow-hidden">
        <TemplateEditor
          :template="template"
          @save="handleSaveDesign"
        />
      </div>
    </div>
  </AppLayout>
</template>

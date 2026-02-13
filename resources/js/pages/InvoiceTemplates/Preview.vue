<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { InvoiceTemplate } from '@/types'
import TemplatePreview from '@/components/InvoiceTemplates/TemplatePreview.vue'

defineProps<{
  template: InvoiceTemplate
}>()
</script>

<template>
  <AppLayout>
    <Head :title="`${template.name} - Preview`" />

    <div class="mx-auto max-w-7xl">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ template.name }}</h1>
        <Button variant="outline" @click="$router.back()">Back</Button>
      </div>

      <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        <TemplatePreview :template="template" />
      </div>
    </div>
  </AppLayout>
</template>

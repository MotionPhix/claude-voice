<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { InvoiceTemplate } from '@/types'

defineProps<{
  templates: InvoiceTemplate[]
}>()
</script>

<template>
  <AppLayout>
    <Head title="Invoice Templates" />

    <div class="mx-auto max-w-7xl">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Invoice Templates</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Create and manage invoice templates for your organization</p>
        </div>
        <Link :href="route('invoice-templates.create')">
          <Button>Create Template</Button>
        </Link>
      </div>

      <div v-if="templates.length === 0" class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-12 text-center dark:border-gray-700 dark:bg-gray-900">
        <p class="text-gray-600 dark:text-gray-400">No templates yet. Create your first invoice template to get started.</p>
        <Link :href="route('invoice-templates.create')" class="mt-4 inline-block">
          <Button>Create Your First Template</Button>
        </Link>
      </div>

      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="template in templates"
          :key="template.id"
          class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
        >
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ template.name }}</h3>
            <p v-if="template.description" class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-400">
              {{ template.description }}
            </p>
            <div class="mt-4 flex gap-2">
              <Link :href="route('invoice-templates.edit', template.id)" class="flex-1">
                <Button variant="outline" class="w-full">Edit</Button>
              </Link>
              <Link :href="route('invoice-templates.preview', template.id)" class="flex-1">
                <Button variant="outline" class="w-full">Preview</Button>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

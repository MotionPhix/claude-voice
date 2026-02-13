<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  name: '',
  description: '',
})

const submit = () => {
  form.post(route('invoice-templates.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Create Invoice Template" />

    <div class="mx-auto max-w-2xl">
      <h1 class="mb-8 text-3xl font-bold text-gray-900 dark:text-white">Create Invoice Template</h1>

      <form @submit.prevent="submit" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <FieldGroup>
          <Field>
            <FieldLabel for="name">Template Name</FieldLabel>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="e.g., Standard Invoice"
              required
            />
          </Field>
        </FieldGroup>

        <FieldGroup>
          <Field>
            <FieldLabel for="description">Description</FieldLabel>
            <Textarea
              id="description"
              v-model="form.description"
              placeholder="Describe the purpose of this template..."
            />
          </Field>
        </FieldGroup>

        <div class="flex gap-3">
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create Template' }}
          </Button>
          <Button type="button" variant="outline" @click="$router.back()">Cancel</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

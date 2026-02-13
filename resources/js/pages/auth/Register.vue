<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Form, Head } from '@inertiajs/vue3'
</script>

<template>
  <AuthBase
    title="Create an account"
    description="Enter your details to create your account"
  >
    <Head title="Register" />

    <Form
      method="post"
      :action="route('register')"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <FieldGroup>

        <Field>
          <FieldLabel for="name">Name</FieldLabel>
          <Input id="name" name="name" required autofocus />
          <InputError :message="errors.name" />
        </Field>

        <Field>
          <FieldLabel for="phone">Phone</FieldLabel>
          <Input id="phone" name="phone" required placeholder="+265..." />
          <InputError :message="errors.phone" />
        </Field>

        <Field>
          <FieldLabel for="email">Email</FieldLabel>
          <Input id="email" type="email" name="email" required />
          <InputError :message="errors.email" />
        </Field>

        <Button type="submit" class="w-full" :disabled="processing">
          <Spinner v-if="processing" />
          Continue
        </Button>

      </FieldGroup>

      <div class="text-sm text-center text-muted-foreground">
        Already have an account?
        <TextLink :href="route('login')" class="underline underline-offset-4">
          Log in
        </TextLink>
      </div>
    </Form>
  </AuthBase>
</template>

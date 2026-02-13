<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Form, Head } from '@inertiajs/vue3'
</script>

<template>
  <AuthBase
    title="Sign in"
    description="Enter your phone number to receive a login code">
    <Head title="Login" />

    <Form
      method="post"
      :action="route('send.otp')"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <FieldGroup>
        <Field>
          <FieldLabel for="phone">Phone Number</FieldLabel>
          <Input
            id="phone"
            type="text"
            name="phone"
            placeholder="+265..."
            required
            autofocus
          />

          <InputError :message="errors.phone" />
        </Field>

        <Button type="submit" class="w-full" :disabled="processing">
          <Spinner v-if="processing" />
          Send Login Code
        </Button>
      </FieldGroup>
    </Form>
  </AuthBase>
</template>

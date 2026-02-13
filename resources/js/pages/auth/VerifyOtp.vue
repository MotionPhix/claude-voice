<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Form, Head } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

const cooldown = ref(60)

onMounted(() => {
  const interval = setInterval(() => {
    if (cooldown.value > 0) cooldown.value--
    else clearInterval(interval)
  }, 1000)
})
</script>

<template>
  <AuthBase
    title="Verify Code"
    description="Enter the 6-digit code sent to your phone"
  >
    <Head title="Verify OTP" />

    <Form
      method="post"
      :action="route('verify.otp')"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <input type="hidden" name="token" :value="$page.props.token" />

      <FieldGroup>
        <Field>
          <FieldLabel for="otp">One-Time Code</FieldLabel>
          <Input
            id="otp"
            type="text"
            name="otp"
            maxlength="6"
            inputmode="numeric"
            pattern="[0-9]*"
            required
            autofocus
          />
          <InputError :message="errors.otp" />
        </Field>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="remember_device" id="remember_device" />
          <Label for="remember_device">
            Remember this device for 30 days
          </Label>
        </div>

        <Button type="submit" class="w-full" :disabled="processing">
          <Spinner v-if="processing" />
          Verify & Sign In
        </Button>

        <div class="text-center text-sm text-muted-foreground">
          Resend available in {{ cooldown }}s
        </div>
      </FieldGroup>
    </Form>
  </AuthBase>
</template>

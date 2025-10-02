<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Building, Mail, Phone, MapPin, Save } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form } from '@inertiajs/vue3';

interface Props {
  errors?: Record<string, string>;
}

const props = defineProps<Props>();

const form = Form({
  name: '',
  email: '',
  phone: '',
  company: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  notes: ''
});

const submit = () => {
  form.post(route('clients.store'), {
    onSuccess: () => {
      router.visit(route('clients.index'));
    }
  });
};
</script>

<template>
  <Head title="Create Client" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="route('clients.index')">
          <Button variant="outline" size="sm">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back to Clients
          </Button>
        </Link>
        <div>
          <h1 class="text-4xl font-bold tracking-tight">Create New Client</h1>
          <p class="text-muted-foreground">
            Add a new client to your organization
          </p>
        </div>
      </div>

      <!-- Client Form -->
      <div class="max-w-4xl">
        <Form @submit="submit">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Basic Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Building class="h-5 w-5" />
                  Basic Information
                </CardTitle>
                <CardDescription>
                  Primary client details and contact information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="name">Client Name *</Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Enter client name"
                    :class="{ 'border-red-500': errors?.name }"
                    required
                  />
                  <p v-if="errors?.name" class="text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="company">Company</Label>
                  <Input
                    id="company"
                    v-model="form.company"
                    type="text"
                    placeholder="Company name (optional)"
                    :class="{ 'border-red-500': errors?.company }"
                  />
                  <p v-if="errors?.company" class="text-sm text-red-600">{{ errors.company }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="email">Email Address *</Label>
                  <div class="relative">
                    <Mail class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="email"
                      v-model="form.email"
                      type="email"
                      placeholder="client@example.com"
                      class="pl-10"
                      :class="{ 'border-red-500': errors?.email }"
                      required
                    />
                  </div>
                  <p v-if="errors?.email" class="text-sm text-red-600">{{ errors.email }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="phone">Phone Number</Label>
                  <div class="relative">
                    <Phone class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="phone"
                      v-model="form.phone"
                      type="tel"
                      placeholder="+1 (555) 123-4567"
                      class="pl-10"
                      :class="{ 'border-red-500': errors?.phone }"
                    />
                  </div>
                  <p v-if="errors?.phone" class="text-sm text-red-600">{{ errors.phone }}</p>
                </div>
              </CardContent>
            </Card>

            <!-- Address Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <MapPin class="h-5 w-5" />
                  Address Information
                </CardTitle>
                <CardDescription>
                  Billing and contact address details
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-2">
                  <Label for="address">Street Address</Label>
                  <Textarea
                    id="address"
                    v-model="form.address"
                    placeholder="Enter street address"
                    :class="{ 'border-red-500': errors?.address }"
                    rows="2"
                  />
                  <p v-if="errors?.address" class="text-sm text-red-600">{{ errors.address }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label for="city">City</Label>
                    <Input
                      id="city"
                      v-model="form.city"
                      type="text"
                      placeholder="City"
                      :class="{ 'border-red-500': errors?.city }"
                    />
                    <p v-if="errors?.city" class="text-sm text-red-600">{{ errors.city }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="state">State/Province</Label>
                    <Input
                      id="state"
                      v-model="form.state"
                      type="text"
                      placeholder="State"
                      :class="{ 'border-red-500': errors?.state }"
                    />
                    <p v-if="errors?.state" class="text-sm text-red-600">{{ errors.state }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label for="postal_code">Postal Code</Label>
                    <Input
                      id="postal_code"
                      v-model="form.postal_code"
                      type="text"
                      placeholder="12345"
                      :class="{ 'border-red-500': errors?.postal_code }"
                    />
                    <p v-if="errors?.postal_code" class="text-sm text-red-600">{{ errors.postal_code }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="country">Country</Label>
                    <Input
                      id="country"
                      v-model="form.country"
                      type="text"
                      placeholder="United States"
                      :class="{ 'border-red-500': errors?.country }"
                    />
                    <p v-if="errors?.country" class="text-sm text-red-600">{{ errors.country }}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Additional Notes -->
          <Card class="mt-8">
            <CardHeader>
              <CardTitle>Additional Notes</CardTitle>
              <CardDescription>
                Any additional information or special instructions for this client
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <Label for="notes">Notes</Label>
                <Textarea
                  id="notes"
                  v-model="form.notes"
                  placeholder="Enter any additional notes about this client..."
                  :class="{ 'border-red-500': errors?.notes }"
                  rows="4"
                />
                <p v-if="errors?.notes" class="text-sm text-red-600">{{ errors.notes }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Form Actions -->
          <div class="flex items-center justify-end gap-4 mt-8">
            <Link :href="route('clients.index')">
              <Button variant="outline" type="button">
                Cancel
              </Button>
            </Link>
            <Button type="submit" :disabled="form.processing">
              <Save class="h-4 w-4 mr-2" />
              {{ form.processing ? 'Creating...' : 'Create Client' }}
            </Button>
          </div>
        </Form>
      </div>
    </div>
  </AppLayout>
</template>
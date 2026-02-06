<template>
  <Head title="Subscription" />

  <SettingsLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription</h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Manage your subscription plan and billing preferences
      </p>
    </div>

    <!-- Current Plan Section -->
    <SettingsSection
      title="Current Plan"
      description="Your active subscription details"
    >
      <div class="rounded-lg border-2 border-indigo-200 dark:border-indigo-800 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950 dark:to-purple-950 p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <Zap class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
              <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ currentPlan.name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ currentPlan.description }}
                </p>
              </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
              <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                ${{ currentPlan.price }}
              </span>
              <span class="text-gray-600 dark:text-gray-400">/month</span>
            </div>
          </div>
          <Badge variant="default" class="text-sm">Active</Badge>
        </div>
      </div>

      <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-2">
            <FileText class="h-4 w-4" />
            <span class="text-sm">Invoices</span>
          </div>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            {{ usage.invoices }} / {{ currentPlan.limits.invoices === -1 ? '∞' : currentPlan.limits.invoices }}
          </p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-2">
            <Users class="h-4 w-4" />
            <span class="text-sm">Team Members</span>
          </div>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            {{ usage.members }} / {{ currentPlan.limits.members === -1 ? '∞' : currentPlan.limits.members }}
          </p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-2">
            <Building2 class="h-4 w-4" />
            <span class="text-sm">Organizations</span>
          </div>
          <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            {{ usage.organizations }} / {{ currentPlan.limits.organizations === -1 ? '∞' : currentPlan.limits.organizations }}
          </p>
        </div>
      </div>
    </SettingsSection>

    <!-- Available Plans Section -->
    <SettingsSection
      title="Available Plans"
      description="Choose the plan that fits your needs"
    >
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="plan in availablePlans"
          :key="plan.id"
          :class="[
            'rounded-lg border-2 p-6 transition-all',
            plan.id === currentPlan.id
              ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950'
              : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
          ]"
        >
          <div class="text-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ plan.name }}</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ plan.description }}</p>
            <div class="mt-4">
              <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">${{ plan.price }}</span>
              <span class="text-gray-600 dark:text-gray-400">/month</span>
            </div>
          </div>

          <ul class="mt-6 space-y-3">
            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2 text-sm">
              <Check class="h-4 w-4 text-green-600 dark:text-green-400 flex-shrink-0" />
              <span class="text-gray-600 dark:text-gray-400">{{ feature }}</span>
            </li>
          </ul>

          <Button
            v-if="plan.id !== currentPlan.id"
            @click="changePlan(plan.id)"
            class="w-full mt-6"
            variant="outline"
          >
            {{ plan.price > currentPlan.price ? 'Upgrade' : 'Downgrade' }}
          </Button>
          <Button v-else disabled class="w-full mt-6">
            Current Plan
          </Button>
        </div>
      </div>
    </SettingsSection>

    <!-- Billing Cycle Section -->
    <SettingsSection
      title="Billing Cycle"
      description="Manage how often you're billed"
    >
      <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <div>
          <p class="font-medium text-gray-900 dark:text-gray-100">Current Billing Cycle</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Monthly billing</p>
        </div>
        <Button variant="outline" disabled>
          Switch to Annual (Save 20%)
        </Button>
      </div>
    </SettingsSection>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/SettingsLayout.vue'
import SettingsSection from '@/components/settings/SettingsSection.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Zap, FileText, Users, Building2, Check } from 'lucide-vue-next'

interface Plan {
  id: string
  name: string
  description: string
  price: number
  features: string[]
  limits: {
    invoices: number
    members: number
    organizations: number
  }
}

interface Props {
  currentPlan: Plan
  availablePlans: Plan[]
  usage: {
    invoices: number
    members: number
    organizations: number
  }
}

defineProps<Props>()

const changePlan = (planId: string) => {
  if (confirm('Are you sure you want to change your plan?')) {
    router.post(route('settings.billing.subscription.change'), {
      plan_id: planId,
    })
  }
}
</script>

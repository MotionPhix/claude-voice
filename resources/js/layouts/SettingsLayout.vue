<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Settings</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage your account, organization, and billing preferences
          </p>
        </div>

        <!-- Settings Container -->
        <div class="flex gap-6">
          <!-- Sidebar Navigation -->
          <aside class="w-64 flex-shrink-0">
            <nav class="space-y-1">
              <!-- Account Section -->
              <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                  Account
                </h3>
                <Link
                  v-for="item in accountItems"
                  :key="item.name"
                  :href="item.href"
                  :class="[
                    isActive(item.href)
                      ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100'
                      : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100',
                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors'
                  ]"
                >
                  <component
                    :is="item.icon"
                    :class="[
                      isActive(item.href)
                        ? 'text-gray-500 dark:text-gray-400'
                        : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400',
                      'mr-3 flex-shrink-0 h-5 w-5'
                    ]"
                  />
                  {{ item.name }}
                </Link>
              </div>

              <!-- Organization Section -->
              <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                  Organization
                </h3>
                <Link
                  v-for="item in organizationItems"
                  :key="item.name"
                  :href="item.href"
                  :class="[
                    isActive(item.href)
                      ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100'
                      : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100',
                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors'
                  ]"
                >
                  <component
                    :is="item.icon"
                    :class="[
                      isActive(item.href)
                        ? 'text-gray-500 dark:text-gray-400'
                        : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400',
                      'mr-3 flex-shrink-0 h-5 w-5'
                    ]"
                  />
                  {{ item.name }}
                </Link>
              </div>

              <!-- Billing Section -->
              <div class="mb-6">
                <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                  Billing
                </h3>
                <Link
                  v-for="item in billingItems"
                  :key="item.name"
                  :href="item.href"
                  :class="[
                    isActive(item.href)
                      ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100'
                      : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100',
                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors'
                  ]"
                >
                  <component
                    :is="item.icon"
                    :class="[
                      isActive(item.href)
                        ? 'text-gray-500 dark:text-gray-400'
                        : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400',
                      'mr-3 flex-shrink-0 h-5 w-5'
                    ]"
                  />
                  {{ item.name }}
                </Link>
              </div>
            </nav>
          </aside>

          <!-- Main Content Area -->
          <main class="flex-1 min-w-0">
            <slot />
          </main>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  User,
  Lock,
  Bell,
  Building2,
  Users,
  Paintbrush,
  Plug,
  CreditCard,
  Receipt,
  Wallet,
  DollarSign
} from 'lucide-vue-next'

const page = usePage()

const accountItems = [
  { name: 'Profile', href: route('settings.account.profile'), icon: User },
  { name: 'Security', href: route('settings.account.security'), icon: Lock },
  { name: 'Notifications', href: route('settings.account.notifications'), icon: Bell },
]

const organizationItems = [
  { name: 'General', href: route('settings.organization.general'), icon: Building2 },
  { name: 'Team', href: route('settings.organization.team'), icon: Users },
  { name: 'Branding', href: route('settings.organization.branding'), icon: Paintbrush },
  { name: 'Integrations', href: route('settings.organization.integrations'), icon: Plug },
  { name: 'Currencies', href: route('settings.organization.currencies'), icon: DollarSign },
]

const billingItems = [
  { name: 'Subscription', href: route('settings.billing.subscription'), icon: CreditCard },
  { name: 'Payment Methods', href: route('settings.billing.payment-methods'), icon: Wallet },
  { name: 'Invoices', href: route('settings.billing.invoices'), icon: Receipt },
]

const isActive = (href: string) => {
  return page.url.startsWith(href)
}
</script>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  TrendingUp,
  TrendingDown,
  FileText,
  Users,
  DollarSign,
  Clock,
  AlertTriangle,
  Plus,
  ArrowRight,
  Calendar,
  Eye,
  Send,
  BarChart3,
  PieChart,
  Activity,
  Shield,
  Settings,
  UserPlus,
  Building
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import RoleBadge from '@/components/ui/role-badge.vue';
import { usePermissions } from '@/composables/usePermissions';

interface AdminDashboardStats {
  total_invoices: number;
  total_revenue: number;
  pending_amount: number;
  overdue_amount: number;
  draft_count: number;
  paid_count: number;
  overdue_count: number;
  clients_count: number;
  revenue_growth: number;
  invoice_growth: number;
  members_count: number;
  active_members: number;
  storage_used: number;
  api_calls_today: number;
}

interface Props {
  stats: AdminDashboardStats;
  recentInvoices: any[];
  recentActivity: any[];
  systemAlerts: any[];
  organizationMetrics: any;
}

const props = defineProps<Props>();

const {
  canCreateInvoices,
  canCreateClients,
  canViewReports,
  canManageMembers,
  canManageOrganization,
  isOwner,
  isAdmin
} = usePermissions();

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const formatPercentage = (value: number) => {
  return `${value > 0 ? '+' : ''}${value.toFixed(1)}%`;
};

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'paid': return 'default';
    case 'sent': return 'secondary';
    case 'draft': return 'outline';
    case 'overdue': return 'destructive';
    default: return 'secondary';
  }
};
</script>

<template>
  <Head title="Admin Dashboard" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold tracking-tight">Admin Dashboard</h1>
          <p class="text-muted-foreground">
            Complete organizational overview and management tools
          </p>
        </div>
        <div class="flex items-center gap-3">
          <RoleBadge :role="isOwner ? 'owner' : 'admin'" />
          <Link v-if="canManageOrganization" :href="route('organizations.settings', $page.props.auth.currentOrganization.uuid)">
            <Button variant="outline">
              <Settings class="h-4 w-4 mr-2" />
              Organization Settings
            </Button>
          </Link>
        </div>
      </div>

      <!-- Key Performance Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card padding="md">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="text-sm font-medium">Total Revenue</h3>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </div>
          <div class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</div>
          <p class="text-xs text-muted-foreground flex items-center">
            <TrendingUp v-if="stats.revenue_growth > 0" class="h-3 w-3 mr-1 text-green-500" />
            <TrendingDown v-else class="h-3 w-3 mr-1 text-red-500" />
            {{ formatPercentage(stats.revenue_growth) }} from last month
          </p>
        </Card>

        <Card padding="md">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="text-sm font-medium">Total Invoices</h3>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </div>
          <div class="text-2xl font-bold">{{ stats.total_invoices }}</div>
          <p class="text-xs text-muted-foreground flex items-center">
            <TrendingUp v-if="stats.invoice_growth > 0" class="h-3 w-3 mr-1 text-green-500" />
            <TrendingDown v-else class="h-3 w-3 mr-1 text-red-500" />
            {{ formatPercentage(stats.invoice_growth) }} from last month
          </p>
        </Card>

        <Card padding="md">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="text-sm font-medium">Active Members</h3>
            <Users class="h-4 w-4 text-muted-foreground" />
          </div>
          <div class="text-2xl font-bold">{{ stats.active_members }}</div>
          <p class="text-xs text-muted-foreground">
            {{ stats.members_count }} total members
          </p>
        </Card>

        <Card padding="md">
          <div class="flex flex-row items-center justify-between space-y-0 pb-2">
            <h3 class="text-sm font-medium">Overdue Amount</h3>
            <AlertTriangle class="h-4 w-4 text-destructive" />
          </div>
          <div class="text-2xl font-bold">{{ formatCurrency(stats.overdue_amount) }}</div>
          <p class="text-xs text-muted-foreground">
            {{ stats.overdue_count }} overdue invoices
          </p>
        </Card>
      </div>

      <!-- Admin Quick Actions -->
      <Card>
        <template #header>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Admin Actions</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Administrative tasks and management tools</p>
        </template>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <Link v-if="canCreateInvoices" :href="route('invoices.create')">
              <Button class="w-full h-20 flex-col gap-2">
                <Plus class="h-5 w-5" />
                <span class="text-xs">Create Invoice</span>
              </Button>
            </Link>

            <Link v-if="canCreateClients" :href="route('clients.create')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Users class="h-5 w-5" />
                <span class="text-xs">Add Client</span>
              </Button>
            </Link>

            <Link v-if="canManageMembers" :href="route('organizations.members.invite', $page.props.auth.currentOrganization.uuid)">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <UserPlus class="h-5 w-5" />
                <span class="text-xs">Invite Member</span>
              </Button>
            </Link>

            <Link v-if="canViewReports" :href="route('reports.index')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <BarChart3 class="h-5 w-5" />
                <span class="text-xs">Reports</span>
              </Button>
            </Link>

            <Link v-if="canManageOrganization" :href="route('organizations.settings', $page.props.auth.currentOrganization.uuid)">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Building class="h-5 w-5" />
                <span class="text-xs">Org Settings</span>
              </Button>
            </Link>

            <Link :href="route('settings.index')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Settings class="h-5 w-5" />
                <span class="text-xs">System Settings</span>
              </Button>
            </Link>
          </div>
      </Card>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <Card>
          <template #header>
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest invoice activity</p>
              </div>
              <Link :href="route('invoices.index')">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </template>

          <div v-if="recentInvoices.length > 0" class="space-y-4">
              <div v-for="invoice in recentInvoices" :key="invoice.id" class="flex items-center justify-between p-4 border rounded-lg">
                <div class="flex items-center space-x-4">
                  <div>
                    <p class="font-medium">{{ invoice.invoice_number }}</p>
                    <p class="text-sm text-muted-foreground">{{ invoice.client.name }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(invoice.total) }}</p>
                  <Badge :variant="getStatusBadgeVariant(invoice.status)">
                    {{ invoice.status }}
                  </Badge>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <FileText class="mx-auto h-12 w-12 text-muted-foreground/50" />
              <h3 class="mt-4 text-lg font-medium">No invoices yet</h3>
              <p class="mt-1 text-sm text-gray-500">
                Get started by creating your first invoice.
              </p>
              <div class="mt-6">
                <Link v-if="canCreateInvoices" :href="route('invoices.create')">
                  <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Create Invoice
                  </Button>
                </Link>
              </div>
            </div>
        </Card>

        <!-- System Alerts & Activity -->
        <Card>
          <template #header>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">System Activity</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Recent system events and alerts</p>
          </template>

          <div v-if="recentActivity?.length > 0" class="space-y-4">
              <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <Activity class="h-4 w-4 mt-1 text-muted-foreground" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm">{{ activity.description }}</p>
                  <p class="text-xs text-muted-foreground">{{ activity.time }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12 text-muted-foreground">
              <Activity class="mx-auto h-8 w-8 mb-2" />
              <p class="text-sm">No recent activity</p>
            </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
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
  Target,
  BarChart3,
  PieChart,
  Send,
  Eye
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import RoleBadge from '@/components/ui/role-badge.vue';
import { usePermissions } from '@/composables/usePermissions';

interface ManagerDashboardStats {
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
  monthly_target: number;
  monthly_achieved: number;
}

interface Props {
  stats: ManagerDashboardStats;
  recentInvoices: any[];
  upcomingDue: any[];
  teamActivity: any[];
  clientMetrics: any;
}

const props = defineProps<Props>();

const {
  canCreateInvoices,
  canCreateClients,
  canSendInvoices,
  canViewReports,
  canManageClients,
  isManager
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

const targetProgress = computed(() => {
  return props.stats.monthly_target > 0
    ? (props.stats.monthly_achieved / props.stats.monthly_target) * 100
    : 0;
});
</script>

<template>
  <Head title="Manager Dashboard" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold tracking-tight">Manager Dashboard</h1>
          <p class="text-muted-foreground">
            Business operations and team performance overview
          </p>
        </div>
        <div class="flex items-center gap-3">
          <RoleBadge role="manager" />
          <Link v-if="canViewReports" :href="route('reports.index')">
            <Button variant="outline">
              <BarChart3 class="h-4 w-4 mr-2" />
              Business Reports
            </Button>
          </Link>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Revenue</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</div>
            <p class="text-xs text-muted-foreground flex items-center">
              <TrendingUp v-if="stats.revenue_growth > 0" class="h-3 w-3 mr-1 text-green-500" />
              <TrendingDown v-else class="h-3 w-3 mr-1 text-red-500" />
              {{ formatPercentage(stats.revenue_growth) }} from last month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Target</CardTitle>
            <Target class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ targetProgress.toFixed(0) }}%</div>
            <Progress :value="targetProgress" class="mt-2" />
            <p class="text-xs text-muted-foreground mt-1">
              {{ formatCurrency(stats.monthly_achieved) }} of {{ formatCurrency(stats.monthly_target) }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Clients</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.clients_count }}</div>
            <p class="text-xs text-muted-foreground">
              Total active clients
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending Collection</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.pending_amount) }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.overdue_count }} overdue · {{ stats.draft_count }} drafts
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Manager Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Business Operations</CardTitle>
          <CardDescription>Key management tasks and operations</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

            <Link :href="route('invoices.index', { status: 'draft' })">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Send class="h-5 w-5" />
                <span class="text-xs">Send Drafts</span>
              </Button>
            </Link>

            <Link v-if="canViewReports" :href="route('reports.clients')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <BarChart3 class="h-5 w-5" />
                <span class="text-xs">Client Reports</span>
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Recent Invoices</CardTitle>
                <CardDescription>Latest billing activity</CardDescription>
              </div>
              <Link :href="route('invoices.index')">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="recentInvoices?.length > 0" class="space-y-4">
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
          </CardContent>
        </Card>

        <!-- Upcoming Due Invoices -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Upcoming Due</CardTitle>
                <CardDescription>Invoices due in next 7 days</CardDescription>
              </div>
              <Link :href="route('invoices.index', { due_soon: 7 })">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="upcomingDue?.length > 0" class="space-y-4">
              <div v-for="invoice in upcomingDue" :key="invoice.id" class="flex items-center justify-between p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                <div class="flex items-center space-x-4">
                  <div>
                    <p class="font-medium">{{ invoice.invoice_number }}</p>
                    <p class="text-sm text-muted-foreground">{{ invoice.client.name }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(invoice.total) }}</p>
                  <p class="text-xs text-muted-foreground">Due {{ invoice.due_date }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <Calendar class="mx-auto h-12 w-12 text-muted-foreground/50" />
              <h3 class="mt-4 text-lg font-medium">No upcoming dues</h3>
              <p class="mt-1 text-sm text-gray-500">
                All invoices are current.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
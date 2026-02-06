<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  FileText,
  Eye,
  Clock,
  CheckCircle,
  Calendar,
  BarChart3,
  Download
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Badge } from '@/components/ui/badge';
import RoleBadge from '@/components/ui/role-badge.vue';
import { usePermissions } from '@/composables/usePermissions';

interface UserDashboardStats {
  total_invoices: number;
  total_revenue: number;
  paid_count: number;
  pending_count: number;
}

interface Props {
  stats: UserDashboardStats;
  recentInvoices: any[];
  accessibleReports: any[];
}

const props = defineProps<Props>();

const {
  canViewInvoices,
  canViewReports,
  canDownloadPdfs,
  isUser
} = usePermissions();

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
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
  <Head title="Dashboard" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold tracking-tight">Dashboard</h1>
          <p class="text-muted-foreground">
            Your invoice overview and accessible information
          </p>
        </div>
        <div class="flex items-center gap-3">
          <RoleBadge role="user" />
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Invoices</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_invoices }}</div>
            <p class="text-xs text-muted-foreground">
              Invoices you can view
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Value</CardTitle>
            <BarChart3 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</div>
            <p class="text-xs text-muted-foreground">
              Total invoice value
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Paid Invoices</CardTitle>
            <CheckCircle class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.paid_count }}</div>
            <p class="text-xs text-muted-foreground">
              Successfully completed
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.pending_count }}</div>
            <p class="text-xs text-muted-foreground">
              Awaiting payment
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions for Users -->
      <Card>
        <CardHeader>
          <CardTitle>Available Actions</CardTitle>
          <CardDescription>Tasks you can perform</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <Link v-if="canViewInvoices" :href="route('invoices.index')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Eye class="h-5 w-5" />
                <span class="text-xs">View Invoices</span>
              </Button>
            </Link>

            <Link v-if="canViewReports" :href="route('reports.index')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <BarChart3 class="h-5 w-5" />
                <span class="text-xs">View Reports</span>
              </Button>
            </Link>

            <Link :href="route('settings.profile')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Calendar class="h-5 w-5" />
                <span class="text-xs">Profile Settings</span>
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Invoices -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Recent Invoices</CardTitle>
              <CardDescription>Latest invoices you can access</CardDescription>
            </div>
            <Link v-if="canViewInvoices" :href="route('invoices.index')">
              <Button variant="ghost" size="sm">
                View all
                <Eye class="ml-2 h-4 w-4" />
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
            <h3 class="mt-4 text-lg font-medium">No invoices available</h3>
            <p class="mt-1 text-sm text-gray-500">
              You don't have access to view any invoices yet.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
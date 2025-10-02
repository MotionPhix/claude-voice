<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  TrendingUp,
  TrendingDown,
  FileText,
  DollarSign,
  Clock,
  AlertTriangle,
  Plus,
  ArrowRight,
  Calculator,
  Receipt,
  PieChart,
  BarChart3,
  CheckCircle,
  XCircle
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import RoleBadge from '@/components/ui/role-badge.vue';
import { usePermissions } from '@/composables/usePermissions';

interface AccountantDashboardStats {
  total_revenue: number;
  pending_amount: number;
  overdue_amount: number;
  paid_amount: number;
  draft_count: number;
  paid_count: number;
  overdue_count: number;
  revenue_growth: number;
  pending_payments: number;
  processed_payments: number;
  payment_variance: number;
}

interface Props {
  stats: AccountantDashboardStats;
  recentInvoices: any[];
  recentPayments: any[];
  paymentSummary: any;
  overdueInvoices: any[];
}

const props = defineProps<Props>();

const {
  canCreateInvoices,
  canMarkInvoicesPaid,
  canViewReports,
  canRecordPayments,
  isAccountant
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

const collectionRate = computed(() => {
  const total = props.stats.paid_amount + props.stats.pending_amount + props.stats.overdue_amount;
  return total > 0 ? (props.stats.paid_amount / total) * 100 : 0;
});
</script>

<template>
  <Head title="Accountant Dashboard" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold tracking-tight">Accountant Dashboard</h1>
          <p class="text-muted-foreground">
            Financial overview and payment management
          </p>
        </div>
        <div class="flex items-center gap-3">
          <RoleBadge role="accountant" />
          <Link v-if="canViewReports" :href="route('reports.index')">
            <Button variant="outline">
              <BarChart3 class="h-4 w-4 mr-2" />
              Financial Reports
            </Button>
          </Link>
        </div>
      </div>

      <!-- Financial Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
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
            <CardTitle class="text-sm font-medium">Pending Payments</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.pending_amount) }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.pending_payments }} invoices pending
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Overdue Amount</CardTitle>
            <AlertTriangle class="h-4 w-4 text-destructive" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.overdue_amount) }}</div>
            <p class="text-xs text-muted-foreground">
              {{ stats.overdue_count }} overdue invoices
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Collection Rate</CardTitle>
            <CheckCircle class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ collectionRate.toFixed(1) }}%</div>
            <Progress :value="collectionRate" class="mt-2" />
          </CardContent>
        </Card>
      </div>

      <!-- Accountant Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Accounting Actions</CardTitle>
          <CardDescription>Payment processing and financial management</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link :href="route('invoices.index', { status: 'sent' })">
              <Button class="w-full h-20 flex-col gap-2">
                <Receipt class="h-5 w-5" />
                <span class="text-xs">Process Payments</span>
              </Button>
            </Link>

            <Link :href="route('invoices.index', { status: 'overdue' })">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <AlertTriangle class="h-5 w-5" />
                <span class="text-xs">Review Overdue</span>
              </Button>
            </Link>

            <Link v-if="canViewReports" :href="route('reports.revenue')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <BarChart3 class="h-5 w-5" />
                <span class="text-xs">Revenue Report</span>
              </Button>
            </Link>

            <Link v-if="canViewReports" :href="route('reports.payments')">
              <Button variant="outline" class="w-full h-20 flex-col gap-2">
                <Calculator class="h-5 w-5" />
                <span class="text-xs">Payment Report</span>
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Payments -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Recent Payments</CardTitle>
                <CardDescription>Latest payment transactions</CardDescription>
              </div>
              <Link :href="route('reports.payments')">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="recentPayments?.length > 0" class="space-y-4">
              <div v-for="payment in recentPayments" :key="payment.id" class="flex items-center justify-between p-4 border rounded-lg">
                <div class="flex items-center space-x-4">
                  <div>
                    <p class="font-medium">{{ payment.invoice_number }}</p>
                    <p class="text-sm text-muted-foreground">{{ payment.client_name }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(payment.amount) }}</p>
                  <p class="text-xs text-muted-foreground">{{ payment.payment_date }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <Receipt class="mx-auto h-12 w-12 text-muted-foreground/50" />
              <h3 class="mt-4 text-lg font-medium">No payments yet</h3>
              <p class="mt-1 text-sm text-gray-500">
                Payments will appear here once processed.
              </p>
            </div>
          </CardContent>
        </Card>

        <!-- Overdue Invoices -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Overdue Invoices</CardTitle>
                <CardDescription>Invoices requiring attention</CardDescription>
              </div>
              <Link :href="route('invoices.index', { status: 'overdue' })">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="overdueInvoices?.length > 0" class="space-y-4">
              <div v-for="invoice in overdueInvoices" :key="invoice.id" class="flex items-center justify-between p-4 border border-red-200 rounded-lg bg-red-50">
                <div class="flex items-center space-x-4">
                  <div>
                    <p class="font-medium">{{ invoice.invoice_number }}</p>
                    <p class="text-sm text-muted-foreground">{{ invoice.client.name }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium text-red-600">{{ formatCurrency(invoice.total) }}</p>
                  <Badge variant="destructive">
                    {{ invoice.days_overdue }} days overdue
                  </Badge>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <CheckCircle class="mx-auto h-12 w-12 text-green-500/50" />
              <h3 class="mt-4 text-lg font-medium">No overdue invoices</h3>
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
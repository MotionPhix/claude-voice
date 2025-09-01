<script setup lang="ts">
import { computed, withDefaults } from 'vue';
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
  Activity
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';

interface DashboardStats {
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
}

interface RecentInvoice {
  id: number;
  invoice_number: string;
  client: {
    name: string;
  };
  total: number;
  status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
  due_date: string;
  created_at: string;
}

interface UpcomingInvoice {
  id: number;
  invoice_number: string;
  client: {
    name: string;
  };
  total: number;
  due_date: string;
  days_until_due: number;
}

interface Props {
  stats?: DashboardStats;
  recent_invoices?: RecentInvoice[];
  upcoming_due?: UpcomingInvoice[];
  monthly_revenue?: Array<{
    month: string;
    revenue: number;
  }>;
}

const props = withDefaults(defineProps<Props>(), {
  stats: () => ({
    total_invoices: 156,
    total_revenue: 89750,
    pending_amount: 15200,
    overdue_amount: 3400,
    draft_count: 8,
    paid_count: 142,
    overdue_count: 6,
    clients_count: 34,
    revenue_growth: 12.5,
    invoice_growth: 8.3
  }),
  recent_invoices: () => [
    {
      id: 1,
      invoice_number: 'INV-2024-001',
      client: { name: 'Acme Corporation' },
      total: 2500,
      status: 'paid',
      due_date: '2024-01-15',
      created_at: '2024-01-01'
    },
    {
      id: 2,
      invoice_number: 'INV-2024-002',
      client: { name: 'Tech Solutions Inc' },
      total: 1800,
      status: 'sent',
      due_date: '2024-01-20',
      created_at: '2024-01-05'
    }
  ],
  upcoming_due: () => [
    {
      id: 3,
      invoice_number: 'INV-2024-003',
      client: { name: 'Digital Agency' },
      total: 3200,
      due_date: '2024-02-01',
      days_until_due: 3
    }
  ],
  monthly_revenue: () => [
    { month: 'Jan', revenue: 65000 },
    { month: 'Feb', revenue: 72000 },
    { month: 'Mar', revenue: 68000 },
    { month: 'Apr', revenue: 78000 },
    { month: 'May', revenue: 85000 },
    { month: 'Jun', revenue: 89750 }
  ]
});

// Format currency
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

// Format percentage
const formatPercentage = (value: number): string => {
  return `${value >= 0 ? '+' : ''}${value.toFixed(1)}%`;
};

// Status colors
const getStatusColor = (status: string) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-600'
  };
  return colors[status] || colors.draft;
};

// Calculate collection rate
const collectionRate = computed(() => {
  const total = (props.stats?.total_revenue || 0) + (props.stats?.pending_amount || 0);
  if (total === 0) return 0;
  return ((props.stats?.total_revenue || 0) / total) * 100;
});

// Get overdue urgency
const getUrgencyColor = (daysOverdue: number) => {
  if (daysOverdue <= 0) return 'text-green-600';
  if (daysOverdue <= 7) return 'text-yellow-600';
  if (daysOverdue <= 30) return 'text-orange-600';
  return 'text-red-600';
};

// Format relative time
const formatRelativeTime = (date: string) => {
  const now = new Date();
  const targetDate = new Date(date);
  const diffTime = targetDate.getTime() - now.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Tomorrow';
  if (diffDays === -1) return 'Yesterday';
  if (diffDays > 1) return `In ${diffDays} days`;
  return `${Math.abs(diffDays)} days ago`;
};

// Chart data and options for ApexCharts
const revenueChartOptions = computed(() => ({
  chart: {
    type: 'area',
    height: 350,
    toolbar: {
      show: false
    },
    zoom: {
      enabled: false
    },
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  grid: {
    show: true,
    borderColor: '#e5e7eb',
    strokeDashArray: 3,
    opacity: 0.4
  },
  colors: ['#3b82f6'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.1,
      stops: [0, 90, 100]
    }
  },
  xaxis: {
    categories: props.monthly_revenue?.map(item => item.month) || [],
    labels: {
      style: {
        colors: '#6b7280',
        fontSize: '12px'
      }
    },
    axisBorder: {
      show: false
    },
    axisTicks: {
      show: false
    }
  },
  yaxis: {
    labels: {
      formatter: (value: number) => `${(value / 1000).toFixed(0)}k`,
      style: {
        colors: '#6b7280',
        fontSize: '12px'
      }
    },
    min: 0
  },
  tooltip: {
    theme: 'light',
    y: {
      formatter: (value: number) => `${value.toLocaleString()}`
    },
    style: {
      fontSize: '12px'
    }
  },
  responsive: [{
    breakpoint: 640,
    options: {
      chart: {
        height: 250
      }
    }
  }]
}));

const revenueChartSeries = computed(() => [{
  name: 'Revenue',
  data: props.monthly_revenue?.map(item => item.revenue) || []
}]);

// Invoice status breakdown for donut chart
const invoiceStatusData = computed(() => [
  { label: 'Paid', value: props.stats?.paid_count || 0, color: '#10b981' },
  { label: 'Pending', value: (props.stats?.total_invoices || 0) - (props.stats?.paid_count || 0) - (props.stats?.draft_count || 0), color: '#f59e0b' },
  { label: 'Draft', value: props.stats?.draft_count || 0, color: '#6b7280' },
  { label: 'Overdue', value: props.stats?.overdue_count || 0, color: '#ef4444' }
]);

const donutChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    height: 300,
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800
    }
  },
  colors: invoiceStatusData.value.map(item => item.color),
  labels: invoiceStatusData.value.map(item => item.label),
  dataLabels: {
    enabled: true,
    formatter: (val: number, opts: any) => {
      const value = invoiceStatusData.value[opts.seriesIndex].value;
      return `${value}`;
    },
    style: {
      fontSize: '12px',
      fontWeight: '600',
      colors: ['#ffffff']
    },
    dropShadow: {
      enabled: false
    }
  },
  legend: {
    position: 'bottom',
    horizontalAlign: 'center',
    fontSize: '12px',
    fontWeight: '500',
    markers: {
      width: 8,
      height: 8,
      radius: 4
    },
    itemMargin: {
      horizontal: 12,
      vertical: 4
    }
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: '14px',
            fontWeight: '600',
            offsetY: -5
          },
          value: {
            show: true,
            fontSize: '20px',
            fontWeight: '700',
            offsetY: 5,
            formatter: (val: string) => val
          },
          total: {
            show: true,
            showAlways: true,
            label: 'Total',
            fontSize: '12px',
            fontWeight: '500',
            color: '#6b7280',
            formatter: () => `${props.stats?.total_invoices || 0}`
          }
        }
      }
    }
  },
  stroke: {
    width: 2,
    colors: ['#ffffff']
  },
  tooltip: {
    theme: 'light',
    y: {
      formatter: (val: number) => `${val} invoices`
    },
    style: {
      fontSize: '12px'
    }
  },
  responsive: [{
    breakpoint: 640,
    options: {
      chart: {
        height: 250
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
}));

const donutChartSeries = computed(() => invoiceStatusData.value.map(item => item.value));

// Collection Rate Chart Options
const collectionChartOptions = computed(() => ({
  chart: {
    type: 'radialBar',
    height: 200,
    toolbar: {
      show: false
    }
  },
  plotOptions: {
    radialBar: {
      startAngle: -135,
      endAngle: 135,
      hollow: {
        size: '60%'
      },
      track: {
        background: '#f3f4f6',
        strokeWidth: '100%'
      },
      dataLabels: {
        name: {
          show: false
        },
        value: {
          show: true,
          fontSize: '24px',
          fontWeight: '700',
          color: '#1f2937',
          offsetY: 8,
          formatter: (val: number) => `${val.toFixed(1)}%`
        }
      }
    }
  },
  colors: ['#10b981'],
  stroke: {
    lineCap: 'round'
  }
}));

const collectionChartSeries = computed(() => [collectionRate.value]);


</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Good morning! 👋
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Here's what's happening with your business today.
        </p>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Revenue -->
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                  Total Revenue
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats?.total_revenue || 0) }}
                </p>
                <div class="flex items-center mt-1">
                  <TrendingUp
                    v-if="(stats?.revenue_growth || 0) >= 0"
                    class="h-4 w-4 text-green-600 mr-1"
                  />
                  <TrendingDown
                    v-else
                    class="h-4 w-4 text-red-600 mr-1"
                  />
                  <span
                    :class="(stats?.revenue_growth || 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                    class="text-sm font-medium"
                  >
                    {{ formatPercentage(stats?.revenue_growth || 0) }}
                  </span>
                  <span class="text-sm text-gray-500 ml-1">vs last month</span>
                </div>
              </div>
              <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-full">
                <DollarSign class="h-6 w-6 text-green-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Total Invoices -->
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                  Total Invoices
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ (stats?.total_invoices || 0).toLocaleString() }}
                </p>
                <div class="flex items-center mt-1">
                  <TrendingUp
                    v-if="(stats?.invoice_growth || 0) >= 0"
                    class="h-4 w-4 text-blue-600 mr-1"
                  />
                  <TrendingDown
                    v-else
                    class="h-4 w-4 text-red-600 mr-1"
                  />
                  <span
                    :class="(stats?.invoice_growth || 0) >= 0 ? 'text-blue-600' : 'text-red-600'"
                    class="text-sm font-medium"
                  >
                    {{ formatPercentage(stats?.invoice_growth || 0) }}
                  </span>
                  <span class="text-sm text-gray-500 ml-1">vs last month</span>
                </div>
              </div>
              <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                <FileText class="h-6 w-6 text-blue-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Pending Amount -->
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                  Pending Amount
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats?.pending_amount || 0) }}
                </p>
                <div class="flex items-center mt-1">
                  <Clock class="h-4 w-4 text-yellow-600 mr-1" />
                  <span class="text-sm text-gray-500">
                    {{ (stats?.total_invoices || 0) - (stats?.paid_count || 0) - (stats?.draft_count || 0) }} invoices
                  </span>
                </div>
              </div>
              <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-full">
                <Clock class="h-6 w-6 text-yellow-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Overdue Amount -->
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                  Overdue Amount
                </p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats?.overdue_amount || 0) }}
                </p>
                <div class="flex items-center mt-1">
                  <AlertTriangle class="h-4 w-4 text-red-600 mr-1" />
                  <span class="text-sm text-gray-500">
                    {{ stats?.overdue_count || 0 }} invoices overdue
                  </span>
                </div>
              </div>
              <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-full">
                <AlertTriangle class="h-6 w-6 text-red-600" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Revenue Chart -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle class="flex items-center gap-2">
                  <BarChart3 class="h-5 w-5" />
                  Revenue Trend
                </CardTitle>
                <CardDescription>Monthly revenue over time</CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <apexchart
              type="area"
              :options="revenueChartOptions"
              :series="revenueChartSeries"
              height="350"
            />
          </CardContent>
        </Card>

        <!-- Invoice Status Breakdown -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <PieChart class="h-5 w-5" />
              Invoice Status
            </CardTitle>
            <CardDescription>Breakdown by status</CardDescription>
          </CardHeader>
          <CardContent>
            <apexchart
              type="donut"
              :options="donutChartOptions"
              :series="donutChartSeries"
              height="300"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Collection Rate & Quick Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Collection Rate -->
        <Card class="lg:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Activity class="h-5 w-5" />
              Collection Rate
            </CardTitle>
            <CardDescription>Percentage of invoices paid</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="text-center">
              <apexchart
                type="radialBar"
                :options="collectionChartOptions"
                :series="collectionChartSeries"
                height="200"
              />
              <p class="text-sm text-gray-500 -mt-4">
                {{ stats?.paid_count || 0 }} of {{ stats?.total_invoices || 0 }} invoices paid
              </p>
            </div>
          </CardContent>
        </Card>

        <!-- Quick Actions -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>Common tasks and shortcuts</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <Link href="/invoices/create">
                <Button class="w-full h-20 flex-col gap-2">
                  <Plus class="h-5 w-5" />
                  <span class="text-xs">Create Invoice</span>
                </Button>
              </Link>
              <Link href="/clients/create">
                <Button variant="outline" class="w-full h-20 flex-col gap-2">
                  <Users class="h-5 w-5" />
                  <span class="text-xs">Add Client</span>
                </Button>
              </Link>
              <Link href="/invoices?status=overdue">
                <Button variant="outline" class="w-full h-20 flex-col gap-2">
                  <AlertTriangle class="h-5 w-5" />
                  <span class="text-xs">View Overdue</span>
                </Button>
              </Link>
              <Link href="/reports">
                <Button variant="outline" class="w-full h-20 flex-col gap-2">
                  <FileText class="h-5 w-5" />
                  <span class="text-xs">Reports</span>
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Activity & Upcoming Due -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Recent Invoices</CardTitle>
                <CardDescription>Latest invoice activity</CardDescription>
              </div>
              <Link href="/invoices">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="invoice in recent_invoices"
                :key="invoice.id"
                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
              >
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                      <FileText class="h-8 w-8 p-1.5 bg-white dark:bg-gray-700 rounded border" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ invoice.invoice_number }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                        {{ invoice.client.name }}
                      </p>
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ formatCurrency(invoice.total) }}
                    </p>
                    <Badge
                      :class="getStatusColor(invoice.status)"
                      class="text-xs"
                    >
                      {{ invoice.status }}
                    </Badge>
                  </div>
                  <Link :href="`/invoices/${invoice.id}`">
                    <Button variant="ghost" size="icon">
                      <Eye class="h-4 w-4" />
                    </Button>
                  </Link>
                </div>
              </div>

              <div v-if="!recent_invoices.length" class="text-center py-8">
                <FileText class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                  No recent invoices
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                  Get started by creating your first invoice.
                </p>
                <div class="mt-6">
                  <Link href="/invoices/create">
                    <Button>
                      <Plus class="mr-2 h-4 w-4" />
                      Create Invoice
                    </Button>
                  </Link>
                </div>
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
                <CardDescription>Invoices due soon</CardDescription>
              </div>
              <Link href="/invoices?due_soon=7">
                <Button variant="ghost" size="sm">
                  View all
                  <ArrowRight class="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="invoice in upcoming_due"
                :key="invoice.id"
                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg"
              >
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                      <Calendar class="h-8 w-8 p-1.5 bg-white dark:bg-gray-700 rounded border" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ invoice.invoice_number }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                        {{ invoice.client.name }}
                      </p>
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ formatCurrency(invoice.total) }}
                    </p>
                    <p
                      :class="getUrgencyColor(invoice.days_until_due)"
                      class="text-xs font-medium"
                    >
                      {{ formatRelativeTime(invoice.due_date) }}
                    </p>
                  </div>
                  <Link :href="`/invoices/${invoice.id}`">
                    <Button variant="ghost" size="icon">
                      <Send class="h-4 w-4" />
                    </Button>
                  </Link>
                </div>
              </div>

              <div v-if="!upcoming_due.length" class="text-center py-8">
                <Calendar class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                  No upcoming due dates
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                  All your invoices are up to date!
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

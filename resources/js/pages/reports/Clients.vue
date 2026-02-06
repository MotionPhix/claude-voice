<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  Users,
  Calendar,
  Download,
  ArrowLeft,
  DollarSign,
  TrendingUp,
  FileText,
  Eye,
  Star,
  AlertCircle
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Badge } from '@/components/ui/badge';

interface ClientMetric {
  id: number;
  name: string;
  total_revenue: number;
  invoice_count: number;
  average_invoice: number;
  payment_rating: 'excellent' | 'good' | 'fair' | 'poor';
  last_invoice_date: string;
  outstanding_amount: number;
}

interface Props {
  clientMetrics?: ClientMetric[];
  stats?: any;
  performance?: any;
}

const props = withDefaults(defineProps<Props>(), {
  clientMetrics: () => ([
    {
      id: 1,
      name: 'Acme Corporation',
      total_revenue: 45600,
      invoice_count: 18,
      average_invoice: 2533,
      payment_rating: 'excellent',
      last_invoice_date: '2024-06-10',
      outstanding_amount: 0
    },
    {
      id: 2,
      name: 'Tech Solutions Inc',
      total_revenue: 32400,
      invoice_count: 12,
      average_invoice: 2700,
      payment_rating: 'good',
      last_invoice_date: '2024-06-08',
      outstanding_amount: 2700
    },
    {
      id: 3,
      name: 'Global Services Ltd',
      total_revenue: 28800,
      invoice_count: 15,
      average_invoice: 1920,
      payment_rating: 'fair',
      last_invoice_date: '2024-05-25',
      outstanding_amount: 5760
    }
  ]),
  stats: () => ({
    totalClients: 45,
    activeClients: 38,
    newThisMonth: 5,
    averageValue: 15420
  }),
  performance: () => ({
    topPerformers: 8,
    needsAttention: 5,
    inactive: 7
  })
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const getRatingBadgeVariant = (rating: string) => {
  switch (rating) {
    case 'excellent': return 'default';
    case 'good': return 'secondary';
    case 'fair': return 'outline';
    case 'poor': return 'destructive';
    default: return 'secondary';
  }
};

const getRatingIcon = (rating: string) => {
  switch (rating) {
    case 'excellent': return Star;
    case 'good': return TrendingUp;
    case 'fair': return AlertCircle;
    case 'poor': return AlertCircle;
    default: return AlertCircle;
  }
};

const getRatingColor = (rating: string) => {
  switch (rating) {
    case 'excellent': return 'text-green-600';
    case 'good': return 'text-blue-600';
    case 'fair': return 'text-yellow-600';
    case 'poor': return 'text-red-600';
    default: return 'text-gray-600';
  }
};
</script>

<template>
  <Head title="Client Analysis Report" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link :href="route('reports.index')">
            <Button variant="outline" size="sm">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Back to Reports
            </Button>
          </Link>
          <div>
            <h1 class="text-4xl font-bold tracking-tight">Client Analysis</h1>
            <p class="text-muted-foreground">
              Client performance and relationship insights
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Button variant="outline">
            <Download class="h-4 w-4 mr-2" />
            Export PDF
          </Button>
          <Button variant="outline">
            <Calendar class="h-4 w-4 mr-2" />
            Date Range
          </Button>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Clients</CardTitle>
            <Users class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.totalClients }}</div>
            <p class="text-xs text-muted-foreground">
              All client relationships
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Clients</CardTitle>
            <TrendingUp class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.activeClients }}</div>
            <p class="text-xs text-green-600">
              {{ Math.round((stats.activeClients / stats.totalClients) * 100) }}% of total clients
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">New This Month</CardTitle>
            <Star class="h-4 w-4 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.newThisMonth }}</div>
            <p class="text-xs text-muted-foreground">
              Business growth
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Average Value</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.averageValue) }}</div>
            <p class="text-xs text-muted-foreground">
              Per client lifetime value
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Client Performance Categories -->
      <Card>
        <CardHeader>
          <CardTitle>Client Performance Overview</CardTitle>
          <CardDescription>Categorized by payment behavior and value</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-6 border rounded-lg bg-green-50 dark:bg-green-950">
              <Star class="h-8 w-8 text-green-600 mx-auto mb-2" />
              <div class="text-2xl font-bold text-green-600">{{ performance.topPerformers }}</div>
              <p class="text-sm text-green-700">Top Performers</p>
              <p class="text-xs text-muted-foreground mt-1">Excellent payment history</p>
            </div>

            <div class="text-center p-6 border rounded-lg bg-yellow-50 dark:bg-yellow-950">
              <AlertCircle class="h-8 w-8 text-yellow-600 mx-auto mb-2" />
              <div class="text-2xl font-bold text-yellow-600">{{ performance.needsAttention }}</div>
              <p class="text-sm text-yellow-700">Needs Attention</p>
              <p class="text-xs text-muted-foreground mt-1">Late payments or declining value</p>
            </div>

            <div class="text-center p-6 border rounded-lg bg-gray-50 dark:bg-gray-950">
              <Users class="h-8 w-8 text-gray-600 mx-auto mb-2" />
              <div class="text-2xl font-bold text-gray-600">{{ performance.inactive }}</div>
              <p class="text-sm text-gray-700">Inactive</p>
              <p class="text-xs text-muted-foreground mt-1">No recent activity</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Client Metrics Table -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Client Performance Metrics</CardTitle>
              <CardDescription>Detailed analysis for each client</CardDescription>
            </div>
            <Link :href="route('clients.index')">
              <Button variant="outline" size="sm">
                <Eye class="h-4 w-4 mr-2" />
                View All Clients
              </Button>
            </Link>
          </div>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="client in clientMetrics" :key="client.id" class="p-4 border rounded-lg">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <component
                    :is="getRatingIcon(client.payment_rating)"
                    :class="`h-5 w-5 ${getRatingColor(client.payment_rating)}`"
                  />
                  <h3 class="font-semibold">{{ client.name }}</h3>
                  <Badge :variant="getRatingBadgeVariant(client.payment_rating)">
                    {{ client.payment_rating }}
                  </Badge>
                </div>
                <Link :href="route('clients.show', client.uuid)">
                  <Button variant="outline" size="sm">
                    <Eye class="h-4 w-4" />
                  </Button>
                </Link>
              </div>

              <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 text-sm">
                <div>
                  <p class="text-muted-foreground">Total Revenue</p>
                  <p class="font-medium">{{ formatCurrency(client.total_revenue) }}</p>
                </div>

                <div>
                  <p class="text-muted-foreground">Invoices</p>
                  <p class="font-medium">{{ client.invoice_count }}</p>
                </div>

                <div>
                  <p class="text-muted-foreground">Avg Invoice</p>
                  <p class="font-medium">{{ formatCurrency(client.average_invoice) }}</p>
                </div>

                <div>
                  <p class="text-muted-foreground">Outstanding</p>
                  <p :class="`font-medium ${client.outstanding_amount > 0 ? 'text-red-600' : 'text-green-600'}`">
                    {{ formatCurrency(client.outstanding_amount) }}
                  </p>
                </div>

                <div>
                  <p class="text-muted-foreground">Last Invoice</p>
                  <p class="font-medium">{{ client.last_invoice_date }}</p>
                </div>

                <div class="flex items-center gap-2">
                  <Button variant="outline" size="sm">
                    <FileText class="h-4 w-4 mr-1" />
                    History
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-6 border-t">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted-foreground">
                Showing top {{ clientMetrics.length }} clients by revenue
              </span>
              <Link :href="route('clients.index')">
                <Button variant="outline" size="sm">
                  View All Clients
                </Button>
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Revenue Distribution & Growth Trends -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Revenue Distribution</CardTitle>
            <CardDescription>Client contribution to total revenue</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                  <span class="text-sm">Top 20% clients</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-4/5 bg-green-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">80%</span>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                  <span class="text-sm">Mid-tier clients</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-1/5 bg-blue-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">15%</span>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                  <span class="text-sm">Small clients</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-1/20 bg-yellow-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">5%</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Growth Opportunities</CardTitle>
            <CardDescription>Potential for expansion</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 border rounded">
                <div>
                  <p class="font-medium text-green-600">Upsell Opportunities</p>
                  <p class="text-sm text-muted-foreground">12 clients with growth potential</p>
                </div>
                <Button variant="outline" size="sm">
                  Review
                </Button>
              </div>

              <div class="flex items-center justify-between p-3 border rounded">
                <div>
                  <p class="font-medium text-blue-600">Re-engagement Needed</p>
                  <p class="text-sm text-muted-foreground">7 inactive clients to contact</p>
                </div>
                <Button variant="outline" size="sm">
                  Contact
                </Button>
              </div>

              <div class="flex items-center justify-between p-3 border rounded">
                <div>
                  <p class="font-medium text-yellow-600">Payment Issues</p>
                  <p class="text-sm text-muted-foreground">5 clients need payment review</p>
                </div>
                <Button variant="outline" size="sm">
                  Address
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
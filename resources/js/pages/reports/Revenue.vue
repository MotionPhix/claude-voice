<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  TrendingUp,
  TrendingDown,
  DollarSign,
  Calendar,
  Download,
  ArrowLeft,
  BarChart3,
  Percent
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Badge } from '@/components/ui/badge';

interface Props {
  revenueData?: any;
  periodData?: any;
  growthMetrics?: any;
}

const props = withDefaults(defineProps<Props>(), {
  revenueData: () => ({
    total: 125000,
    thisMonth: 15500,
    lastMonth: 12800,
    thisYear: 89000,
    lastYear: 72000
  }),
  periodData: () => ([
    { period: 'Jan 2024', revenue: 8500, growth: 12.5 },
    { period: 'Feb 2024', revenue: 9200, growth: 8.2 },
    { period: 'Mar 2024', revenue: 10100, growth: 9.8 },
    { period: 'Apr 2024', revenue: 11200, growth: 10.9 },
    { period: 'May 2024', revenue: 12800, growth: 14.3 },
    { period: 'Jun 2024', revenue: 15500, growth: 21.1 }
  ]),
  growthMetrics: () => ({
    monthlyGrowth: 21.1,
    yearlyGrowth: 23.6,
    averageMonthly: 11250,
    projectedYear: 186000
  })
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const formatPercentage = (value: number) => {
  const sign = value > 0 ? '+' : '';
  return `${sign}${value.toFixed(1)}%`;
};
</script>

<template>
  <Head title="Revenue Report" />

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
            <h1 class="text-4xl font-bold tracking-tight">Revenue Report</h1>
            <p class="text-muted-foreground">
              Financial performance and revenue analysis
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
            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(revenueData.total) }}</div>
            <p class="text-xs text-muted-foreground">
              All time revenue
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">This Month</CardTitle>
            <TrendingUp class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(revenueData.thisMonth) }}</div>
            <p class="text-xs text-green-600 flex items-center">
              <TrendingUp class="h-3 w-3 mr-1" />
              {{ formatPercentage(growthMetrics.monthlyGrowth) }} from last month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">This Year</CardTitle>
            <BarChart3 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(revenueData.thisYear) }}</div>
            <p class="text-xs text-green-600 flex items-center">
              <TrendingUp class="h-3 w-3 mr-1" />
              {{ formatPercentage(growthMetrics.yearlyGrowth) }} from last year
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Monthly Average</CardTitle>
            <Percent class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(growthMetrics.averageMonthly) }}</div>
            <p class="text-xs text-muted-foreground">
              6-month average
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Revenue Trend Chart -->
      <Card>
        <CardHeader>
          <CardTitle>Revenue Trend</CardTitle>
          <CardDescription>Monthly revenue performance over the last 6 months</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Simple bar chart representation -->
            <div class="space-y-3">
              <div v-for="item in periodData" :key="item.period" class="flex items-center justify-between">
                <div class="w-20 text-sm font-medium">{{ item.period.split(' ')[0] }}</div>
                <div class="flex-1 mx-4">
                  <div class="relative">
                    <div class="w-full bg-muted rounded h-6 overflow-hidden">
                      <div
                        class="h-full bg-gradient-to-r from-blue-500 to-green-500 rounded transition-all duration-300"
                        :style="`width: ${(item.revenue / Math.max(...periodData.map(d => d.revenue))) * 100}%`"
                      ></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center text-xs font-medium text-white">
                      {{ formatCurrency(item.revenue) }}
                    </div>
                  </div>
                </div>
                <div class="w-16 text-right">
                  <Badge :variant="item.growth > 0 ? 'default' : 'destructive'">
                    {{ formatPercentage(item.growth) }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Growth Analysis -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Growth Analysis</CardTitle>
            <CardDescription>Performance trends and projections</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <p class="text-sm font-medium">Monthly Growth Rate</p>
                <p class="text-xs text-muted-foreground">Average over 6 months</p>
              </div>
              <div class="flex items-center gap-2">
                <TrendingUp class="h-4 w-4 text-green-500" />
                <span class="text-lg font-bold text-green-600">
                  {{ formatPercentage(growthMetrics.monthlyGrowth) }}
                </span>
              </div>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <p class="text-sm font-medium">Yearly Growth Rate</p>
                <p class="text-xs text-muted-foreground">Year over year</p>
              </div>
              <div class="flex items-center gap-2">
                <TrendingUp class="h-4 w-4 text-green-500" />
                <span class="text-lg font-bold text-green-600">
                  {{ formatPercentage(growthMetrics.yearlyGrowth) }}
                </span>
              </div>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <p class="text-sm font-medium">Projected Year-End</p>
                <p class="text-xs text-muted-foreground">Based on current trend</p>
              </div>
              <div class="flex items-center gap-2">
                <BarChart3 class="h-4 w-4 text-blue-500" />
                <span class="text-lg font-bold text-blue-600">
                  {{ formatCurrency(growthMetrics.projectedYear) }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Revenue Sources</CardTitle>
            <CardDescription>Breakdown by invoice status</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                  <span class="text-sm">Paid Invoices</span>
                </div>
                <div class="text-sm font-medium">{{ formatCurrency(89000) }}</div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                  <span class="text-sm">Pending Collection</span>
                </div>
                <div class="text-sm font-medium">{{ formatCurrency(25000) }}</div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                  <span class="text-sm">Overdue</span>
                </div>
                <div class="text-sm font-medium">{{ formatCurrency(11000) }}</div>
              </div>
            </div>

            <div class="pt-4 border-t">
              <div class="flex items-center justify-between font-medium">
                <span>Total Outstanding</span>
                <span>{{ formatCurrency(36000) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
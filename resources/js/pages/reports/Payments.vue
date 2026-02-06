<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  Receipt,
  Calendar,
  Download,
  ArrowLeft,
  DollarSign,
  Clock,
  CheckCircle,
  AlertTriangle,
  Percent
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Badge } from '@/components/ui/badge';

interface Payment {
  id: number;
  invoice_number: string;
  client_name: string;
  amount: number;
  payment_date: string;
  method: string;
  status: 'completed' | 'pending' | 'failed';
}

interface Props {
  payments?: Payment[];
  stats?: any;
  collectionMetrics?: any;
}

const props = withDefaults(defineProps<Props>(), {
  payments: () => ([
    {
      id: 1,
      invoice_number: 'INV-2024-001',
      client_name: 'Acme Corporation',
      amount: 2500,
      payment_date: '2024-06-15',
      method: 'Bank Transfer',
      status: 'completed'
    },
    {
      id: 2,
      invoice_number: 'INV-2024-002',
      client_name: 'Tech Solutions Inc',
      amount: 1800,
      payment_date: '2024-06-14',
      method: 'Credit Card',
      status: 'completed'
    },
    {
      id: 3,
      invoice_number: 'INV-2024-003',
      client_name: 'Global Services Ltd',
      amount: 3200,
      payment_date: '2024-06-13',
      method: 'Check',
      status: 'pending'
    }
  ]),
  stats: () => ({
    totalProcessed: 45600,
    pendingAmount: 12800,
    averagePaymentTime: 18,
    collectionRate: 87.5
  }),
  collectionMetrics: () => ({
    onTime: 72,
    late: 15,
    outstanding: 13
  })
});

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'completed': return 'default';
    case 'pending': return 'secondary';
    case 'failed': return 'destructive';
    default: return 'secondary';
  }
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'completed': return CheckCircle;
    case 'pending': return Clock;
    case 'failed': return AlertTriangle;
    default: return Clock;
  }
};
</script>

<template>
  <Head title="Payment Report" />

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
            <h1 class="text-4xl font-bold tracking-tight">Payment Report</h1>
            <p class="text-muted-foreground">
              Payment tracking and collection analysis
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
            <CardTitle class="text-sm font-medium">Total Processed</CardTitle>
            <Receipt class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.totalProcessed) }}</div>
            <p class="text-xs text-muted-foreground">
              This month
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending Amount</CardTitle>
            <Clock class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.pendingAmount) }}</div>
            <p class="text-xs text-muted-foreground">
              Awaiting processing
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Collection Rate</CardTitle>
            <Percent class="h-4 w-4 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.collectionRate }}%</div>
            <p class="text-xs text-green-600">
              Above industry average
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Avg Payment Time</CardTitle>
            <Calendar class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.averagePaymentTime }} days</div>
            <p class="text-xs text-muted-foreground">
              From invoice to payment
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Collection Performance -->
      <Card>
        <CardHeader>
          <CardTitle>Collection Performance</CardTitle>
          <CardDescription>Payment timing analysis</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-6 border rounded-lg">
              <CheckCircle class="h-8 w-8 text-green-500 mx-auto mb-2" />
              <div class="text-2xl font-bold text-green-600">{{ collectionMetrics.onTime }}%</div>
              <p class="text-sm text-muted-foreground">Paid On Time</p>
            </div>

            <div class="text-center p-6 border rounded-lg">
              <Clock class="h-8 w-8 text-yellow-500 mx-auto mb-2" />
              <div class="text-2xl font-bold text-yellow-600">{{ collectionMetrics.late }}%</div>
              <p class="text-sm text-muted-foreground">Late Payments</p>
            </div>

            <div class="text-center p-6 border rounded-lg">
              <AlertTriangle class="h-8 w-8 text-red-500 mx-auto mb-2" />
              <div class="text-2xl font-bold text-red-600">{{ collectionMetrics.outstanding }}%</div>
              <p class="text-sm text-muted-foreground">Outstanding</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Payments -->
      <Card>
        <CardHeader>
          <CardTitle>Recent Payments</CardTitle>
          <CardDescription>Latest payment transactions</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="payment in payments" :key="payment.id" class="flex items-center justify-between p-4 border rounded-lg">
              <div class="flex items-center space-x-4">
                <component
                  :is="getStatusIcon(payment.status)"
                  :class="`h-5 w-5 ${payment.status === 'completed' ? 'text-green-500' : payment.status === 'pending' ? 'text-yellow-500' : 'text-red-500'}`"
                />
                <div>
                  <p class="font-medium">{{ payment.invoice_number }}</p>
                  <p class="text-sm text-muted-foreground">{{ payment.client_name }}</p>
                </div>
              </div>

              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(payment.amount) }}</p>
                  <p class="text-xs text-muted-foreground">{{ payment.method }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm">{{ payment.payment_date }}</p>
                  <Badge :variant="getStatusBadgeVariant(payment.status)">
                    {{ payment.status }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-6 border-t">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted-foreground">
                Showing {{ payments.length }} of {{ payments.length }} payments
              </span>
              <Button variant="outline" size="sm">
                View All Payments
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Payment Methods Analysis -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Payment Methods</CardTitle>
            <CardDescription>Breakdown by payment type</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                  <span class="text-sm">Bank Transfer</span>
                </div>
                <div class="text-sm font-medium">45%</div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                  <span class="text-sm">Credit Card</span>
                </div>
                <div class="text-sm font-medium">35%</div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                  <span class="text-sm">Check</span>
                </div>
                <div class="text-sm font-medium">15%</div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                  <span class="text-sm">Other</span>
                </div>
                <div class="text-sm font-medium">5%</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Monthly Trends</CardTitle>
            <CardDescription>Payment processing over time</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 border rounded">
                <span class="text-sm">June 2024</span>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-3/4 bg-green-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">75%</span>
                </div>
              </div>

              <div class="flex items-center justify-between p-3 border rounded">
                <span class="text-sm">May 2024</span>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-4/5 bg-green-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">80%</span>
                </div>
              </div>

              <div class="flex items-center justify-between p-3 border rounded">
                <span class="text-sm">April 2024</span>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-2/3 bg-yellow-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">67%</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
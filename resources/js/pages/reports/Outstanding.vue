<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  AlertTriangle,
  Calendar,
  Download,
  ArrowLeft,
  DollarSign,
  Clock,
  Users,
  FileText,
  Send,
  Eye
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { Badge } from '@/components/ui/badge';

interface OutstandingInvoice {
  id: number;
  invoice_number: string;
  client_name: string;
  amount: number;
  due_date: string;
  days_overdue: number;
  status: 'sent' | 'overdue';
}

interface Props {
  outstandingInvoices?: OutstandingInvoice[];
  stats?: any;
  agingAnalysis?: any;
}

const props = withDefaults(defineProps<Props>(), {
  outstandingInvoices: () => ([
    {
      id: 1,
      invoice_number: 'INV-2024-015',
      client_name: 'Delayed Corp',
      amount: 5500,
      due_date: '2024-05-15',
      days_overdue: 31,
      status: 'overdue'
    },
    {
      id: 2,
      invoice_number: 'INV-2024-018',
      client_name: 'Slow Pay Inc',
      amount: 3200,
      due_date: '2024-05-28',
      days_overdue: 18,
      status: 'overdue'
    },
    {
      id: 3,
      invoice_number: 'INV-2024-022',
      client_name: 'Future Tech Ltd',
      amount: 2800,
      due_date: '2024-06-20',
      days_overdue: 0,
      status: 'sent'
    }
  ]),
  stats: () => ({
    totalOutstanding: 42500,
    overdueAmount: 23400,
    sentAmount: 19100,
    averageDaysOverdue: 24
  }),
  agingAnalysis: () => ({
    current: 19100,
    days1to30: 8700,
    days31to60: 7200,
    days61to90: 4800,
    over90: 2700
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
    case 'sent': return 'secondary';
    case 'overdue': return 'destructive';
    default: return 'secondary';
  }
};

const getDaysOverdueSeverity = (days: number) => {
  if (days <= 0) return 'text-green-600';
  if (days <= 30) return 'text-yellow-600';
  if (days <= 60) return 'text-orange-600';
  return 'text-red-600';
};
</script>

<template>
  <Head title="Outstanding Invoices Report" />

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
            <h1 class="text-4xl font-bold tracking-tight">Outstanding Invoices</h1>
            <p class="text-muted-foreground">
              Overdue and pending payment analysis
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Button variant="outline">
            <Download class="h-4 w-4 mr-2" />
            Export PDF
          </Button>
          <Button variant="outline">
            <Send class="h-4 w-4 mr-2" />
            Send Reminders
          </Button>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Outstanding</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.totalOutstanding) }}</div>
            <p class="text-xs text-muted-foreground">
              All unpaid invoices
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Overdue Amount</CardTitle>
            <AlertTriangle class="h-4 w-4 text-red-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-red-600">{{ formatCurrency(stats.overdueAmount) }}</div>
            <p class="text-xs text-red-600">
              Requires immediate attention
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Sent (Not Due)</CardTitle>
            <Clock class="h-4 w-4 text-yellow-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(stats.sentAmount) }}</div>
            <p class="text-xs text-muted-foreground">
              Within payment terms
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Avg Days Overdue</CardTitle>
            <Calendar class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.averageDaysOverdue }}</div>
            <p class="text-xs text-muted-foreground">
              For overdue invoices
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Aging Analysis -->
      <Card>
        <CardHeader>
          <CardTitle>Aging Analysis</CardTitle>
          <CardDescription>Outstanding amounts by age</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="text-center p-4 border rounded-lg">
              <div class="text-lg font-bold text-green-600">{{ formatCurrency(agingAnalysis.current) }}</div>
              <p class="text-sm text-muted-foreground">Current</p>
              <p class="text-xs text-green-600">Not yet due</p>
            </div>

            <div class="text-center p-4 border rounded-lg">
              <div class="text-lg font-bold text-yellow-600">{{ formatCurrency(agingAnalysis.days1to30) }}</div>
              <p class="text-sm text-muted-foreground">1-30 Days</p>
              <p class="text-xs text-yellow-600">Recently overdue</p>
            </div>

            <div class="text-center p-4 border rounded-lg">
              <div class="text-lg font-bold text-orange-600">{{ formatCurrency(agingAnalysis.days31to60) }}</div>
              <p class="text-sm text-muted-foreground">31-60 Days</p>
              <p class="text-xs text-orange-600">Needs follow-up</p>
            </div>

            <div class="text-center p-4 border rounded-lg">
              <div class="text-lg font-bold text-red-600">{{ formatCurrency(agingAnalysis.days61to90) }}</div>
              <p class="text-sm text-muted-foreground">61-90 Days</p>
              <p class="text-xs text-red-600">Collection risk</p>
            </div>

            <div class="text-center p-4 border rounded-lg">
              <div class="text-lg font-bold text-red-800">{{ formatCurrency(agingAnalysis.over90) }}</div>
              <p class="text-sm text-muted-foreground">90+ Days</p>
              <p class="text-xs text-red-800">High risk</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Outstanding Invoices List -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Outstanding Invoices</CardTitle>
              <CardDescription>All unpaid invoices ordered by due date</CardDescription>
            </div>
            <div class="flex items-center gap-2">
              <Button variant="outline" size="sm">
                <Send class="h-4 w-4 mr-2" />
                Bulk Reminder
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="invoice in outstandingInvoices" :key="invoice.id" class="flex items-center justify-between p-4 border rounded-lg">
              <div class="flex items-center space-x-4">
                <AlertTriangle
                  :class="`h-5 w-5 ${invoice.status === 'overdue' ? 'text-red-500' : 'text-yellow-500'}`"
                />
                <div>
                  <p class="font-medium">{{ invoice.invoice_number }}</p>
                  <p class="text-sm text-muted-foreground">{{ invoice.client_name }}</p>
                </div>
              </div>

              <div class="flex items-center space-x-6">
                <div class="text-right">
                  <p class="font-medium">{{ formatCurrency(invoice.amount) }}</p>
                  <p class="text-xs text-muted-foreground">Due: {{ invoice.due_date }}</p>
                </div>

                <div class="text-right min-w-[80px]">
                  <p :class="`text-sm font-medium ${getDaysOverdueSeverity(invoice.days_overdue)}`">
                    {{ invoice.days_overdue > 0 ? `${invoice.days_overdue} days` : 'Current' }}
                  </p>
                  <Badge :variant="getStatusBadgeVariant(invoice.status)">
                    {{ invoice.status }}
                  </Badge>
                </div>

                <div class="flex items-center gap-2">
                  <Link :href="route('invoices.show', invoice.uuid)">
                    <Button variant="outline" size="sm">
                      <Eye class="h-4 w-4" />
                    </Button>
                  </Link>
                  <Button variant="outline" size="sm">
                    <Send class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-6 border-t">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted-foreground">
                Showing {{ outstandingInvoices.length }} outstanding invoices
              </span>
              <Link :href="route('invoices.index', { status: 'overdue' })">
                <Button variant="outline" size="sm">
                  View All Outstanding
                </Button>
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Collection Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Recommended Actions</CardTitle>
            <CardDescription>Next steps for collection</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <p class="font-medium text-red-600">Critical: 90+ Days Overdue</p>
                <p class="text-sm text-muted-foreground">2 invoices require immediate action</p>
              </div>
              <Button variant="outline" size="sm">
                <AlertTriangle class="h-4 w-4 mr-2" />
                Review
              </Button>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <p class="font-medium text-orange-600">Send Reminders</p>
                <p class="text-sm text-muted-foreground">5 invoices ready for follow-up</p>
              </div>
              <Button variant="outline" size="sm">
                <Send class="h-4 w-4 mr-2" />
                Send
              </Button>
            </div>

            <div class="flex items-center justify-between p-4 border rounded-lg">
              <div>
                <p class="font-medium text-blue-600">Phone Follow-up</p>
                <p class="text-sm text-muted-foreground">3 high-value invoices need personal contact</p>
              </div>
              <Button variant="outline" size="sm">
                <Users class="h-4 w-4 mr-2" />
                Contact
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Collection Performance</CardTitle>
            <CardDescription>Monthly collection metrics</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 border rounded">
                <span class="text-sm">Collections this month</span>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-green-600">{{ formatCurrency(28500) }}</span>
                </div>
              </div>

              <div class="flex items-center justify-between p-3 border rounded">
                <span class="text-sm">Collection rate</span>
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-muted rounded h-2">
                    <div class="w-4/5 bg-green-500 rounded h-2"></div>
                  </div>
                  <span class="text-sm font-medium">78%</span>
                </div>
              </div>

              <div class="flex items-center justify-between p-3 border rounded">
                <span class="text-sm">Average collection time</span>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium">32 days</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
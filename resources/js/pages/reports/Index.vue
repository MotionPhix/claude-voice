<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  BarChart3,
  PieChart,
  TrendingUp,
  DollarSign,
  Users,
  Receipt,
  Calendar,
  Download,
  FileText,
  ArrowRight
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import Card from '@/components/custom/Card.vue';
import { usePermissions } from '@/composables/usePermissions';

const {
  canViewReports,
  isAccountant,
  isManager,
  isAdmin,
  isOwner
} = usePermissions();

interface ReportSection {
  title: string;
  description: string;
  icon: any;
  route: string;
  color: string;
  available: boolean;
}

const financialReports: ReportSection[] = [
  {
    title: 'Revenue Report',
    description: 'Monthly and yearly revenue analysis with trends',
    icon: TrendingUp,
    route: route('reports.revenue'),
    color: 'text-green-600',
    available: isAccountant || isManager || isAdmin || isOwner
  },
  {
    title: 'Payment Report',
    description: 'Payment tracking and collection analysis',
    icon: Receipt,
    route: route('reports.payments'),
    color: 'text-blue-600',
    available: isAccountant || isAdmin || isOwner
  },
  {
    title: 'Outstanding Invoices',
    description: 'Overdue and pending payment analysis',
    icon: FileText,
    route: route('reports.outstanding'),
    color: 'text-orange-600',
    available: canViewReports
  }
];

const businessReports: ReportSection[] = [
  {
    title: 'Client Analysis',
    description: 'Client performance and relationship insights',
    icon: Users,
    route: route('reports.clients'),
    color: 'text-purple-600',
    available: isManager || isAdmin || isOwner
  }
];
</script>

<template>
  <Head title="Reports" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold tracking-tight">Reports & Analytics</h1>
          <p class="text-muted-foreground">
            Comprehensive business insights and financial analysis
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Button variant="outline">
            <Download class="h-4 w-4 mr-2" />
            Export All
          </Button>
        </div>
      </div>

      <!-- Quick Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Reports</CardTitle>
            <BarChart3 class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ financialReports.length + businessReports.length }}</div>
            <p class="text-xs text-muted-foreground">
              Available report types
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Last Updated</CardTitle>
            <Calendar class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">Today</div>
            <p class="text-xs text-muted-foreground">
              Real-time data
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Export Formats</CardTitle>
            <Download class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">PDF</div>
            <p class="text-xs text-muted-foreground">
              CSV, Excel available
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Access Level</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ canViewReports ? 'Full' : 'Limited' }}</div>
            <p class="text-xs text-muted-foreground">
              Based on role permissions
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Financial Reports -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Financial Reports</CardTitle>
              <CardDescription>Revenue, payments, and financial performance analysis</CardDescription>
            </div>
            <DollarSign class="h-8 w-8 text-green-600" />
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="report in financialReports" :key="report.title" class="space-y-4">
              <div v-if="report.available" class="p-6 border rounded-lg hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                  <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-3">
                      <component :is="report.icon" :class="`h-5 w-5 ${report.color}`" />
                      <h3 class="font-semibold">{{ report.title }}</h3>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ report.description }}</p>
                  </div>
                </div>
                <div class="mt-4 flex justify-between items-center">
                  <Link :href="report.route">
                    <Button variant="outline" size="sm">
                      View Report
                      <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                  </Link>
                </div>
              </div>

              <div v-else class="p-6 border rounded-lg opacity-50 bg-muted/50">
                <div class="flex items-start justify-between">
                  <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-3">
                      <component :is="report.icon" class="h-5 w-5 text-muted-foreground" />
                      <h3 class="font-semibold text-muted-foreground">{{ report.title }}</h3>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ report.description }}</p>
                  </div>
                </div>
                <div class="mt-4">
                  <Button variant="outline" size="sm" disabled>
                    Access Restricted
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Business Reports -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle>Business Intelligence</CardTitle>
              <CardDescription>Client analysis and business performance metrics</CardDescription>
            </div>
            <PieChart class="h-8 w-8 text-purple-600" />
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="report in businessReports" :key="report.title" class="space-y-4">
              <div v-if="report.available" class="p-6 border rounded-lg hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                  <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-3">
                      <component :is="report.icon" :class="`h-5 w-5 ${report.color}`" />
                      <h3 class="font-semibold">{{ report.title }}</h3>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ report.description }}</p>
                  </div>
                </div>
                <div class="mt-4 flex justify-between items-center">
                  <Link :href="report.route">
                    <Button variant="outline" size="sm">
                      View Report
                      <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                  </Link>
                </div>
              </div>

              <div v-else class="p-6 border rounded-lg opacity-50 bg-muted/50">
                <div class="flex items-start justify-between">
                  <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-3">
                      <component :is="report.icon" class="h-5 w-5 text-muted-foreground" />
                      <h3 class="font-semibold text-muted-foreground">{{ report.title }}</h3>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ report.description }}</p>
                  </div>
                </div>
                <div class="mt-4">
                  <Button variant="outline" size="sm" disabled>
                    Access Restricted
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  ArrowLeft,
  Building,
  Mail,
  Phone,
  MapPin,
  Edit,
  FileText,
  DollarSign,
  Calendar,
  Plus,
  Eye,
  Trash2
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { usePermissions } from '@/composables/usePermissions';

interface Client {
  id: number;
  name: string;
  email: string;
  phone?: string;
  company?: string;
  address?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
  notes?: string;
  is_active: boolean;
  created_at: string;
}

interface Invoice {
  id: number;
  invoice_number: string;
  total: number;
  status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
  due_date: string;
  created_at: string;
}

interface ClientStats {
  total_invoices: number;
  total_revenue: number;
  outstanding_amount: number;
  average_invoice: number;
  last_invoice_date?: string;
}

interface Props {
  client: Client;
  recentInvoices: Invoice[];
  stats: ClientStats;
}

const props = defineProps<Props>();

const {
  canUpdateClients,
  canDeleteClients,
  canCreateInvoices
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
    case 'cancelled': return 'destructive';
    default: return 'secondary';
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString();
};
</script>

<template>
  <Head :title="`Client: ${client.name}`" />

  <AppLayout>
    <div class="container mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link :href="route('clients.index')">
            <Button variant="outline" size="sm">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Back to Clients
            </Button>
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-4xl font-bold tracking-tight">{{ client.name }}</h1>
              <Badge :variant="client.is_active ? 'default' : 'secondary'">
                {{ client.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </div>
            <p class="text-muted-foreground">
              Client since {{ formatDate(client.created_at) }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Link v-if="canCreateInvoices" :href="route('invoices.create', { client: client.id })">
            <Button>
              <Plus class="h-4 w-4 mr-2" />
              Create Invoice
            </Button>
          </Link>
          <Link v-if="canUpdateClients" :href="route('clients.edit', client.id)">
            <Button variant="outline">
              <Edit class="h-4 w-4 mr-2" />
              Edit Client
            </Button>
          </Link>
        </div>
      </div>

      <!-- Client Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Invoices</CardTitle>
            <FileText class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_invoices }}</div>
            <p class="text-xs text-muted-foreground">
              All time
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</div>
            <p class="text-xs text-muted-foreground">
              Lifetime value
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Outstanding</CardTitle>
            <Calendar class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold" :class="stats.outstanding_amount > 0 ? 'text-red-600' : 'text-green-600'">
              {{ formatCurrency(stats.outstanding_amount) }}
            </div>
            <p class="text-xs text-muted-foreground">
              Unpaid amount
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Average Invoice</CardTitle>
            <DollarSign class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.average_invoice) }}</div>
            <p class="text-xs text-muted-foreground">
              Per invoice
            </p>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Client Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Building class="h-5 w-5" />
              Client Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Contact Details -->
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <Mail class="h-4 w-4 text-muted-foreground" />
                <div>
                  <p class="text-sm text-muted-foreground">Email</p>
                  <p class="font-medium">{{ client.email }}</p>
                </div>
              </div>

              <div v-if="client.phone" class="flex items-center gap-3">
                <Phone class="h-4 w-4 text-muted-foreground" />
                <div>
                  <p class="text-sm text-muted-foreground">Phone</p>
                  <p class="font-medium">{{ client.phone }}</p>
                </div>
              </div>

              <div v-if="client.company" class="flex items-center gap-3">
                <Building class="h-4 w-4 text-muted-foreground" />
                <div>
                  <p class="text-sm text-muted-foreground">Company</p>
                  <p class="font-medium">{{ client.company }}</p>
                </div>
              </div>
            </div>

            <!-- Address -->
            <div v-if="client.address || client.city || client.state || client.country">
              <Separator class="my-4" />
              <div class="flex items-start gap-3">
                <MapPin class="h-4 w-4 text-muted-foreground mt-1" />
                <div class="space-y-1">
                  <p class="text-sm text-muted-foreground">Address</p>
                  <div class="space-y-1">
                    <p v-if="client.address" class="font-medium">{{ client.address }}</p>
                    <p v-if="client.city || client.state || client.postal_code" class="font-medium">
                      {{ [client.city, client.state, client.postal_code].filter(Boolean).join(', ') }}
                    </p>
                    <p v-if="client.country" class="font-medium">{{ client.country }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="client.notes">
              <Separator class="my-4" />
              <div>
                <p class="text-sm text-muted-foreground mb-2">Notes</p>
                <p class="text-sm">{{ client.notes }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Invoices -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Recent Invoices</CardTitle>
              <Link :href="route('invoices.index', { client: client.id })">
                <Button variant="outline" size="sm">
                  <Eye class="h-4 w-4 mr-2" />
                  View All
                </Button>
              </Link>
            </div>
            <CardDescription>Latest invoice activity for this client</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="recentInvoices.length > 0" class="space-y-4">
              <div v-for="invoice in recentInvoices" :key="invoice.id" class="flex items-center justify-between p-4 border rounded-lg">
                <div class="space-y-1">
                  <p class="font-medium">{{ invoice.invoice_number }}</p>
                  <p class="text-sm text-muted-foreground">
                    Due: {{ formatDate(invoice.due_date) }}
                  </p>
                </div>
                <div class="text-right space-y-1">
                  <p class="font-medium">{{ formatCurrency(invoice.total) }}</p>
                  <Badge :variant="getStatusBadgeVariant(invoice.status)">
                    {{ invoice.status }}
                  </Badge>
                </div>
              </div>

              <div class="pt-4 border-t">
                <Link :href="route('invoices.index', { client: client.id })">
                  <Button variant="outline" class="w-full">
                    View All Invoices for {{ client.name }}
                  </Button>
                </Link>
              </div>
            </div>

            <div v-else class="text-center py-8">
              <FileText class="mx-auto h-12 w-12 text-muted-foreground/50" />
              <h3 class="mt-4 text-lg font-medium">No invoices yet</h3>
              <p class="mt-1 text-sm text-muted-foreground">
                Create the first invoice for this client.
              </p>
              <div class="mt-6">
                <Link v-if="canCreateInvoices" :href="route('invoices.create', { client: client.id })">
                  <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Create Invoice
                  </Button>
                </Link>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Danger Zone -->
      <Card v-if="canDeleteClients" class="border-red-200">
        <CardHeader>
          <CardTitle class="text-red-600">Danger Zone</CardTitle>
          <CardDescription>
            Irreversible and destructive actions
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg">
            <div>
              <p class="font-medium text-red-600">Delete Client</p>
              <p class="text-sm text-muted-foreground">
                This will permanently delete the client and all associated data. This action cannot be undone.
              </p>
            </div>
            <Button variant="destructive">
              <Trash2 class="h-4 w-4 mr-2" />
              Delete
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
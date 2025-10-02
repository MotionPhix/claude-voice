<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Search, Edit, Trash2, Eye } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/composables/usePermissions';

interface Client {
  id: number;
  name: string;
  email: string;
  phone?: string;
  city?: string;
  country?: string;
  is_active: boolean;
  invoices_count: number;
  total_owed: number;
  created_at: string;
}

interface Props {
  clients: {
    data: Client[];
    links: any;
    meta: any;
  };
  search?: string;
}

const props = defineProps<Props>();

const { canCreateClients, canEditClients, canDeleteClients } = usePermissions();

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};
</script>

<template>
  <Head title="Clients" />

  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Clients</h1>
          <p class="text-muted-foreground">Manage your client relationships</p>
        </div>

        <Link v-if="canCreateClients" :href="route('clients.create')">
          <Button>
            <Plus class="h-4 w-4 mr-2" />
            New Client
          </Button>
        </Link>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <div class="flex items-center space-x-4">
            <div class="relative flex-1 max-w-sm">
              <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <Input
                :value="search"
                placeholder="Search clients..."
                class="pl-9"
              />
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- Clients Table -->
      <Card>
        <CardHeader>
          <CardTitle>All Clients</CardTitle>
          <CardDescription>
            A list of all your clients and their information
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="overflow-hidden rounded-md border">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-muted/50">
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Client
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Contact
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Location
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Invoices
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Amount Owed
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Status
                  </th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="client in clients.data" :key="client.id" class="border-b">
                  <td class="p-4">
                    <div class="font-medium">{{ client.name }}</div>
                    <div class="text-sm text-muted-foreground">
                      Created {{ new Date(client.created_at).toLocaleDateString() }}
                    </div>
                  </td>
                  <td class="p-4">
                    <div class="text-sm">{{ client.email }}</div>
                    <div v-if="client.phone" class="text-sm text-muted-foreground">
                      {{ client.phone }}
                    </div>
                  </td>
                  <td class="p-4">
                    <div v-if="client.city || client.country" class="text-sm">
                      {{ [client.city, client.country].filter(Boolean).join(', ') }}
                    </div>
                    <div v-else class="text-sm text-muted-foreground">—</div>
                  </td>
                  <td class="p-4">
                    <div class="text-sm font-medium">{{ client.invoices_count }}</div>
                  </td>
                  <td class="p-4">
                    <div class="text-sm font-medium">{{ formatCurrency(client.total_owed) }}</div>
                  </td>
                  <td class="p-4">
                    <Badge :variant="client.is_active ? 'default' : 'secondary'">
                      {{ client.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center space-x-2">
                      <Link :href="route('clients.show', client.id)">
                        <Button variant="ghost" size="sm">
                          <Eye class="h-4 w-4" />
                        </Button>
                      </Link>
                      <Link v-if="canEditClients" :href="route('clients.edit', client.id)">
                        <Button variant="ghost" size="sm">
                          <Edit class="h-4 w-4" />
                        </Button>
                      </Link>
                      <Button v-if="canDeleteClients" variant="ghost" size="sm">
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="!clients.data.length" class="text-center py-12">
            <div class="text-muted-foreground">No clients found</div>
            <Link v-if="canCreateClients" :href="route('clients.create')" class="mt-4 inline-block">
              <Button>
                <Plus class="h-4 w-4 mr-2" />
                Create your first client
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
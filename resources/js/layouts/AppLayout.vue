<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useAppearance } from '@/composables/useAppearance';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  BarChart3,
  Bell,
  ChevronDown,
  FileText,
  HelpCircle,
  Home,
  LogOut,
  Menu,
  Monitor,
  Moon,
  Repeat,
  Search,
  Settings,
  Sun,
  User,
  Users,
  Wallet,
  X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
}

interface Props {
  breadcrumbs?: Array<{
    title: string;
    href?: string;
  }>;
  title?: string;
}

const props = defineProps<Props>();

const page = usePage();
const { appearance, updateAppearance } = useAppearance();

// Navigation state
const sidebarOpen = ref(false);
const searchQuery = ref('');

// Mock notifications count - in real app, this would come from props or API
const notificationCount = ref(3);

const user = computed(() => page.props.auth?.user as User);

// Navigation menu structure
const navigation = [
  {
    name: 'Dashboard',
    href: route('dashboard'),
    icon: Home,
    current: page.url === '/dashboard',
  },
  {
    name: 'Invoices',
    icon: FileText,
    current: page.url.startsWith('/invoices'),
    children: [
      { name: 'All Invoices', href: route('invoices.index') },
      { name: 'Create Invoice', href: route('invoices.create') },
      { name: 'Draft Invoices', href: route('invoices.index', { status: 'draft' }) },
      { name: 'Overdue Invoices', href: route('invoices.index', { status: 'overdue' }) },
    ],
  },
  {
    name: 'Clients',
    href: route('clients.index'),
    icon: Users,
    current: page.url.startsWith('/clients'),
  },
  {
    name: 'Payments',
    href: '/payments',
    icon: Wallet,
    current: page.url.startsWith('/payments'),
  },
  {
    name: 'Recurring',
    href: route('recurring-invoices.index'),
    icon: Repeat,
    current: page.url.startsWith('/recurring-invoices'),
  },
  {
    name: 'Reports',
    href: route('reports.index'),
    icon: BarChart3,
    current: page.url.startsWith('/reports'),
  },
];

const secondaryNavigation = [
  {
    name: 'Settings',
    href: route('profile.edit'),
    icon: Settings,
    current: page.url.startsWith('/settings'),
  },
  {
    name: 'Help & Support',
    href: '/help',
    icon: HelpCircle,
    current: page.url.startsWith('/help'),
  },
];

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
  sidebarOpen.value = false;
};

const logout = () => {
  router.post(route('logout'));
};

const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.get('/search', { q: searchQuery.value.trim() });
  }
};

const toggleTheme = (theme: 'light' | 'dark' | 'system') => {
  updateAppearance(theme);
};

// Handle escape key to close sidebar
let handleEscape: ((e: KeyboardEvent) => void) | null = null;

onMounted(() => {
  handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      closeSidebar();
    }
  };
  document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
  if (handleEscape) {
    document.removeEventListener('keydown', handleEscape);
  }
});

// Format page title
const pageTitle = computed(() => {
  if (props.title) return props.title;
  if (props.breadcrumbs?.length) {
    return props.breadcrumbs[props.breadcrumbs.length - 1].title;
  }
  return 'Dashboard';
});
</script>

<template>
  <div class="h-full">
    <Head :title="pageTitle" />

    <TooltipProvider>
      <!-- Mobile sidebar overlay -->
      <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden" @click="closeSidebar">
        <div class="fixed inset-0 bg-gray-600/75"></div>
      </div>

      <!-- Sidebar -->
      <div
        :class="[
          'fixed inset-y-0 left-0 z-50 w-64 transform border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0 dark:border-gray-700 dark:bg-gray-900',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
      >
        <div class="flex h-full flex-col">
          <!-- Logo/Brand -->
          <div class="flex h-16 items-center justify-between border-b border-gray-200 px-6 dark:border-gray-700">
            <Link :href="route('dashboard')" class="flex items-center">
              <div class="mr-3 flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600">
                <FileText class="h-5 w-5 text-white" />
              </div>
              <span class="text-xl font-bold text-gray-900 dark:text-white">InvoiceHub</span>
            </Link>
            <Button variant="ghost" size="icon" class="lg:hidden" @click="closeSidebar" aria-label="Close navigation menu">
              <X class="h-5 w-5" />
            </Button>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
              <!-- Primary Navigation -->
              <div v-for="item in navigation" :key="item.name">
                <!-- Simple nav item -->
                <Link
                  v-if="!item.children"
                  :href="item.href"
                  :class="[
                    'group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    item.current
                      ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300'
                      : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800',
                  ]"
                  @click="closeSidebar"
                >
                  <component
                    :is="item.icon"
                    :class="[
                      'mr-3 h-5 w-5 flex-shrink-0',
                      item.current ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                    ]"
                  />
                  {{ item.name }}
                </Link>

                <!-- Nav item with children -->
                <div v-else>
                  <div
                    :class="[
                      'group flex cursor-pointer items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                      item.current
                        ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300'
                        : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800',
                    ]"
                  >
                    <component
                      :is="item.icon"
                      :class="['mr-3 h-5 w-5 flex-shrink-0', item.current ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500']"
                    />
                    {{ item.name }}
                    <ChevronDown :class="['ml-auto h-4 w-4 transform transition-transform', item.current ? 'rotate-180' : '']" />
                  </div>

                  <!-- Submenu -->
                  <div v-if="item.current" class="mt-2 ml-8 space-y-1">
                    <Link
                      v-for="child in item.children"
                      :key="child.name"
                      :href="child.href"
                      class="block rounded-md px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                      @click="closeSidebar"
                    >
                      {{ child.name }}
                    </Link>
                  </div>
                </div>
              </div>

              <!-- Divider -->
              <div class="my-6 border-t border-gray-200 dark:border-gray-700"></div>

              <!-- Secondary Navigation -->
              <div v-for="item in secondaryNavigation" :key="item.name">
                <Link
                  :href="item.href"
                  :class="[
                    'group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    item.current
                      ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300'
                      : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800',
                  ]"
                  @click="closeSidebar"
                >
                  <component
                    :is="item.icon"
                    :class="['mr-3 h-5 w-5 flex-shrink-0', item.current ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500']"
                  />
                  {{ item.name }}
                </Link>
              </div>
            </div>
          </nav>

          <!-- User menu in sidebar footer -->
          <div class="border-t border-gray-200 p-4 dark:border-gray-700">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-auto w-full justify-start px-3 py-2">
                  <div class="flex items-center">
                    <div class="mr-3 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                      <User class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div class="min-w-0 flex-1 text-left">
                      <div class="truncate text-sm font-medium text-gray-900 dark:text-white">
                        {{ user?.name }}
                      </div>
                      <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ user?.email }}
                      </div>
                    </div>
                  </div>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="start" class="w-56">
                <DropdownMenuItem as-child>
                  <Link :href="route('profile.edit')">
                    <User class="mr-2 h-4 w-4" />
                    Profile
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem as-child>
                  <Link :href="route('profile.edit')">
                    <Settings class="mr-2 h-4 w-4" />
                    Settings
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="logout">
                  <LogOut class="mr-2 h-4 w-4" />
                  Sign out
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <div class="lg:pl-64">
        <!-- Top header -->
        <div class="sticky top-0 z-40 border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
          <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
              <!-- Mobile menu button -->
              <Button variant="ghost" size="icon" class="mr-2 lg:hidden" @click="toggleSidebar" aria-label="Open navigation menu">
                <Menu class="h-5 w-5" />
              </Button>

              <!-- Breadcrumbs -->
              <nav v-if="breadcrumbs?.length" class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                  <li v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.title">
                    <div class="flex items-center">
                      <span v-if="index > 0" class="mx-2 text-gray-400" aria-hidden="true"> / </span>
                      <Link
                        v-if="breadcrumb.href && index < breadcrumbs.length - 1"
                        :href="breadcrumb.href"
                        class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                      >
                        {{ breadcrumb.title }}
                      </Link>
                      <span v-else class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ breadcrumb.title }}
                      </span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 v-else class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ pageTitle }}
              </h1>
            </div>

            <div class="flex items-center space-x-4">
              <!-- Search -->
              <div class="hidden sm:block">
                <div class="relative">
                  <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                  <Input v-model="searchQuery" type="search" placeholder="Search invoices..." class="w-64 pl-9" @keydown.enter="performSearch" />
                </div>
              </div>

              <!-- Theme toggle -->
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="icon" aria-label="Toggle theme">
                    <Sun v-if="appearance === 'light'" class="h-5 w-5" />
                    <Moon v-else-if="appearance === 'dark'" class="h-5 w-5" />
                    <Monitor v-else class="h-5 w-5" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="toggleTheme('light')">
                    <Sun class="mr-2 h-4 w-4" />
                    Light
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="toggleTheme('dark')">
                    <Moon class="mr-2 h-4 w-4" />
                    Dark
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="toggleTheme('system')">
                    <Monitor class="mr-2 h-4 w-4" />
                    System
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>

              <!-- Notifications -->
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button variant="ghost" size="icon" class="relative">
                    <Bell class="h-5 w-5" />
                    <Badge
                      v-if="notificationCount > 0"
                      class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center bg-red-500 p-0 text-xs"
                    >
                      {{ notificationCount }}
                    </Badge>
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Notifications</p>
                </TooltipContent>
              </Tooltip>

              <!-- User menu -->
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" class="relative h-8 w-8 rounded-full" aria-label="User menu">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                      <User class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <div class="px-2 py-1.5 text-sm text-gray-900 dark:text-white">
                    <div class="font-medium">{{ user?.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</div>
                  </div>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem as-child>
                    <Link :href="route('profile.edit')">
                      <User class="mr-2 h-4 w-4" />
                      Profile
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem as-child>
                    <Link :href="route('profile.edit')">
                      <Settings class="mr-2 h-4 w-4" />
                      Settings
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="logout">
                    <LogOut class="mr-2 h-4 w-4" />
                    Sign out
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </div>

        <!-- Page content -->
        <main class="flex-1 pb-8">
          <slot />
        </main>
      </div>
    </TooltipProvider>
  </div>
</template>

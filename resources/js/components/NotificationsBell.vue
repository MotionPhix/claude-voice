<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import { Badge } from '@/components/ui/badge'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Bell, X, Check, CheckCheck } from 'lucide-vue-next'

interface NotificationItem {
  id: number
  type: string
  level: string
  title: string
  message: string
  data: any
  icon: string
  level_color: string
  notifiable_type?: string
  notifiable_id?: number
  is_read: boolean
  is_dismissed: boolean
  created_at: string
  read_at?: string
}

// State
const notifications = ref<NotificationItem[]>([])
const unreadCount = ref(0)
const loading = ref(false)
const isOpen = ref(false)

// Get current user
const page = usePage()
const auth = computed(() => (page.props as any).auth)

// CSRF token helper
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

// Route helper (assuming Ziggy is available)
const route = (name: string, params?: Record<string, any>) => 
  (window as any).route?.(name, params) || '#'

// API endpoints
const listUrl = route('api.notifications.index')
const markReadUrl = (id: number) => route('api.notifications.mark-read', { notification: id })
const dismissUrl = (id: number) => route('api.notifications.dismiss', { notification: id })
const markAllUrl = route('api.notifications.mark-all-read')
const unreadCountUrl = route('api.notifications.unread-count')

// Fetch notifications from API
const fetchNotifications = async () => {
  loading.value = true
  try {
    const response = await fetch(listUrl, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const data = await response.json()
      notifications.value = data.notifications || []
      unreadCount.value = data.unread_count || 0
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    loading.value = false
  }
}

// Mark notification as read
const markAsRead = async (notification: NotificationItem) => {
  if (notification.is_read) return
  
  try {
    const response = await fetch(markReadUrl(notification.id), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf(),
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      // Update local state
      const index = notifications.value.findIndex(n => n.id === notification.id)
      if (index !== -1) {
        notifications.value[index].is_read = true
        notifications.value[index].read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    }
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

// Dismiss notification
const dismiss = async (notification: NotificationItem) => {
  try {
    const response = await fetch(dismissUrl(notification.id), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf(),
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      // Remove from local state
      const index = notifications.value.findIndex(n => n.id === notification.id)
      if (index !== -1) {
        if (!notifications.value[index].is_read) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        notifications.value.splice(index, 1)
      }
    }
  } catch (error) {
    console.error('Failed to dismiss notification:', error)
  }
}

// Mark all as read
const markAllAsRead = async () => {
  try {
    const response = await fetch(markAllUrl, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf(),
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      // Update local state
      notifications.value = notifications.value.map(n => ({
        ...n,
        is_read: true,
        read_at: new Date().toISOString()
      }))
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

// Real-time notifications setup (if Echo is available)
const setupRealTimeNotifications = () => {
  const userId = auth.value?.user?.id
  
  if (userId && (window as any).Echo) {
    // Listen for new notifications
    (window as any).Echo.private(`user.${userId}`)
      .listen('.SystemNotificationCreated', (event: any) => {
        const newNotification: NotificationItem = {
          id: event.id,
          type: event.type,
          level: event.level,
          title: event.title,
          message: event.message,
          data: event.data,
          icon: event.icon,
          level_color: event.level_color,
          notifiable_type: event.notifiable_type,
          notifiable_id: event.notifiable_id,
          is_read: false,
          is_dismissed: false,
          created_at: event.created_at || new Date().toISOString(),
        }
        
        // Add to top of notifications list
        notifications.value.unshift(newNotification)
        unreadCount.value += 1
      })
  }
}

// Format relative time
const formatRelativeTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.abs(now.getTime() - date.getTime()) / (1000 * 60 * 60)
  
  if (diffInHours < 1) {
    const diffInMinutes = Math.floor(diffInHours * 60)
    return `${diffInMinutes}m ago`
  } else if (diffInHours < 24) {
    return `${Math.floor(diffInHours)}h ago`
  } else {
    const diffInDays = Math.floor(diffInHours / 24)
    return `${diffInDays}d ago`
  }
}

// Get icon component based on notification type
const getIcon = (iconName: string) => {
  // You can expand this based on available icons
  switch (iconName) {
    case 'check-circle': return CheckCheck
    case 'alert-triangle': return Bell
    default: return Bell
  }
}

// Lifecycle
onMounted(() => {
  fetchNotifications()
  setupRealTimeNotifications()
})

onUnmounted(() => {
  // Clean up Echo listeners if needed
  const userId = auth.value?.user?.id
  if (userId && (window as any).Echo) {
    (window as any).Echo.leave(`user.${userId}`)
  }
})
</script>

<template>
  <DropdownMenu v-model:open="isOpen">
    <DropdownMenuTrigger asChild>
      <Button 
        variant="ghost" 
        size="icon" 
        class="relative"
        :class="{ 'text-blue-600': unreadCount > 0 }"
      >
        <Bell class="h-5 w-5" />
        <Badge 
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs"
          variant="destructive"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </Badge>
      </Button>
    </DropdownMenuTrigger>
    
    <DropdownMenuContent class="w-96 p-0" align="end">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="font-semibold">Notifications</h3>
        <Button 
          v-if="unreadCount > 0"
          variant="ghost" 
          size="sm"
          @click="markAllAsRead"
          class="text-sm text-blue-600 hover:text-blue-700"
        >
          Mark all read
        </Button>
      </div>
      
      <!-- Loading state -->
      <div v-if="loading" class="p-4 text-center text-muted-foreground">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
        Loading notifications...
      </div>
      
      <!-- Empty state -->
      <div v-else-if="notifications.length === 0" class="p-8 text-center text-muted-foreground">
        <Bell class="h-12 w-12 mx-auto mb-2 text-muted-foreground/50" />
        <p class="text-sm">No notifications yet</p>
      </div>
      
      <!-- Notifications list -->
      <ScrollArea v-else class="max-h-96">
        <div class="p-2">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="flex items-start gap-3 p-3 rounded-lg hover:bg-muted/50 cursor-pointer relative group"
            :class="{ 'bg-blue-50/50': !notification.is_read }"
            @click="markAsRead(notification)"
          >
            <!-- Icon -->
            <div 
              class="rounded-full p-2 mt-0.5"
              :class="notification.level_color"
            >
              <component 
                :is="getIcon(notification.icon)" 
                class="h-4 w-4" 
              />
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <h4 class="text-sm font-medium leading-none">
                  {{ notification.title }}
                </h4>
                <div class="flex items-center gap-1">
                  <span class="text-xs text-muted-foreground whitespace-nowrap">
                    {{ formatRelativeTime(notification.created_at) }}
                  </span>
                  <!-- Unread indicator -->
                  <div 
                    v-if="!notification.is_read"
                    class="w-2 h-2 bg-blue-600 rounded-full"
                  ></div>
                </div>
              </div>
              
              <p class="text-sm text-muted-foreground mt-1 line-clamp-2">
                {{ notification.message }}
              </p>
            </div>
            
            <!-- Dismiss button -->
            <Button
              variant="ghost"
              size="icon"
              class="h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
              @click.stop="dismiss(notification)"
            >
              <X class="h-3 w-3" />
            </Button>
          </div>
        </div>
      </ScrollArea>
      
      <!-- Footer -->
      <DropdownMenuSeparator />
      <div class="p-2">
        <Button 
          variant="ghost" 
          class="w-full justify-center text-sm"
          @click="$router?.push('/notifications') || (window.location.href = route('notifications.index'))"
        >
          View all notifications
        </Button>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

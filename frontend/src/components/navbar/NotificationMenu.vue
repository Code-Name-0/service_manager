<template>
  <div v-if="authStore.isLoggedIn && canReceiveNotifications" class="mr-3">
    <v-menu
      v-model="notificationMenu"
      :close-on-content-click="false"
      location="bottom end"
      origin="top end"
      offset="10"
      max-width="400"
    >
      <template v-slot:activator="{ props }">
        <v-btn
          icon
          size="small"
          v-bind="props"
          :color="notificationsStore.hasUnreadNotifications ? 'warning' : 'default'"
        >
          <v-badge
            :content="notificationsStore.unreadCount"
            :model-value="notificationsStore.hasUnreadNotifications"
            color="error"
            offset-x="2"
            offset-y="2"
          >
            <v-icon>mdi-bell</v-icon>
          </v-badge>
        </v-btn>
      </template>

      <v-card min-width="350" max-width="400" max-height="500">
        <v-card-title class="d-flex align-center justify-space-between pa-4">
          <span class="text-h6">Notifications</span>
          <v-btn
            v-if="notificationsStore.hasUnreadNotifications"
            variant="text"
            size="small"
            color="primary"
            @click="markAllNotificationsAsRead"
          >
            Mark all read
          </v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <v-card-text class="pa-0" style="max-height: 400px; overflow-y: auto;">
          <div v-if="notificationsStore.notifications.length === 0" class="text-center pa-4 text-grey">
            <v-icon size="48" class="mb-2">mdi-bell-outline</v-icon>
            <p>No notifications yet</p>
          </div>

          <v-list v-else class="pa-0">
            <v-list-item
              v-for="notification in notificationsStore.latestNotifications"
              :key="notification.id"
              :class="{ 'bg-blue-lighten-5': !notification.read }"
              @click="onNotificationClick(notification)"
            >
              <template v-slot:prepend>
                <v-avatar 
                  :color="notification.read ? 'grey-lighten-1' : 'primary'"
                  size="40"
                >
                  <v-icon 
                    :color="notification.read ? 'grey-darken-1' : 'white'"
                    size="20"
                  >
                    {{ getNotificationIcon(notification.type) }}
                  </v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="text-wrap">
                {{ notification.title }}
              </v-list-item-title>
              
              <v-list-item-subtitle class="text-wrap mt-1">
                {{ notification.message }}
              </v-list-item-subtitle>

              <v-list-item-subtitle class="mt-2">
                <small>{{ formatNotificationTime(notification.created_at) }}</small>
              </v-list-item-subtitle>

              <template v-slot:append>
                <v-chip
                  v-if="!notification.read"
                  color="primary"
                  size="x-small"
                  variant="flat"
                >
                  New
                </v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>

        <v-divider v-if="notificationsStore.notifications.length > 0"></v-divider>
      </v-card>
    </v-menu>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'

export default {
  name: 'NotificationMenu',
  
  emits: ['notification-click'],

  setup() {
    const authStore = useAuthStore()
    const notificationsStore = useNotificationsStore()
    return { authStore, notificationsStore }
  },

  data() {
    return {
      notificationMenu: false
    }
  },

  computed: {
    canReceiveNotifications() {
      return this.authStore.hasPermission('manage_requests') || this.authStore.hasPermission('super_admin')
    }
  },

  methods: {
    onNotificationClick(notification) {
      console.log('Notification clicked:', notification)
      this.markNotificationAsRead(notification.id)
      this.$emit('notification-click', notification)
    },

    markNotificationAsRead(notificationId) {
      this.notificationsStore.markAsRead(notificationId)
    },

    markAllNotificationsAsRead() {
      this.notificationsStore.markAllAsRead()
      this.notificationMenu = false
    },

    getNotificationIcon(type) {
      switch (type) {
        case 'service_request':
          return 'mdi-email-plus'
        default:
          return 'mdi-information'
      }
    },

    formatNotificationTime(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diffInMinutes = Math.floor((now - date) / (1000 * 60))
      
      if (diffInMinutes < 1) {
        return 'Just now'
      } else if (diffInMinutes < 60) {
        return `${diffInMinutes}m ago`
      } else if (diffInMinutes < 1440) {
        const hours = Math.floor(diffInMinutes / 60)
        return `${hours}h ago`
      } else {
        const days = Math.floor(diffInMinutes / 1440)
        return `${days}d ago`
      }
    }
  }
}
</script>

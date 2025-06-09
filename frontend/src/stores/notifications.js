import { defineStore } from 'pinia'
import { useAuthStore } from './auth'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    isListening: false
  }),

  getters: {
    unreadNotifications: (state) => state.notifications.filter(n => !n.read),
    latestNotifications: (state) => state.notifications.slice(0, 5),
    allNotifications: (state) => state.notifications,
    hasUnreadNotifications: (state) => state.unreadCount > 0,
    hasMoreThanFive: (state) => state.notifications.length > 5
  },

  actions: {
    // Start listening for real-time notifications
    startListening() {
      if (this.isListening) return

      const authStore = useAuthStore()
      
      // Only listen if admin has the right permissions
      if (!authStore.hasPermission('manage_requests') && !authStore.hasPermission('super_admin')) {
        return
      }

      console.log('Starting to listen for notifications...')

      window.Echo.channel('admin-notifications')
        .listen('.new.service.request', (event) => {
          console.log('New service request notification received:', event)
          this.addNotification({
            id: Date.now(),
            type: 'service_request',
            title: 'New Service Request',
            message: `New request from ${event.client_name} for ${event.service_name}`,
            data: event,
            read: false,
            created_at: new Date().toISOString()
          })
        })

      this.isListening = true
    },

    // Stop listening for notifications
    stopListening() {
      if (!this.isListening) return
      
      console.log('Stopping notification listener...')
      window.Echo.leaveChannel('admin-notifications')
      this.isListening = false
    },

    // Add a new notification
    addNotification(notification) {
      this.notifications.unshift(notification)
      this.unreadCount++

      // Keep only latest 50 notifications
      if (this.notifications.length > 50) {
        this.notifications = this.notifications.slice(0, 50)
      }

      console.log('Notification added:', notification)
    },

    // Mark notification as read
    markAsRead(notificationId) {
      const notification = this.notifications.find(n => n.id === notificationId)
      if (notification && !notification.read) {
        notification.read = true
        this.unreadCount = Math.max(0, this.unreadCount - 1)
      }
    },

    // Mark all notifications as read
    markAllAsRead() {
      this.notifications.forEach(notification => {
        notification.read = true
      })
      this.unreadCount = 0
    },

    // Clear all notifications
    clearAll() {
      this.notifications = []
      this.unreadCount = 0
    },

    // Initialize notifications when user logs in
    initialize() {
      this.startListening()
    },

    // Cleanup when user logs out
    cleanup() {
      this.stopListening()
      this.clearAll()
    }
  }
})

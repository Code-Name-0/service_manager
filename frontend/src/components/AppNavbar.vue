<template>
  <v-app-bar
    elevation="10"
    color="#ffffff"
    height="65"
    app
  >
    <v-container fluid class="pa-10">
      <div class="d-flex align-center justify-space-between ">
        <!-- Logo -->
        <div class="d-flex align-center">
          <img 
            src="@/assets/logo.jpg" 
            alt="Originova Logo" 
            class="logo-image"
            style="height: 40px; width: auto; cursor: pointer;  border-radius: 50%;"
            @click="goHome"
          />
        </div>

        <div class="d-flex align-center">


          <NavLinks 
            :current-view="this.$route.query.view"
            @navigate="navigateToView"
          />

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

          <template v-if="authStore.isLoggedIn">
            <v-chip
              v-if="authStore.adminName"
              color="primary"
              variant="tonal"
              size="small"
              class="mx-2 d-none d-sm-flex"
            >
              {{ authStore.adminName }}
            </v-chip>
            


            <v-btn
              color="error"
              variant="outlined"
              rounded="pill"
              size="small"
              class="ml-2"
              @click="handleLogout"
              :loading="authStore.loading"
            >
              <v-icon v-if="$vuetify.display.xs" size="small">mdi-logout</v-icon>
              <template v-if="$vuetify.display.smAndUp">
                <v-icon size="small" class="mr-1">mdi-logout</v-icon>
                Logout
              </template>
            </v-btn>
          </template>

          <template v-else>
            <v-btn
              color="primary"
              variant="outlined"
              rounded="pill"
              size="small"
              @click="handleLogin"
              :loading="authStore.loading"
            >
              <v-icon v-if="$vuetify.display.xs" size="small">mdi-login</v-icon>
              <template v-if="$vuetify.display.smAndUp">
                <v-icon size="small" class="mr-1">mdi-login</v-icon>
                Login
              </template>
            </v-btn>
          </template>
        </div>
      </div>
    </v-container>
  </v-app-bar>

  <v-dialog v-model="loginModal" max-width="400" persistent>
    <v-card>
      <v-card-title class="text-h5 text-center pa-6">
        <img 
              src="@/assets/logo.jpg" 
              alt="Originova Logo" 
              class="logo-image"
              style="height: 40px; width: 40px;   border-radius: 50%;"
              @click="goHome"
            />
            <br>
        Admin Login
      </v-card-title>
      <v-card-text class="px-6">
        <v-form ref="loginForm" v-model="formValid" @submit.prevent="submitLogin">
          <v-text-field
            v-model="loginForm.email"
            label="Email"
            type="email"
            variant="outlined"
            prepend-inner-icon="mdi-email"
            :rules="emailRules"
            required
            class="mb-3"
          ></v-text-field>
          
          <v-text-field
            v-model="loginForm.password"
            label="Password"
            :type="showPassword ? 'text' : 'password'"
            variant="outlined"
            prepend-inner-icon="mdi-lock"
            :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            @click:append-inner="showPassword = !showPassword"
            :rules="passwordRules"
            required
            class="mb-3"
          ></v-text-field>

          <v-alert
            v-if="authStore.error"
            type="error"
            variant="tonal"
            class="mb-3"
          >
            {{ authStore.error }}
          </v-alert>
          <button type="submit" style="display: none;" @click="submitLogin"></button>
        </v-form>
      </v-card-text>

      <v-card-actions class="px-6 pb-6 d-flex flex-column">
        <v-btn
          color="primary"
          variant="flat"
          @click="submitLogin"
          :loading="authStore.loading"
          :disabled="!formValid"
          block
          class="mb-2"
        >
          Login
        </v-btn>
        <v-btn
          variant="outlined"
          @click="closeLoginModal"
          :disabled="authStore.loading"
          block
        >
          Cancel
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import NavLinks from '@/components/navbar/NavLinks.vue'

export default {
  name: 'AppNavbar',
  
  components: {
    NavLinks
  },
  
  setup() {
    
    const authStore = useAuthStore()
    const notificationsStore = useNotificationsStore()
    return { authStore, notificationsStore }
  },

  data() {
    return {
      loginModal: false,
      showPassword: false,
      formValid: false,
      notificationMenu: false,
      loginForm: {
        email: '',
        password: ''
      },
      emailRules: [
        v => !!v || 'Email is required',
        v => /.+@.+\..+/.test(v) || 'Email must be valid'
      ],
      passwordRules: [
        v => !!v || 'Password is required',
        v => v.length >= 6 || 'Password must be at least 6 characters'
      ]
    }
  },

  computed: {
    canReceiveNotifications() {
      return this.authStore.hasPermission('manage_requests') || this.authStore.hasPermission('super_admin')
    }
  },


  
  methods: {
    navigateToView(viewType) {
      console.log('Navigating to view:', viewType)
      console.log('Current route:', this.$route.path, this.$route.query)
     
      this.$router.push({ path: '/dashboard', query: { view: viewType } })
      console.log('Navigation pushed')
    },
    onNotificationClick(notification) {
      console.log('Notification clicked:', notification)
      this.markNotificationAsRead(notification.id)
      this.$router.push({ path: '/dashboard', query: { view: 'request_manager' } })
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
    },



    handleLogin() {
      console.log('Login clicked')
      this.loginModal = true
    },

    async submitLogin() {
      if (!this.formValid) return

      try {
        await this.authStore.login({
          email: this.loginForm.email,
          password: this.loginForm.password
        })
        
        this.closeLoginModal()
        
        this.$router.push('/dashboard')
        
      } catch (error) {
        console.error('Login failed:', error)
      }
    },

    closeLoginModal() {
      this.loginModal = false
      this.loginForm.email = ''
      this.loginForm.password = ''
      this.showPassword = false
      this.authStore.error = null
    },

    async handleLogout() {
      console.log('Logout clicked')
      await this.authStore.logout()
      this.$router.push('/')
    },

    goHome() {
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>
.logo-image {
  transition: transform 0.2s ease;
}

.logo-image:hover {
  transform: scale(1.05);
}
</style>

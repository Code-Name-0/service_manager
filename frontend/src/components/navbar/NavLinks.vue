<template>
  <div v-if="authStore.isLoggedIn" class="d-flex align-center mr-5">
    <v-btn
      v-if="authStore.hasPermission('super_admin')"
      variant="text" 
      size="small"
      :color="currentView === 'super_admin' ? 'info' : 'default'"
      @click="$emit('navigate', 'super_admin')"
      class="mr-2"
    >
      <v-icon size="small" class="mr-1">mdi-shield-crown</v-icon>
      <span class="d-none d-md-inline">Admin Management</span>
    </v-btn>

    <v-btn
      v-if="authStore.hasPermission('manage_services')"
      variant="text"
      size="small"
      @click="$emit('navigate', 'service_manager')"
      class="mr-2"
      :color="currentView === 'service_manager' ? 'info' : 'default'"
    >
      <v-icon size="small" class="mr-1">mdi-cog</v-icon>
      <span class="d-none d-md-inline">Service Management</span>
    </v-btn>

    <v-btn
      v-if="authStore.hasPermission('manage_requests')"
      variant="text"
      size="small"
      @click="$emit('navigate', 'request_manager')"
      class="mr-2"
      :color="currentView === 'request_manager' ? 'info' : 'default'"
    >
      <v-icon size="small" class="mr-1">mdi-email</v-icon>
      <span class="d-none d-md-inline">Request Management</span>
    </v-btn>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'NavLinks',
  
  emits: ['navigate'],
  
  props: {
    currentView: {
      type: String,
      default: null
    }
  },

  setup() {
    const authStore = useAuthStore()
    return { authStore }
  }
}
</script>

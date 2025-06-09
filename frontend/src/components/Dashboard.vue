<template>
  <div>
    <AppNavbar />
    <div class="main-content bg-blue-grey-50 px-4 pb-4 min-h-screen">
      <v-container fluid class="pa-4">
        <div class="d-flex align-center mb-6">
          <v-icon icon="mdi-view-dashboard" size="large" color="primary" class="mr-3"></v-icon>
          <h1 class="text-h3 font-weight-bold">Dashboard</h1>
        </div>

        
        <ServiceManagerDashboard v-if="currentView === 'service_manager'" />
        <RequestManagerDashboard v-else-if="currentView === 'request_manager'" />
        <SuperAdminDashboard v-else-if="currentView === 'super_admin'" />

        <div v-else class="text-center py-12">
          <v-icon icon="mdi-account-question" size="80" color="grey-lighten-1" class="mb-4"></v-icon>
          <h2 class="text-h5 text-grey-darken-1 mb-3">Dashboard Not Available</h2>
          <p class="text-body-1 text-grey">No dashboard available for your role.</p>
        </div>
      </v-container>
    </div>
  </div>
</template>

<script>
import AppNavbar from './AppNavbar.vue'
import ServiceManagerDashboard from './dashboards/ServiceManagerDashboard.vue'
import RequestManagerDashboard from './dashboards/RequestManagerDashboard.vue'
import SuperAdminDashboard from './dashboards/SuperAdminDashboard.vue'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'AdminDashboard',
  
  components: {
    AppNavbar,
    ServiceManagerDashboard,
    RequestManagerDashboard,
    SuperAdminDashboard
  },

  setup() {
    const authStore = useAuthStore()
    return {
      authStore
    }
  },

  computed: {
    userRole() {
      const role = this.authStore.adminRole
      console.log('Current admin:', this.authStore.admin)
      console.log('Admin permissions:', this.authStore.adminPermissions)
      console.log('Detected role:', role)
      return role
    },

    currentView() {
      // Get the view from query parameter
      const queryView = this.$route.query.view
      console.log('Dashboard currentView computed - Query view:', queryView)
      console.log('Dashboard currentView computed - User permissions:', this.authStore.adminPermissions)
      
      // validating m3a l admin permission
      if (queryView) {
        if (queryView === 'super_admin' && this.authStore.hasPermission('super_admin')) {
          console.log('Dashboard returning super_admin view')
          return queryView
        } else if (queryView === 'service_manager' && this.authStore.hasPermission('manage_services')) {
          console.log('Dashboard returning service_manager view')
          return queryView
        } else if (queryView === 'request_manager' && this.authStore.hasPermission('manage_requests')) {
          console.log('Dashboard returning request_manager view')
          return queryView
        }
      }
      
      console.log('Dashboard returning default userRole:', this.userRole)
      return this.userRole
    }
  }
}
</script>

<style scoped>
.main-content {
  padding-top: 5rem;
}

.v-data-table {
  border-radius: 5rem;
}
</style>

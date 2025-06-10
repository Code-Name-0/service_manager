<template>
  <div>
    <div class="request-manager-content">
      <v-container fluid class="pa-4">
        <div class="d-flex align-center mb-6">
          <v-icon icon="mdi-email-outline" size="large" color="primary" class="mr-3"></v-icon>
          <h1 class="text-h3 font-weight-bold">Service Requests</h1>
        </div>
        
        <v-row class="mb-6">
          <v-col cols="12" md="3">
            <StatCard 
              title="Total Requests"
              :value="stats.totalRequests"
              icon="mdi-email"
              icon-color="primary"
              value-color="text-primary"
            />
          </v-col>
          <v-col cols="12" md="3">
            <StatCard 
              title="Pending"
              :value="stats.pendingRequests"
              icon="mdi-clock-outline"
              icon-color="warning"
              value-color="text-warning"
            />
          </v-col>
          <v-col cols="12" md="3">
            <StatCard 
              title="Approved"
              :value="stats.approvedRequests"
              icon="mdi-check-circle"
              icon-color="success"
              value-color="text-success"
            />
          </v-col>
          <v-col cols="12" md="3">
            <StatCard 
              title="Completed"
              :value="stats.completedRequests"
              icon="mdi-check-all"
              icon-color="info"
              value-color="text-info"
            />
          </v-col>
        </v-row>

        <v-card elevation="2" rounded="lg" class="pa-6">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-bold">
              <v-icon icon="mdi-email" class="mr-2"></v-icon>
              Service Requests
            </h2>
            <v-btn-group>
              <v-btn
                :color="statusFilter === 'all' ? 'primary' : 'default'"
                @click="statusFilter = 'all'"
                size="small"
              >
                All
              </v-btn>
              <v-btn
                :color="statusFilter === 'pending' ? 'warning' : 'default'"
                @click="statusFilter = 'pending'"
                size="small"
              >
                Pending
              </v-btn>
              <v-btn
                :color="statusFilter === 'approved' ? 'success' : 'default'"
                @click="statusFilter = 'approved'"
                size="small"
              >
                Approved
              </v-btn>
              <v-btn
                :color="statusFilter === 'completed' ? 'info' : 'default'"
                @click="statusFilter = 'completed'"
                size="small"
              >
                Completed
              </v-btn>
            </v-btn-group>
          </div>

          <v-text-field
            v-model="search"
            label="Search requests..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            class="mb-4"
          ></v-text-field>

          <v-data-table-server
            :headers="headers"
            :items="requests"
            :loading="loading"
            :items-length="totalItems"
            :items-per-page="itemsPerPage"
            :items-per-page-options="[10, 25, 50, 100]"
            @update:options="loadItems"
            class="elevation-10"
            item-value="id"
          >
            <template v-slot:[`item.client_name`]="{ item }">
              <div>
                <div class="font-weight-medium">{{ item.client_name }}</div>
                <div class="text-caption text-medium-emphasis">{{ item.client_email }}</div>
              </div>
            </template>

            <template v-slot:[`item.service`]="{ item }">
              <v-chip color="primary" size="small" variant="outlined">
                {{ item.service?.name || 'Unknown Service' }}
              </v-chip>
            </template>
            
            <template v-slot:[`item.status`]="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                variant="flat"
              >
                {{ getStatusText(item.status) }}
              </v-chip>
            </template>

            <template v-slot:[`item.created_at`]="{ item }">
              <div class="text-caption">
                {{ formatDate(item.created_at) }}
              </div>
            </template>
            
            <template v-slot:[`item.actions`]="{ item }">
              <v-btn
                v-if="item.status === 'pending'"
                icon
                size="small"
                color="success"
                @click="approveRequest(item)"
                class="mr-1"
                title="Approve Request"
              >
                <v-icon>mdi-check</v-icon>
              </v-btn>
              <v-btn
                v-if="item.status === 'pending'"
                icon
                size="small"
                color="error"
                @click="denyRequest(item)"
                class="mr-1"
                title="Deny Request"
              >
                <v-icon>mdi-close</v-icon>
              </v-btn>
              <v-btn
                v-if="item.status === 'approved'"
                icon
                size="small"
                color="info"
                @click="completeRequest(item)"
                class="mr-1"
                title="Mark as Completed"
              >
                <v-icon>mdi-check-all</v-icon>
              </v-btn>
              <v-btn
                icon
                size="small"
                color="primary"
                @click="viewRequest(item)"
                title="View Details"
              >
                <v-icon>mdi-eye</v-icon>
              </v-btn>
            </template>
          </v-data-table-server>
        </v-card>
      </v-container>
    </div>

    <v-dialog v-model="requestDialog" max-width="700px" persistent>
      <v-card>
        <v-card-title>
          <span class="text-h5">Request Details</span>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text>
          <v-container v-if="selectedRequest">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  :model-value="selectedRequest.client_name"
                  label="Client Name"
                  variant="outlined"
                  readonly
                  prepend-inner-icon="mdi-account"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  :model-value="selectedRequest.client_email"
                  label="Client Email"
                  prepend-inner-icon="mdi-email"
                  variant="outlined"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  :model-value="selectedRequest.client_phone"
                  label="Client Phone"
                  prepend-inner-icon="mdi-phone"
                  variant="outlined"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  :model-value="selectedRequest.service?.name"
                  label="Service"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-wrench"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  :model-value="selectedRequest.message"
                  label="Client Message"
                  prepend-inner-icon="mdi-message"
                  variant="outlined"
                  rows="3"
                  readonly
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="adminResponse"
                  label="Admin Response"
                  prepend-inner-icon="mdi-note-edit"
                  variant="outlined"
                  rows="3"
                  placeholder="Add your response to the client..."
                ></v-textarea>
              </v-col>
              <v-col cols="12">
                <v-card variant="outlined" class="pa-3">
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <h4 class="text-subtitle-1 font-weight-medium mb-1">Request Status</h4>
                      <p class="text-caption text-medium-emphasis mb-0">
                        Current status: {{ getStatusText(selectedRequest.status) }}
                      </p>
                    </div>
                    <v-chip
                      :color="getStatusColor(selectedRequest.status)"
                      size="small"
                      variant="flat"
                    >
                      {{ getStatusText(selectedRequest.status) }}
                    </v-chip>
                  </div>
                </v-card>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="closeRequestDialog"
          >
            Close
          </v-btn>
          <v-btn
            v-if="selectedRequest?.status === 'pending'"
            color="error"
            variant="flat"
            @click="updateRequestStatus('deny')"
            :loading="submitting"
          >
            Deny
          </v-btn>
          <v-btn
            v-if="selectedRequest?.status === 'pending'"
            color="success"
            variant="flat"
            @click="updateRequestStatus('approve')"
            :loading="submitting"
          >
            Approve
          </v-btn>
          <v-btn
            v-if="selectedRequest?.status === 'approved'"
            color="info"
            variant="flat"
            @click="updateRequestStatus('complete')"
            :loading="submitting"
          >
            Mark as Completed
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    

    <v-snackbar
      v-model="notificationSnackbar.show"
      color="info"
      :timeout="5000"
      location="top right"
      class="notification-snackbar"
    >
      <div class="d-flex align-center">
        <v-icon class="mr-2">mdi-bell-ring</v-icon>
        <div>
          <div class="font-weight-medium">{{ notificationSnackbar.title }}</div>
          <div class="text-caption">{{ notificationSnackbar.message }}</div>
        </div>
      </div>
      <template v-slot:actions>
        <v-btn
          variant="text"
          size="small"
          @click="markNotificationAsRead"
        >
          Mark as Read
        </v-btn>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="notificationSnackbar.show = false"
        ></v-btn>
      </template>
    </v-snackbar>


    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
    >
      {{ snackbar.message }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="snackbar.show = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import StatCard from '@/components/common/StatCard.vue'

import { watchDebounced } from '@vueuse/core'


export default {

  name: 'RequestManagerDashboard',

  components: {
    StatCard
  },


  setup() {

    const authStore = useAuthStore()
    const notificationsStore = useNotificationsStore()

    return { authStore, notificationsStore }
  },


  data() {

    return {

      loading: false,
      submitting: false,
      requests: [],
      totalItems: 0,
      paginationMeta: null,
      currentPage: 1,
      stats: {
        totalRequests: 0,
        pendingRequests: 0,
        approvedRequests: 0,
        completedRequests: 0
      },
      requestDialog: false,
      selectedRequest: null,
      adminResponse: '',
      statusFilter: 'all',
      headers: [
        { title: 'Client', key: 'client_name', sortable: false },
        { title: 'Service', key: 'service', sortable: false },
        { title: 'Status', key: 'status', sortable: true },
        { title: 'Creation Date', key: 'created_at', sortable: true },
        { title: 'Actions', key: 'actions', sortable: false, width: '200px' }
      ],
      search: '',

      itemsPerPage: 10,
      snackbar: {
        show: false,
        message: '',
        color: 'success'
      },
      notificationSnackbar: {
        show: false,
        title: '',
        message: ''
      },
      currentNotification: null
    }  },

  async mounted() {
    await this.loadItems()

    await this.fetchStats()
    
    watchDebounced(() => this.search, () => this.loadItems(), { debounce: 1000 })
    watchDebounced(() => this.statusFilter, () => this.loadItems(), { debounce: 100 })

    this.notificationsStore.initialize()
    this.setupNotificationListener()
  },

  methods: {
    setupNotificationListener() {

      this.$watch(
        () => this.notificationsStore.notifications.length,
        (newLength, oldLength) => {
          if (newLength > oldLength) {

            const latestNotification = this.notificationsStore.notifications[0]
            this.showNotificationSnackbar(latestNotification)
          }
        }
      )
    },

    showNotificationSnackbar(notification) {
      this.currentNotification = notification
      this.notificationSnackbar.title = notification.title
      this.notificationSnackbar.message = notification.message
      this.notificationSnackbar.show = true
    },

    markNotificationAsRead() {
      if (this.currentNotification) {
        this.notificationsStore.markAsRead(this.currentNotification.id)
        this.notificationSnackbar.show = false
        this.currentNotification = null
      }
    },
     
    async fetchStats() {
      try {
        const response = await axios.get(`${this.$backend.baseApiUrl}/stats/service-requests`)
        this.stats = {
          totalRequests: response.data.total_requests,
          pendingRequests: response.data.pending_requests,
          approvedRequests: response.data.approved_requests,
          completedRequests: response.data.completed_requests
        }
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },
    
    async loadItems(options = {}) {
      this.loading = true
      try {
        const { page = 1, itemsPerPage = this.itemsPerPage, sortBy = [], search } = options
        
        if (itemsPerPage !== this.itemsPerPage) {

          this.itemsPerPage = itemsPerPage
        }
        
        const perPageValue = itemsPerPage === -1 ? 100 : itemsPerPage

        
        const params = {
          page,
          per_page: perPageValue,
          search: this.search || search || '',
          status: this.statusFilter !== 'all' ? this.statusFilter : ''
        }

        if (sortBy.length > 0) {
          params.sort_by = sortBy[0].key

          params.sort_order = sortBy[0].order
        }

        const response = await axios.get(`${this.$backend.baseApiUrl}/service-requests`, { params })
        console.log('url:', `${this.$backend.baseApiUrl}/service-requests`)
        console.log('Params:', params)
        console.log('Response data:', response)
        this.requests = response.data.data || []
        this.totalItems = response.data.meta?.total || 0

        this.paginationMeta = response.data.meta || null
      } catch (error) {
        console.error('Error loading service requests:', error)
        this.showSnackbar('Error loading service requests', 'error')
      } finally {
        this.loading = false
      }
    },

    viewRequest(request) {
      this.selectedRequest = request
      this.adminResponse = request.admin_response || ''
      this.requestDialog = true
    },

    closeRequestDialog() {
      this.requestDialog = false
      this.selectedRequest = null
      this.adminResponse = ''
    },

    async approveRequest(request) {
      await this.quickUpdateStatus(request, 'approve')
    },

    async denyRequest(request) {
      await this.quickUpdateStatus(request, 'deny')
    },

    async completeRequest(request) {
      await this.quickUpdateStatus(request, 'complete')
    },

    async quickUpdateStatus(request, action) {
      try {
        const response = await axios.put(`${this.$backend.baseApiUrl}/service-requests/${request.id}/${action}`)
        
        const index = this.requests.findIndex(r => r.id === request.id)
        if (index !== -1) {
          this.requests[index] = response.data.data || response.data.service_request

        }
        
        await this.fetchStats()
        this.showSnackbar(response.data.message)
        await this.loadItems() 
      } catch (error) {
        console.error(`Error updating request status to ${action}:`, error)
        this.showSnackbar(
          error.response?.data?.message || `Error updating request status`,
          'error'
        )
      }
    },

    async updateRequestStatus(action) {
      if (!this.selectedRequest) return

      this.submitting = true

      try {
        const response = await axios.put(
          `${this.$backend.baseApiUrl}/service-requests/${this.selectedRequest.id}/${action}`,

          {
            admin_response: this.adminResponse
          }
        )
        
        const index = this.requests.findIndex(r => r.id === this.selectedRequest.id)
        if (index !== -1) {
          this.requests[index] = response.data.data || response.data.service_request
        }

        
        await this.fetchStats()
        this.showSnackbar(response.data.message)
        this.closeRequestDialog()
        await this.loadItems() 
      } catch (error) {
        console.error(`Error updating request status to ${action}:`, error)
        this.showSnackbar(
          error.response?.data?.message || `Error updating request status`,
          'error'
        )
      } finally {
        this.submitting = false
      }
    },

    showSnackbar(message, color = 'success') {
      this.snackbar.message = message
      this.snackbar.color = color
      this.snackbar.show = true
    },

    getStatusColor(status) {
      const colors = {
        pending: 'warning',
        approved: 'success',
        denied: 'error',
        completed: 'info'
      }
      return colors[status] || 'default'
    },

    getStatusText(status) {
      const texts = {
        pending: 'Pending',
        approved: 'Approved',
        denied: 'Denied',
        completed: 'Completed'
      }
      return texts[status] || status
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}
</script>

<style scoped>
.request-manager-content {
  padding-top: 80px;
}

.v-data-table {
  border-radius: 8px;
}
</style>

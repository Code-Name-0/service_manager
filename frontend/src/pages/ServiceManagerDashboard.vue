<template>
  <div>
    <div class="service-manager-content">
      <v-container fluid class="pa-4">
        <div class="d-flex align-center mb-6">
          <v-icon icon="mdi-cog-outline" size="large" color="primary" class="mr-3"></v-icon>
          <h1 class="text-h3 font-weight-bold">Service Manager Dashboard</h1>
        </div>
        
        <v-row class="mb-6">
          <v-col cols="12" md="4">
            <StatCard 
              title="Total Services"
              :value="stats.totalServices"
              icon="mdi-cog"
              icon-color="primary"
              value-color="text-primary"
            />
          </v-col>
          <v-col cols="12" md="4">
            <StatCard 
              title="Active Services"
              :value="stats.activeServices"
              icon="mdi-check-circle"
              icon-color="success"
              value-color="text-success"
            />
          </v-col>
          <v-col cols="12" md="4">
            <StatCard 
              title="Total Requests"
              :value="stats.totalRequests"
              icon="mdi-email"
              icon-color="info"
              value-color="text-info"
            />
          </v-col>
        </v-row>

        <v-card elevation="2" rounded="lg" class="pa-6">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-bold">
              <v-icon icon="mdi-cog" class="mr-2"></v-icon>
              Services Management
            </h2>
            <v-btn
              color="primary"
              @click="openAddDialog"
              prepend-icon="mdi-plus"
            >
              Add Service
            </v-btn>
          </div>

          <v-text-field
            v-model="search"
            label="Search services..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            class="mb-4"
          ></v-text-field>

          <v-data-table-server
            :headers="headers"
            :items="services"
            :loading="loading"
            :items-length="totalItems"
            :items-per-page="itemsPerPage"
            :items-per-page-options="[10, 25, 50, 100]"
            @update:options="loadItems"
            class="elevation-1"
            item-value="id"
          >
            <template v-slot:[`item.is_active`]="{ item }">
              <v-chip
                :color="getStatusColor(item.is_active)"
                size="small"
                variant="flat"
              >
                {{ getStatusText(item.is_active) }}
              </v-chip>
            </template>
            
            <template v-slot:[`item.service_requests_count`]="{ item }">
              <v-chip color="info" size="small" variant="outlined">
                {{ item.service_requests_count || 0 }}
              </v-chip>
            </template>
            
            <template v-slot:[`item.actions`]="{ item }">
              <v-btn
                icon
                size="small"
                :color="item.is_active ? 'warning' : 'success'"
                @click="toggleServiceStatus(item)"
                class="mr-1"
                :title="item.is_active ? 'Deactivate Service' : 'Activate Service'"
              >
                <v-icon>{{ item.is_active ? 'mdi-pause' : 'mdi-play' }}</v-icon>
              </v-btn>
              <v-btn
                icon
                size="small"
                color="primary"
                @click="editService(item)"
                class="mr-1"
                title="Edit Service"
              >
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn
                icon
                size="small"
                color="error"
                @click="confirmDelete(item)"
                title="Delete Service"
              >
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table-server>
          

        </v-card>
      </v-container>
    </div>

    <v-dialog v-model="serviceDialog" max-width="600px" persistent>
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ editingService ? 'Edit Service' : 'Add New Service' }}</span>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text>
          <v-form ref="serviceForm" v-model="serviceFormValid" lazy-validation>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="serviceForm.name"
                    :rules="serviceFormRules.name"
                    label="Service Name *"
                    variant="outlined"
                    required
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12">
                  <v-textarea
                    v-model="serviceForm.description"
                    :rules="serviceFormRules.description"
                    label="Description *"
                    variant="outlined"
                    rows="3"
                    required
                  ></v-textarea>
                </v-col>
                
                <v-col cols="12">
                  <v-card variant="outlined" class="pa-3">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <h4 class="text-subtitle-1 font-weight-medium mb-1">Service Status</h4>
                        <p class="text-caption text-medium-emphasis mb-0">
                          {{ serviceForm.is_active ? 'Service is currently active and available to clients' : 'Service is currently inactive and not available to clients' }}
                        </p>
                      </div>
                      <v-switch
                        v-model="serviceForm.is_active"
                        :label="serviceForm.is_active ? 'Active' : 'Inactive'"
                        :color="serviceForm.is_active ? 'success' : 'error'"
                        hide-details
                      ></v-switch>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="closeDialog"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            @click="saveService"
            :loading="submitting"
            :disabled="!serviceFormValid"
          >
            {{ editingService ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">Confirm Delete</v-card-title>
        <v-card-text>
          Are you sure you want to delete the service "{{ serviceToDelete?.name }}"?
          <v-alert
            v-if="serviceToDelete?.service_requests_count > 0"
            type="warning"
            class="mt-3"
            variant="tonal"
          >
            This service has {{ serviceToDelete.service_requests_count }} associated requests.
            Deleting it may affect existing requests.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="grey"
            variant="text"
            @click="deleteDialog = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            @click="deleteService"
            :loading="submitting"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

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
import StatCard from '@/components/common/StatCard.vue'

import { watchDebounced } from '@vueuse/core'


export default {

  name: 'ServiceManagerDashboard',

  components: {
    StatCard
  },


  data() {

    return {
      loading: false,
      submitting: false,
      services: [],
      paginationMeta: null,
      currentPage: 1,
      stats: {
        totalServices: 0,
        activeServices: 0,
        totalRequests: 0
      },
      serviceDialog: false,
      deleteDialog: false,
      editingService: null,
      serviceToDelete: null,
      serviceFormValid: false,
      serviceForm: {
        name: '',
        description: '',
        is_active: true
      },
      serviceFormRules: {
        name: [v => !!v || 'Name is required'],
        description: [v => !!v || 'Description is required']
      },
      headers: [
        { title: 'Name', key: 'name', sortable: false },
        { title: 'Description', key: 'description', sortable: false },
        { title: 'Status', key: 'is_active', sortable: true },
        { title: 'Requests Count', key: 'service_requests_count', sortable: true },
        { title: 'Actions', key: 'actions', sortable: false, width: '160px' }
      ],

      search: '',


      totalItems: 0,

      itemsPerPage: 10,
      snackbar: {

        show: false,
        message: '',

        color: 'success'
      }

    }
  },


  async mounted() {

    await this.loadItems()


    await this.fetchStats()
    

    watchDebounced(

      () => this.search,
      () => this.loadItems(),
      { debounce: 1000 }
    )
  },

  methods: {
    async fetchStats() {
      try {
        const response = await axios.get(`${this.$backend.baseApiUrl}/stats/services`)
        this.stats = {
          totalServices: response.data.total_services,
          activeServices: response.data.active_services,
          totalRequests: response.data.total_requests
        }
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
    },

    async loadItems(options = {}) {
      this.loading = true
      try {
        const { page = 1, itemsPerPage = this.itemsPerPage, sortBy = [] } = options
        
        if (itemsPerPage !== this.itemsPerPage) {

          this.itemsPerPage = itemsPerPage
        }
        
        const perPageValue = itemsPerPage === -1 ? 100 : itemsPerPage

        
        const params = {
          page,
          per_page: perPageValue
        }
        



        if (this.search) {
          params.search = this.search
        }
        
        if (sortBy.length > 0) {

          params.sort_by = sortBy[0].key
          params.sort_order = sortBy[0].order
        }

        const response = await axios.get(`${this.$backend.baseApiUrl}/services`, { params })
        
        this.services = response.data.data || []
        this.totalItems = response.data.meta?.total || 0

        this.paginationMeta = response.data.meta || null
      } catch (error) {
        console.error('Error fetching services:', error)
        this.showSnackbar('Error fetching services', 'error')
      } finally {
        this.loading = false
      }
    },

    async fetchServices(page = 1) {
      await this.loadItems({ page })

    },

    handlePageChange(page) {
      this.loadItems({ page })
    },

    openAddDialog() {
      this.editingService = null
      this.serviceForm = {
        name: '',
        description: '',
        is_active: true
      }
      this.serviceDialog = true
    },

    editService(service) {
      this.editingService = service
      this.serviceForm = {
        name: service.name,
        description: service.description,
        is_active: service.is_active
      }
      this.serviceDialog = true
    },

    closeDialog() {
      this.serviceDialog = false
      this.editingService = null
    },

    async saveService() {
      if (!this.$refs.serviceForm.validate()) return

      this.submitting = true

      try {
        let response

        if (this.editingService) {
          response = await axios.put(

            `${this.$backend.baseApiUrl}/services/${this.editingService.id}`,
            this.serviceForm
          )

          const index = this.services.findIndex(s => s.id === this.editingService.id)
          if (index !== -1) {
            this.services[index] = response.data.data || response.data.service
          }

          this.showSnackbar('Service updated successfully')
        } else {

          response = await axios.post(`${this.$backend.baseApiUrl}/services`, this.serviceForm)
          this.services.push(response.data.data || response.data.service)

          this.showSnackbar('Service created successfully')
        }

        await this.fetchStats()
        this.closeDialog()

      } catch (error) {
        console.error('Error saving service:', error)
        this.showSnackbar(

          error.response?.data?.message || 'Error saving service',
          'error'

        )
      } finally {
        this.submitting = false
      }
    },



    confirmDelete(service) {
      this.serviceToDelete = service
      this.deleteDialog = true
    },



    async deleteService() {
      if (!this.serviceToDelete) return

      this.submitting = true
      try {
        await axios.delete(`${this.$backend.baseApiUrl}/services/${this.serviceToDelete.id}`)
        


        const index = this.services.findIndex(s => s.id === this.serviceToDelete.id)
        if (index !== -1) {
          this.services.splice(index, 1)
        }
        
        await this.fetchStats()
        this.showSnackbar('Service deleted successfully')
        this.deleteDialog = false
        this.serviceToDelete = null
      } catch (error) {
        console.error('Error deleting service:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Error deleting service',
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

    async toggleServiceStatus(service) {
      try {
        const response = await axios.put(`${this.$backend.baseApiUrl}/services/${service.id}/toggle-status`)
        



        const index = this.services.findIndex(s => s.id === service.id)
        if (index !== -1) {
          this.services[index].is_active = (response.data.data || response.data.service).is_active
        }
        
        await this.fetchStats()
        this.showSnackbar(response.data.message)
      } catch (error) {
        console.error('Error toggling service status:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Error updating service status',
          'error'
        )
      }
    },

    getStatusColor(isActive) {
      return isActive ? 'success' : 'error'
    },

    getStatusText(isActive) {
      return isActive ? 'Active' : 'Inactive'
    }
  }
}
</script>

<style scoped>
.service-manager-content {
  padding-top: 80px;
}

.v-data-table {
  border-radius: 8px;
}
</style>

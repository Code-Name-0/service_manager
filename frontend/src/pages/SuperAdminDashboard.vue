<template>
  <div>
    <div class="super-admin-content">
      <v-container fluid class="pa-4">
        <div class="d-flex align-center mb-6">
          <v-icon icon="mdi-shield-crown" size="large" color="primary" class="mr-3"></v-icon>
          <h1 class="text-h3 font-weight-bold">Super Admin Dashboard</h1>
        </div>



        <v-row class="mb-6">
          <v-col cols="12" md="3">
            <StatCard 
              title="Total Admins"
              :value="stats.totalAdmins"
              icon="mdi-account-multiple"
              icon-color="primary"
              value-color="text-primary"
            />
          </v-col>
          <v-col cols="12" md="3">
            <StatCard 
              title="Active Admins"
              :value="stats.activeAdmins"
              icon="mdi-account-check"
              icon-color="success"
              value-color="text-success"
            />
          </v-col>
          <v-col cols="12" md="3">
            <StatCard 
              title="Blocked Admins"
              :value="stats.blockedAdmins"
              icon="mdi-account-off"
              icon-color="error"
              value-color="text-error"
            />
          </v-col>
          <v-col cols="12" md="3">
            <StatCard 
              title="Super Admins"
              :value="stats.superAdmins"
              icon="mdi-shield-star"
              icon-color="warning"
              value-color="text-warning"
            />
          </v-col>
        </v-row>


        <v-card elevation="2" rounded="lg" class="pa-6">
          <div class="d-flex justify-space-between align-center mb-4">
            <h2 class="text-h5 font-weight-bold">
              <v-icon icon="mdi-account-multiple" class="mr-2"></v-icon>
              Admin Accounts Management
            </h2>
            <v-btn
              color="primary"
              @click="openAddAdminDialog"
              prepend-icon="mdi-plus"
            >
              Add New Admin
            </v-btn>
          </div>


          <v-text-field
            v-model="search"
            label="Search admins..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            class="mb-4"
          ></v-text-field>


          <v-data-table-server
            :headers="headers"
            :items="admins"
            :loading="loading"
            :items-length="totalItems"
            :items-per-page="itemsPerPage"
            :items-per-page-options="[10, 25, 50, 100]"
            @update:options="loadItems"
            class="elevation-1"
            item-value="id"
          >
            <template v-slot:[`item.name`]="{ item }">
              <div>
                <div class="font-weight-medium">{{ item.name }}</div>
                <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
              </div>
            </template>

            <template v-slot:[`item.permissions`]="{ item }">
              <div class="d-flex flex-wrap ga-1">
                <v-chip
                  v-for="permission in item.permissions"
                  :key="permission"
                  size="x-small"
                  :color="getPermissionColor(permission)"
                  variant="outlined"
                >
                  {{ getPermissionText(permission) }}
                </v-chip>
              </div>
            </template>
            
            <template v-slot:[`item.is_blocked`]="{ item }">
              <v-chip
                :color="item.is_blocked ? 'error' : 'success'"
                size="small"
                variant="flat"
              >
                {{ item.is_blocked ? 'Blocked' : 'Active' }}
              </v-chip>
            </template>

            <template v-slot:[`item.created_at`]="{ item }">
              <div class="text-caption">
                {{ formatDate(item.created_at) }}
              </div>
            </template>
            
            <template v-slot:[`item.actions`]="{ item }">
              <v-btn
                icon
                size="small"
                color="primary"
                @click="editAdmin(item)"
                class="mr-1"
                title="Edit Admin"
              >
                <v-icon>mdi-pencil</v-icon>
              </v-btn>
              <v-btn
                v-if="!item.is_blocked"
                icon
                size="small"
                color="warning"
                @click="blockAdmin(item)"
                class="mr-1"
                title="Block Admin"
              >
                <v-icon>mdi-account-off</v-icon>
              </v-btn>
              <v-btn
                v-if="item.is_blocked"
                icon
                size="small"
                color="success"
                @click="unblockAdmin(item)"
                class="mr-1"
                title="Unblock Admin"
              >
                <v-icon>mdi-account-check</v-icon>
              </v-btn>
              <v-btn
                v-if="item.id !== currentAdminId"
                icon
                size="small"
                color="error"
                @click="confirmDeleteAdmin(item)"
                title="Delete Admin"
              >
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table-server>
        </v-card>
      </v-container>
    </div>


    <v-dialog v-model="adminDialog" max-width="600px" persistent>
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ isEditing ? 'Edit Admin' : 'Add New Admin' }}</span>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="adminForm.name"
                  label="Full Name"
                  variant="outlined"
                  :rules="[v => !!v || 'Name is required']"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="adminForm.email"
                  label="Email"
                  type="email"
                  variant="outlined"
                  :rules="emailRules"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" v-if="!isEditing">
                <v-text-field
                  v-model="adminForm.password"
                  label="Password"
                  :type="showPassword ? 'text' : 'password'"
                  variant="outlined"
                  :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  @click:append-inner="showPassword = !showPassword"
                  :rules="passwordRules"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12" v-if="isEditing">
                <v-text-field
                  v-model="adminForm.password"
                  label="New Password (leave empty to keep current)"
                  :type="showPassword ? 'text' : 'password'"
                  variant="outlined"
                  :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  @click:append-inner="showPassword = !showPassword"
                  hint="Leave empty to keep current password"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-select
                  v-model="adminForm.permissions"
                  :items="availablePermissions"
                  item-title="label"
                  item-value="value"
                  label="Permissions"
                  multiple
                  variant="outlined"
                  chips
                  closable-chips
                ></v-select>
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
            @click="closeAdminDialog"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            @click="saveAdmin"
            :loading="submitting"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>



    <v-dialog v-model="deleteDialog" max-width="400px">
      <v-card>
        <v-card-title class="text-h5">Confirm Delete</v-card-title>
        <v-card-text>
          Are you sure you want to delete admin "{{ adminToDelete?.name }}"? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" @click="deleteAdmin" :loading="submitting">Delete</v-btn>
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

import { watchDebounced } from '@vueuse/core'
import StatCard from '@/components/common/StatCard.vue'


export default {

  name: 'SuperAdminDashboard',

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
      admins: [],
      totalItems: 0,
      paginationMeta: null,
      currentPage: 1,
      stats: {
        totalAdmins: 0,
        activeAdmins: 0,
        blockedAdmins: 0,
        superAdmins: 0
      },
      adminDialog: false,
      deleteDialog: false,
      isEditing: false,
      showPassword: false,
      selectedAdmin: null,
      adminToDelete: null,
      adminForm: {
        name: '',
        email: '',
        password: '',
        permissions: []
      },
      availablePermissions: [
        { label: 'Super Admin', value: 'super_admin' },
        { label: 'Manage Services', value: 'manage_services' },
        { label: 'Manage Requests', value: 'manage_requests' }
      ],
      headers: [
        { title: 'Admin', key: 'name', sortable: false },
        { title: 'Permissions', key: 'permissions', sortable: false },
        { title: 'Status', key: 'is_blocked', sortable: true },
        { title: 'Created', key: 'created_at', sortable: true },
        { title: 'Actions', key: 'actions', sortable: false, width: '200px' }
      ],
      search: '',

      itemsPerPage: 10,
      emailRules: [
        v => !!v || 'Email is required',
        v => /.+@.+\..+/.test(v) || 'Email must be valid'
      ],
      passwordRules: [
        v => !!v || 'Password is required',
        v => v.length >= 6 || 'Password must be at least 6 characters'
      ],
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
    }
  },

  computed: {
    currentAdminId() {
      return this.authStore.admin?.id
    }
  },

  async mounted() {
    await this.loadItems()

    await this.fetchStats()

    watchDebounced(() => this.search, () => this.loadItems(), { debounce: 1000 })
    
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

    async fetchStats() {
      try {
        const response = await axios.get(`${this.$backend.baseApiUrl}/stats/admins`)
        this.stats = {
          totalAdmins: response.data.total_admins,
          activeAdmins: response.data.active_admins,
          blockedAdmins: response.data.blocked_admins,
          superAdmins: response.data.super_admins
        }
      } catch (error) {
        console.error('Error fetching stats:', error)
      }
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
          search: this.search || search || ''
        }

        if (sortBy.length > 0) {
          params.sort_by = sortBy[0].key
          params.sort_order = sortBy[0].order
        }

        const response = await axios.get(`${this.$backend.baseApiUrl}/admins`, { params })
        
        this.admins = response.data.data || []
        this.totalItems = response.data.meta?.total || 0
        this.paginationMeta = response.data.meta || null
      } catch (error) {
        console.error('Error loading admins:', error)
        this.showSnackbar('Error loading admins', 'error')
      } finally {
        this.loading = false
      }
    },

    async fetchAdmins(page = 1) {
      this.loading = true
      try {
        const response = await axios.get(`${this.$backend.baseApiUrl}/admins`, {
          params: { page }
        })

        this.admins = response.data.data || []
        this.paginationMeta = response.data.meta || null

        this.currentPage = page
        await this.fetchStats()
      } catch (error) {
        console.error('Error fetching admins:', error)
        this.showSnackbar('Error fetching admins', 'error')
      } finally {
        this.loading = false
      }
    },

    handlePageChange(page) {
      this.fetchAdmins(page)
    },

    openAddAdminDialog() {
      this.isEditing = false
      this.selectedAdmin = null
      this.adminForm = {
        name: '',
        email: '',
        password: '',
        permissions: []
      }
      this.adminDialog = true
    },

    editAdmin(admin) {
      this.isEditing = true
      this.selectedAdmin = admin
      this.adminForm = {
        name: admin.name,
        email: admin.email,
        password: '',
        permissions: admin.permissions || []
      }
      this.adminDialog = true
    },



    closeAdminDialog() {
      this.adminDialog = false
      this.selectedAdmin = null
      this.showPassword = false
      this.adminForm = {
        name: '',
        email: '',
        password: '',
        permissions: []
      }
    },



    async saveAdmin() {
      this.submitting = true
      try {
        let response
        if (this.isEditing) {

          const payload = {
            name: this.adminForm.name,
            email: this.adminForm.email,
            permissions: this.adminForm.permissions
          }
          



          if (this.adminForm.password.trim()) {
            payload.password = this.adminForm.password
          }



          response = await axios.put(
            `${this.$backend.baseApiUrl}/admins/${this.selectedAdmin.id}`,
            payload
          )
        } else {


          response = await axios.post(`${this.$backend.baseApiUrl}/admins`, {
            name: this.adminForm.name,
            email: this.adminForm.email,
            password: this.adminForm.password,
            permissions: this.adminForm.permissions
          })
        }





        await this.loadItems()
        this.closeAdminDialog()
        this.showSnackbar(response.data.message || `Admin ${this.isEditing ? 'updated' : 'created'} successfully`)
      } catch (error) {
        console.error('Error saving admin:', error)
        this.showSnackbar(
          error.response?.data?.message || `Error ${this.isEditing ? 'updating' : 'creating'} admin`,
          'error'
        )
      } finally {
        this.submitting = false
      }
    },




    async blockAdmin(admin) {
      try {
        const response = await axios.put(`${this.$backend.baseApiUrl}/admins/${admin.id}/block`)
        await this.loadItems()
        this.showSnackbar(response.data.message || 'Admin blocked successfully')
      } catch (error) {
        console.error('Error blocking admin:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Error blocking admin',
          'error'
        )
      }
    },



    async unblockAdmin(admin) {
      try {
        const response = await axios.put(`${this.$backend.baseApiUrl}/admins/${admin.id}/unblock`)
        await this.loadItems()
        this.showSnackbar(response.data.message || 'Admin unblocked successfully')
      } catch (error) {
        console.error('Error unblocking admin:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Error unblocking admin',
          'error'
        )
      }
    },



    confirmDeleteAdmin(admin) {
      this.adminToDelete = admin
      this.deleteDialog = true
    },



    async deleteAdmin() {
      if (!this.adminToDelete) return

      this.submitting = true
      try {
        const response = await axios.delete(`${this.$backend.baseApiUrl}/admins/${this.adminToDelete.id}`)
        await this.loadItems()
        this.deleteDialog = false
        this.adminToDelete = null
        this.showSnackbar(response.data.message || 'Admin deleted successfully')
      } catch (error) {
        console.error('Error deleting admin:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Error deleting admin',
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

    getPermissionColor(permission) {
      const colors = {
        super_admin: 'error',
        manage_services: 'primary',
        manage_requests: 'success'
      }
      return colors[permission] || 'default'
    },

    getPermissionText(permission) {
      const texts = {
        super_admin: 'Super Admin',
        manage_services: 'Services',
        manage_requests: 'Requests'
      }
      return texts[permission] || permission
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}



</script>

<style scoped>
.super-admin-content {
  padding-top: 80px;
}
</style>
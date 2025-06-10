import { defineStore } from 'pinia'
import axios from 'axios'

// Configure axios base URLs
const API_BASE_ADMIN = 'http://127.0.0.1:8000/api/admin'
// const API_BASE_PUBLIC = 'http://127.0.0.1:8000/api' // For future use

export const useAuthStore = defineStore('auth', {
  state: () => ({
    admin: null,
    token: localStorage.getItem('admin_token') || null,
    isAuthenticated: false,
    loading: false,
    error: null
  }),

  getters: {
    isLoggedIn: (state) => !!state.token && state.isAuthenticated,
    adminName: (state) => state.admin?.name || '',
    adminEmail: (state) => state.admin?.email || '',
    adminPermissions: (state) => state.admin?.permissions || [],
    hasPermission: (state) => (permission) => {
      return state.admin?.permissions?.includes(permission) || false
    },
    adminRole: (state) => {
      const permissions = state.admin?.permissions || []
      if (permissions.includes('super_admin')) {
        return 'super_admin'
      } else if (permissions.includes('manage_services')) {
        return 'service_manager'
      } else if (permissions.includes('manage_requests')) {
        return 'request_manager'
      }
      return null
    }
  },

  actions: {
    async initializeAuth() {


      if (this.token) {
        try {
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
          await this.fetchAdmin()


        } catch (error) {
          console.error('Token validation failed:', error)
          this.logout()
        }
      }

    },

    async login(credentials) {

      this.loading = true

      this.error = null
      
      try {
        const response = await axios.post(`${API_BASE_ADMIN}/login`, {
          email: credentials.email,
          password: credentials.password
        })
        
        this.token = response.data.token
        this.admin = response.data.admin
        this.isAuthenticated = true
        
        // Add permissions to admin object
        if (response.data.permissions) {
          this.admin.permissions = response.data.permissions
        }
        
        // Store token in localStorage
        localStorage.setItem('admin_token', this.token)
        
        // Set default Authorization header for future requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        console.error('Login error:', error.response?.data)
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {

      if (this.token) {
        try {

          await axios.post(`${API_BASE_ADMIN}/logout`)
        } catch (error) {

          console.error('Logout error:', error)
        }
      }


      this.admin = null
      this.token = null
      this.isAuthenticated = false
      this.error = null
      

      localStorage.removeItem('admin_token')
      

      delete axios.defaults.headers.common['Authorization']
    },


    async fetchAdmin() {
      try {
        const response = await axios.get(`${API_BASE_ADMIN}/me`)
        // With Laravel Resources, admin data might be in response.data.data or response.data.admin
        this.admin = response.data.data || response.data.admin
        this.isAuthenticated = true
        
        // Update admin permissions in state
        if (response.data.permissions) {
          this.admin.permissions = response.data.permissions
        }
        
        return response.data
      } catch (error) {
        console.error('Fetch admin error:', error)
        this.logout()
        throw error
      }
    },


    async fetchPermissions() {

      try {
        const response = await axios.get(`${API_BASE_ADMIN}/permissions`)

        return response.data
      } catch (error) {
        console.error('Fetch permissions error:', error)

        throw error
      }
    }
  }
})
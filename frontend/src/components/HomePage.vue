<template>
  <div  >
    <AppNavbar />
    <div class="main-content px-4 pb-4 min-h-screen" >
      <h1 class="mb-6">Home Page</h1>

    <div v-if="loading"> 
      <v-card loading text="...">
        <v-card-title>
          <h2>Loading Services...</h2>
        </v-card-title>
      </v-card>
    </div>
    
    <div v-if="error" style="color: red;">
      Error: {{ error }}
    </div>
    
    <v-container v-if="!loading && !error && services.length > 0" fluid class="pa-4 d-flex justify-center">
      <div class="content-wrapper">
        <v-row justify="center">
          <v-col 
            v-for="(service, index) in services" 
            :key="service.id || index"
            cols="12"
            sm="6"
            md="6"
            lg="4"
            xl="3"
            class="pa-2"
          >
          <v-card 
            height="300"
            elevation="4"
            rounded="lg"
            class="service-card d-flex flex-column justify-space-between"
            hover
            @click="toggleServiceModal(service)"
            style="cursor: pointer;"
          >
            <v-card-item>
              <div>
                <div class="d-flex justify-space-between align-center mb-3">
                  <div class="text-overline">
                    Service
                  </div>
                  <v-chip
                    :color="service.is_active ? 'success' : 'warning'"
                    variant="tonal"
                    size="small"
                    rounded="pill"
                  >
                    {{ service.is_active ? 'Active' : 'Inactive' }}
                  </v-chip>
                </div>


                <div class="text-h6 mb-3">
                  {{ service.name || service.title || 'No Name' }}
                </div>
                <div class="text-body-2 description-text">
                  {{ service.description || 'No Description' }}
                </div>
              </div>
            </v-card-item>

            <v-card-actions class="pa-4">
              <v-btn
                color="primary"
                variant="outlined"
                size="small"
                rounded="pill"
                block
              >
                View Details
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
      </div>
    </v-container>

    <div v-if="!loading && !error && services.length === 0">
      No services found.
    </div>
    </div>

    <v-dialog v-model="serviceModal" max-width="600">
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center pa-4">
          <div class="text-h5">
            {{ selectedService?.name || 'Service Details' }}
          </div>
          <v-btn
            icon="mdi-close"
            variant="text"
            @click="serviceModal = false"
          ></v-btn>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text class="pa-4">
          <div class="mb-4">
            <div class="text-overline mb-2">Description</div>
            <div class="text-body-1">
              {{ selectedService?.description || 'No description available' }}
            </div>
          </div>
          
          <div class="mb-4">
            <div class="text-overline mb-2">Status</div>
            <v-spacer></v-spacer>
            <v-chip
              :color="selectedService?.is_active ? 'success' : 'error'"
              variant="tonal"
              size="small"
            >
              {{ selectedService?.is_active ? 'Active' : 'Inactive' }}
            </v-chip>
          </div>
          
          <div v-if="selectedService?.id" class="mb-4">
            <div class="text-overline mb-2">Service ID</div>
            <v-spacer></v-spacer>
            <div class="text-body-2 text-medium-emphasis">
              #{{ selectedService.id }}
            </div>
          </div>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            variant="outlined"
            @click="serviceModal = false"
          >
            Close
          </v-btn>
          <v-btn
            :color="selectedService?.is_active ? 'primary' : 'grey'"
            variant="flat"
            @click="toggleRequestModal"
            :disabled="!selectedService?.is_active"
          >
            {{selectedService?.is_active ? 'Request Service' : 'Service Unavailable'}}
          </v-btn>
        </v-card-actions>
      </v-card> 
    </v-dialog>

    <v-dialog v-model="requestModal" max-width="600" persistent>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center pa-4">
          <div class="text-h5">
            Request Service: {{ selectedService?.name }}
          </div>
          <v-btn
            icon="mdi-close"
            variant="text"
            @click="closeRequestModal"
          ></v-btn>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-card-text class="pa-4">
          <v-form ref="requestForm" v-model="formValid">
            <v-text-field
              v-model="requestForm.client_name"
              label="Full Name"
              :rules="[rules.required]"
              prepend-inner-icon="mdi-account"
              variant="outlined"
              class="mb-3"
            ></v-text-field>

            <v-text-field
              v-model="requestForm.client_email"
              label="Email Address"
              prepend-inner-icon="mdi-email"
              type="email"
              :rules="[rules.required, rules.email]"
              variant="outlined"
              class="mb-3"
            ></v-text-field>

            <v-text-field
              v-model="requestForm.client_phone"
              label="Phone Number"
              prepend-inner-icon="mdi-phone"
              :rules="[rules.required, rules.phone]"
              variant="outlined"
              class="mb-3"
            ></v-text-field>

            <v-textarea
              v-model="requestForm.message"
              label="Message / Requirements"
              prepend-inner-icon="mdi-message"
              :rules="[rules.required]"
              variant="outlined"
              rows="4"
              placeholder="Please describe your requirements or any specific details about the service you need..."
            ></v-textarea>
          </v-form>

          <v-alert
            v-if="requestError"
            type="error"
            class="mt-4"
            closable
            @click:close="requestError = null"
          >
            {{ requestError }}
          </v-alert>

          <v-alert
            v-if="requestSuccess"
            type="success"
            class="mt-4"
            closable
            @click:close="requestSuccess = null"
          >
            {{ requestSuccess }}
          </v-alert>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            variant="outlined"
            @click="closeRequestModal"
            :disabled="submittingRequest"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            @click="submitServiceRequest"
            :loading="submittingRequest"
            :disabled="!formValid"
          >
            Submit Request
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from 'axios'
import AppNavbar from './AppNavbar.vue'

export default {
  name: 'HomePage',

  components: {
    AppNavbar
  },

  mounted() {
    this.fetchServices()
  },
  
  data() {
    return {
      services: [],
      loading: false,
      error: null,
      serviceModal: false,
      selectedService: null,
      requestModal: false,
      submittingRequest: false,
      requestError: null,
      requestSuccess: null,
      formValid: false,
      requestForm: {
        client_name: '',
        client_email: '',
        client_phone: '',
        message: ''
      },
      rules: {
        required: value => !!value || 'This field is required',
        email: value => {
          const pattern = /.+@.+\..+/
          return pattern.test(value) || 'Invalid email address'
        },
        phone: value => {
          const pattern = /^[+]?[0-9\s\-()]{10,13}$/
          return pattern.test(value) || 'Invalid phone number'
        }
      }
    }
  },
  
  methods: {
    filterActiveServices(services, onlyActive = true) {
      if (!onlyActive) return services
      return services.filter(service => service.is_active)
    },
    toggleServiceModal(service) {
      this.selectedService = service
      this.serviceModal = true
    },
    toggleRequestModal() {
      this.serviceModal = false
      this.requestModal = true
      this.resetRequestForm()
    },
    closeRequestModal() {
      this.requestModal = false
      this.resetRequestForm()
    },
    resetRequestForm() {
      this.requestForm = {
        client_name: '',
        client_email: '',
        client_phone: '',
        message: ''
      }
      this.requestError = null
      this.requestSuccess = null
      this.formValid = false
      if (this.$refs.requestForm) {
        this.$refs.requestForm.resetValidation()
      }
    },
    async submitServiceRequest() {
      if (!this.formValid || !this.selectedService) return

      this.submittingRequest = true
      this.requestError = null
      this.requestSuccess = null

      try {
        const url = `${this.$backend.basePublicUrl}/service-requests`
        const requestData = {
          service_id: this.selectedService.id,
          client_name: this.requestForm.client_name,
          client_email: this.requestForm.client_email,
          client_phone: this.requestForm.client_phone,
          message: this.requestForm.message
        }

        console.log('Submitting service request:', requestData)

        const response = await axios.post(url, requestData)
        
        this.requestSuccess = response.data.message || 'Service request submitted successfully!'
        
        setTimeout(() => {
          this.closeRequestModal()
        }, 2000)

      } catch (error) {
        console.error('Error submitting service request:', error)
        
        if (error.response?.data?.errors) {
          // Handle validation errors
          const errors = error.response.data.errors
          const errorMessages = Object.values(errors).flat()
          this.requestError = errorMessages.join(', ')
        } else {
          this.requestError = error.response?.data?.message || error.message || 'Failed to submit service request'
        }
      } finally {
        this.submittingRequest = false
      }
    },
    async fetchServices() {
      this.loading = true
      this.error = null
      
      try {
        const url = `${this.$backend.basePublicUrl}/services`
        console.log('Fetching services from:', url)
        
        const response = await axios.get(url)
        console.log(response.data)
        this.services = response.data.services
      } catch (error) {
        this.error = error.response?.data?.message || error.message
        console.error('Error fetching services:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.description-text {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.content-wrapper {
  max-width: 1200px;
  width: 100%;
}
</style>
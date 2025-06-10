<template>
  <v-dialog v-model="localModel" max-width="400" persistent>
    <v-card>
      <v-card-title class="text-h5 text-center pa-6">
        <img 
          src="@/assets/logo.jpg" 
          alt="Originova Logo" 
          class="logo-image"
          style="height: 40px; width: 40px; border-radius: 50%;"
          @click="$emit('go-home')"
        />
        <br>
        Admin Login
      </v-card-title>
      <v-card-text class="px-6">
        <v-form ref="loginForm" v-model="formValid" @submit.prevent="handleSubmit">
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
          <button type="submit" style="display: none;" @click="handleSubmit"></button>
        </v-form>
      </v-card-text>

      <v-card-actions class="px-6 pb-6 d-flex flex-column">
        <v-btn
          color="primary"
          variant="flat"
          @click="handleSubmit"
          :loading="authStore.loading"
          :disabled="!formValid"
          block
          class="mb-2"
        >
          Login
        </v-btn>
        <v-btn
          variant="outlined"
          @click="handleClose"
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

export default {
  name: 'LoginModal',
  
  emits: ['update:modelValue', 'login-success', 'go-home'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },

  setup() {
    const authStore = useAuthStore()
    return { authStore }
  },

  data() {
    return {
      showPassword: false,
      formValid: false,
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
    localModel: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    }
  },

  methods: {
    async handleSubmit() {
      if (!this.formValid) return

      try {
        await this.authStore.login({
          email: this.loginForm.email,
          password: this.loginForm.password
        })
        
        this.handleClose()
        this.$emit('login-success')
        
      } catch (error) {
        console.error('Login failed:', error)
      }
    },

    handleClose() {
      this.localModel = false
      this.loginForm.email = ''
      this.loginForm.password = ''
      this.showPassword = false
      this.authStore.error = null
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

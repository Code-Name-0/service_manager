<template>
  <v-snackbar
    v-model="localModel"
    :color="color"
    :timeout="timeout"
    location="top right"
    variant="elevated"
  >
    <div class="d-flex align-center">
      <v-icon class="mr-2">{{ icon }}</v-icon>
      {{ message }}
    </div>
    
    <template v-slot:actions>
      <v-btn
        variant="text"
        @click="localModel = false"
      >
        Close
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
export default {
  name: 'CustomSnackbar',
  
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    message: {
      type: String,
      required: true
    },
    color: {
      type: String,
      default: 'success'
    },
    timeout: {
      type: Number,
      default: 5000
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
    },
    
    icon() {
      switch (this.color) {
        case 'success':
          return 'mdi-check-circle'
        case 'error':
          return 'mdi-alert-circle'
        case 'warning':
          return 'mdi-alert'
        case 'info':
          return 'mdi-information'
        default:
          return 'mdi-information'
      }
    }
  }
}
</script>

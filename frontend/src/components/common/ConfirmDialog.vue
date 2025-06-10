<template>
  <v-dialog v-model="localModel" max-width="400">
    <v-card>
      <v-card-title class="text-h6">
        {{ title }}
      </v-card-title>
      
      <v-card-text>
        {{ message }}
      </v-card-text>
      
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn 
          variant="text" 
          @click="localModel = false"
        >
          Cancel
        </v-btn>
        <v-btn 
          color="error" 
          variant="flat"
          @click="handleConfirm"
          :loading="loading"
        >
          {{ confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'ConfirmDialog',
  
  emits: ['update:modelValue', 'confirm'],

  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Confirm Action'
    },
    message: {
      type: String,
      required: true
    },
    confirmText: {
      type: String,
      default: 'Confirm'
    },
    loading: {
      type: Boolean,
      default: false
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
    handleConfirm() {
      this.$emit('confirm')
    }
  }
}
</script>

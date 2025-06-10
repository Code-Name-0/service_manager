<template>
  <v-dialog v-model="localModel" max-width="400" persistent>
    <v-card>
      <v-card-title class="text-h5 d-flex align-center">
        <v-icon :color="iconColor" class="mr-2">{{ icon }}</v-icon>
        {{ title }}
      </v-card-title>
      
      <v-card-text class="pb-0">
        {{ message }}
      </v-card-text>

      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn
          variant="outlined"
          @click="handleCancel"
          :disabled="loading"
        >
          {{ cancelText }}
        </v-btn>
        <v-btn
          :color="confirmColor"
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
  
  emits: ['update:modelValue', 'confirm', 'cancel'],

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
    cancelText: {
      type: String,
      default: 'Cancel'
    },
    confirmColor: {
      type: String,
      default: 'error'
    },
    icon: {
      type: String,
      default: 'mdi-alert-circle'
    },
    iconColor: {
      type: String,
      default: 'warning'
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
    },

    handleCancel() {
      this.localModel = false
      this.$emit('cancel')
    }
  }
}
</script>

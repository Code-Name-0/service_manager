<template>
  <v-chip
    :color="chipColor"
    :variant="variant"
    :size="size"
    class="font-weight-medium"
  >
    <v-icon v-if="showIcon" :icon="statusIcon" size="small" class="mr-1"></v-icon>
    {{ displayText }}
  </v-chip>
</template>

<script>
export default {
  name: 'StatusChip',
  
  props: {
    status: {
      type: String,
      required: true
    },
    size: {
      type: String,
      default: 'small'
    },
    variant: {
      type: String,
      default: 'flat'
    },
    showIcon: {
      type: Boolean,
      default: true
    }
  },

  computed: {
    chipColor() {
      switch (this.status.toLowerCase()) {
        case 'pending':
          return 'warning'
        case 'in_progress':
        case 'in progress':
          return 'info'
        case 'completed':
          return 'success'
        case 'cancelled':
        case 'canceled':
          return 'error'
        case 'active':
          return 'success'
        case 'inactive':
          return 'grey'
        default:
          return 'primary'
      }
    },

    statusIcon() {
      switch (this.status.toLowerCase()) {
        case 'pending':
          return 'mdi-clock-outline'
        case 'in_progress':
        case 'in progress':
          return 'mdi-progress-clock'
        case 'completed':
          return 'mdi-check-circle'
        case 'cancelled':
        case 'canceled':
          return 'mdi-close-circle'
        case 'active':
          return 'mdi-check-circle'
        case 'inactive':
          return 'mdi-pause-circle'
        default:
          return 'mdi-information'
      }
    },

    displayText() {
      return this.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    }
  }
}
</script>

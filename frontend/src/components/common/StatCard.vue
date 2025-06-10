<template>
  <v-card elevation="3">
    <v-card-text class="text-center py-6">
      <v-icon 
        :color="iconColor" 
        size="48" 
        class="mb-3"
      >
        {{ icon }}
      </v-icon>
      <div class="text-h4 font-weight-bold mb-2" :class="valueColor">
        {{ value }}
      </div>
      <div class="text-body-1 text-grey-darken-1">
        {{ title }}
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: 'StatCard',
  
  props: {
    title: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
      required: true
    },
    icon: {
      type: String,
      required: true
    },
    iconColor: {
      type: String,
      default: 'primary'
    },
    valueColor: {
      type: String,
      default: 'text-primary'
    }
  }
}
</script>

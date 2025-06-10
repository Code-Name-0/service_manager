<template>
  <div class="d-flex align-center justify-center ga-2">
    <v-btn
      v-if="showEdit"
      :color="editColor"
      :variant="variant"
      :size="size"
      :icon="iconOnly"
      @click="$emit('edit')"
      :disabled="disabled"
    >
      <v-icon size="small">mdi-pencil</v-icon>
      <span v-if="!iconOnly" class="ml-1">Edit</span>
    </v-btn>

    <v-btn
      v-if="showDelete"
      :color="deleteColor"
      :variant="variant"
      :size="size"
      :icon="iconOnly"
      @click="$emit('delete')"
      :disabled="disabled"
    >
      <v-icon size="small">mdi-delete</v-icon>
      <span v-if="!iconOnly" class="ml-1">Delete</span>
    </v-btn>

    <v-btn
      v-if="showView"
      :color="viewColor"
      :variant="variant"
      :size="size"
      :icon="iconOnly"
      @click="$emit('view')"
      :disabled="disabled"
    >
      <v-icon size="small">mdi-eye</v-icon>
      <span v-if="!iconOnly" class="ml-1">View</span>
    </v-btn>
  </div>
</template>

<script>
export default {
  name: 'DataTableActions',
  
  emits: ['edit', 'delete', 'view'],

  props: {
    showEdit: {
      type: Boolean,
      default: true
    },
    showDelete: {
      type: Boolean,
      default: true
    },
    showView: {
      type: Boolean,
      default: false
    },
    editColor: {
      type: String,
      default: 'primary'
    },
    deleteColor: {
      type: String,
      default: 'error'
    },
    viewColor: {
      type: String,
      default: 'info'
    },
    variant: {
      type: String,
      default: 'text'
    },
    size: {
      type: String,
      default: 'small'
    },
    iconOnly: {
      type: Boolean,
      default: true
    },
    disabled: {
      type: Boolean,
      default: false
    }
  }
}
</script>

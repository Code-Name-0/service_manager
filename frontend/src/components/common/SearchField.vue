<template>
  <v-text-field
    v-model="localValue"
    :label="label"
    :placeholder="placeholder"
    variant="outlined"
    density="compact"
    prepend-inner-icon="mdi-magnify"
    clearable
    hide-details
    :class="customClass"
    @input="$emit('update:modelValue', $event.target.value)"
  ></v-text-field>
</template>

<script>
export default {
  name: 'SearchField',
  
  emits: ['update:modelValue'],

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: 'Search'
    },
    placeholder: {
      type: String,
      default: 'Type to search...'
    },
    customClass: {
      type: String,
      default: ''
    }
  },

  computed: {
    localValue: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    }
  }
}
</script>

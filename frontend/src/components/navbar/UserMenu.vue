<template>
  <div class="d-flex align-center">
    <template v-if="authStore.isLoggedIn">
      <v-chip
        v-if="authStore.adminName"
        color="primary"
        variant="tonal"
        size="small"
        class="mx-2 d-none d-sm-flex"
      >
        {{ authStore.adminName }}
      </v-chip>
      

      <v-btn
        color="error"
        variant="outlined"
        rounded="pill"
        size="small"
        class="ml-2"
        @click="$emit('logout')"
        :loading="authStore.loading"
      >
        <v-icon v-if="$vuetify.display.xs" size="small">mdi-logout</v-icon>
        <template v-if="$vuetify.display.smAndUp">
          <v-icon size="small" class="mr-1">mdi-logout</v-icon>
          Logout
        </template>
      </v-btn>
    </template>

    <template v-else>
      <v-btn
        color="primary"
        variant="outlined"
        rounded="pill"
        size="small"
        @click="$emit('login')"
        :loading="authStore.loading"
      >
        <v-icon v-if="$vuetify.display.xs" size="small">mdi-login</v-icon>
        <template v-if="$vuetify.display.smAndUp">
          <v-icon size="small" class="mr-1">mdi-login</v-icon>
          Login
        </template>
      </v-btn>
    </template>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'UserMenu',
  
  emits: ['login', 'logout'],

  setup() {
    const authStore = useAuthStore()
    return { authStore }
  }
}
</script>

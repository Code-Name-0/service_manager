<template>
  <v-app>
    <router-view />
  </v-app>
</template>

<script>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

export default {
  name: 'App',
  data(){
    return {myname: ref('rabah abderrazak')}
  },
  
  mounted() {
    // Initialize authentication when app starts
    const authStore = useAuthStore()
    authStore.initializeAuth()
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  
}
</style>

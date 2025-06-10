import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomePage from '@/pages/HomePage.vue'
import Dashboard from '@/pages/Dashboard.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomePage,
    meta: { requiresGuest: true } // Only accessible when not logged in
  },
  {
    path: '/dashboard',
    name: 'Dashboard', 
    component: Dashboard,
    meta: { requiresAuth: true } // Only accessible when logged in
  },
  // Redirect any unknown routes to home
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Wait for auth initialization if not already done
  if (authStore.token && !authStore.isAuthenticated) {
    try {
      await authStore.initializeAuth()
    } catch (error) {
      console.error('Auth initialization failed:', error)
    }
  }
  
  const isLoggedIn = authStore.isLoggedIn
  const requiresAuth = to.meta.requiresAuth
  const requiresGuest = to.meta.requiresGuest
  
  // If user is not logged in and trying to access any route except home
  if (!isLoggedIn && to.path !== '/') {
    // Redirect to home page
    next('/')
  } else if (requiresAuth && !isLoggedIn) {
    // Redirect to home if trying to access protected route without auth
    next('/')
  } else if (requiresGuest && isLoggedIn) {
    // Redirect to dashboard if trying to access guest-only route while logged in
    next('/dashboard')
  } else {
    next()
  }
})

export default router
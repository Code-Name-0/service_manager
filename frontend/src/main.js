import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import {createPinia} from 'pinia'
import router from './router'
import App from './App.vue'
import 'vuetify/styles'
import axios from 'axios'

import '@mdi/font/css/materialdesignicons.css'


axios.interceptors.request.use(

  (config) => {
    const token = localStorage.getItem('admin_token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`

    }
    return config
  },

  (error) => {
    return Promise.reject(error)
  }

)


import Echo from 'laravel-echo'

import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'fe2daf6ce51118fcacab',
  cluster: 'eu',
  forceTLS: true
})

const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light'
    }
})
const pinia = createPinia()
const app = createApp(App)

app.config.globalProperties.$backend = {
    baseApiUrl: "http://127.0.0.1:8000/api/admin",
    basePublicUrl: "http://127.0.0.1:8000/api"
}



app.use(vuetify).use(pinia).use(router)



app.mount('#app')
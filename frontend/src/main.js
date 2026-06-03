// Point d'entrée Vue : Pinia, router, reprise de session si token présent
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

import { useAuthStore } from './stores/auth'
import { setupApiInterceptors } from './api/setupInterceptors'

const auth = useAuthStore()
setupApiInterceptors(router)

if (auth.token) {
  auth.fetchUser().catch(() => auth.logout())
}

app.mount('#app')

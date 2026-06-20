import { createApp } from 'vue'
import { createPinia } from 'pinia'
import '@fortawesome/fontawesome-free/css/all.min.css'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/auth'
import './assets/main.css'

async function bootstrap() {
  const app = createApp(App)
  const pinia = createPinia()

  app.use(pinia)
  app.use(router)

  const auth = useAuthStore()
  await auth.tryAutoLogin()
  if (!auth.isAuthenticated) {
    auth.fetchCsrfCookie()
  }

  app.mount('#app')
}

bootstrap()

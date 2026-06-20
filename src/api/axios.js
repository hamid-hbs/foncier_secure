import axios from 'axios'

const csrfBaseURL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  withCredentials: true,
  headers: { Accept: 'application/json' },
})

api.interceptors.response.use(
  (res) => res,
  async (error) => {
    if (error.response?.status === 419) {
      try {
        await axios.get('/sanctum/csrf-cookie', { baseURL: csrfBaseURL, withCredentials: true })
        return api.request(error.config)
      } catch { /* fail */ }
    }
    if (error.response?.status === 401) {
      const { useAuthStore } = await import('@/stores/auth')
      useAuthStore().clearAuth()
    }
    return Promise.reject(error)
  }
)

export default api

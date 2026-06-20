import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import api from '@/api/axios'
import authApi from '@/api/auth'

const csrfBaseURL = import.meta.env.VITE_API_URL?.replace('/api', '') || ''

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)

  function setBearer(tokenValue) {
    token.value = tokenValue
    if (tokenValue) {
      api.defaults.headers.common['Authorization'] = `Bearer ${tokenValue}`
    } else {
      delete api.defaults.headers.common['Authorization']
    }
  }

  function clearAuth() {
    user.value = null
    token.value = null
    delete api.defaults.headers.common['Authorization']
    localStorage.removeItem('auth_token')
  }

  async function fetchCsrfCookie() {
    try {
      await axios.get('/sanctum/csrf-cookie', { baseURL: csrfBaseURL, withCredentials: true })
    } catch { /* CSRF not available in dev — safe to ignore */ }
  }

  async function tryAutoLogin() {
    const saved = localStorage.getItem('auth_token')
    if (!saved) return
    setBearer(saved)
    try {
      const res = await authApi.me()
      user.value = res.data.user || res.data
    } catch {
      clearAuth()
    }
  }

  async function login(email, password) {
    loading.value = true
    try {
      await fetchCsrfCookie()
      const res = await authApi.login(email, password)
      const t = res.data.token
      localStorage.setItem('auth_token', t)
      setBearer(t)
      user.value = res.data.user || res.data
      return res
    } finally {
      loading.value = false
    }
  }

  async function register(data) {
    loading.value = true
    try {
      await fetchCsrfCookie()
      const res = await authApi.register(data)
      const t = res.data.token
      localStorage.setItem('auth_token', t)
      setBearer(t)
      user.value = res.data.user || res.data
      return res
    } finally {
      loading.value = false
    }
  }

  async function fetchProfile() {
    try {
      const res = await authApi.me()
      user.value = res.data.user || res.data
    } catch {
      clearAuth()
    }
  }

  async function logout() {
    try { await authApi.logout() } catch { /* ignore */ }
    clearAuth()
  }

  async function updateProfile(data) {
    const res = await authApi.updateProfile(data)
    user.value = res.data.user || res.data
    return res
  }

  async function requestRole(data) {
    return await authApi.requestRole(data)
  }

  return {
    user, token, loading, isAuthenticated,
    fetchCsrfCookie, tryAutoLogin, login, register, fetchProfile, logout, updateProfile, requestRole, clearAuth,
  }
})

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

  const pendingApproval = computed(() => !!user.value && !user.value.is_active)

  const isAuthenticated = computed(() => !!token.value)

  const userRole = computed(() => {
    if (!user.value) return null
    return typeof user.value.role === 'object' ? user.value.role?.code : user.value.role
  })

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
    } catch { /* CSRF not available in dev */ }
  }

  async function tryAutoLogin() {
    const saved = localStorage.getItem('auth_token')
    if (!saved) return
    setBearer(saved)
    try {
      const res = await authApi.me()
      user.value = res.data
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
      user.value = res.data.user
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
      user.value = res.data.user
      return res
    } finally {
      loading.value = false
    }
  }

  async function fetchProfile() {
    try {
      const res = await authApi.me()
      user.value = res.data
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
    user.value = res.data
    return res
  }

  async function fetchDashboard() {
    return await authApi.dashboard()
  }

  async function deleteAccount() {
    await authApi.deleteAccount()
    clearAuth()
  }

  return {
    user, token, loading, pendingApproval, isAuthenticated, userRole,
    fetchCsrfCookie, tryAutoLogin, login, register, fetchProfile, logout, updateProfile, fetchDashboard, deleteAccount, clearAuth,
  }
})

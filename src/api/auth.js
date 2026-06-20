import api from './axios'

export default {
  login: (email, password) => api.post('/auth/login', { email, password }),
  register: (data) => api.post('/auth/register', data),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/profile'),
  updateProfile: (data) => api.put('/auth/profile', data),
  requestRole: (data) => api.post('/auth/request-role', data),
  sendOtp: (email) => api.post('/auth/forgot-password', { email }),
  resetPassword: (data) => api.post('/auth/reset-password', data),
}

import api from './axios'

export default {
  getStats: () => api.get('/observatoire'),
}

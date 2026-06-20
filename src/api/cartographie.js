import api from './axios'

export default {
  getLayers: (params) => api.get('/cartographie/couches', { params }),
}

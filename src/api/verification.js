import api from './axios'

export default {
  list: (params) => api.get('/verifications', { params }),
  show: (id) => api.get(`/verifications/${id}`),
  create: (data) => api.post('/verifications', data),
  rapport: (id) => api.get(`/verifications/${id}/rapport`, { responseType: 'blob' }),
  solliciterGeometre: (id) => api.post(`/verifications/${id}/solliciter-geometre`),
  rapportGeometre: (id, data) => api.post(`/verifications/${id}/rapport-geometre`, data),
  getMissions: () => api.get('/geometre/missions'),
}

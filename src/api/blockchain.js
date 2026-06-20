import api from './axios'

export default {
  getLogs: (params) => api.get('/blockchain', { params }),
  verify: () => api.get('/blockchain/verifier'),
  historiqueModule: (module, referenceId) => api.get(`/blockchain/module/${module}/${referenceId || ''}`),
}

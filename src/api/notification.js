import api from './axios'

export default {
  list: (params) => api.get('/notifications', { params }),
  nonLues: () => api.get('/notifications/non-lues'),
  markAsRead: (id) => api.patch(`/notifications/${id}/read`),
}

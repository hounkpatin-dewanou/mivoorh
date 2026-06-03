/** Appels API espace super-admin (/admin/...). */
import api from './axios'

export const fetchAdminEmployees = (params) => api.get('/admin/employees', { params })
export const createAdminEmployee = (payload) => api.post('/admin/employees', payload)
export const updateAdminEmployee = (id, payload) => api.put(`/admin/employees/${id}`, payload)
export const deleteAdminEmployee = (id) => api.delete(`/admin/employees/${id}`)

export const fetchAdminRequests = (params) => api.get('/admin/advance-requests', { params })
export const createAdminRequest = (payload) => api.post('/admin/advance-requests', payload)
export const updateAdminRequest = (id, payload) => api.put(`/admin/advance-requests/${id}`, payload)
export const deleteAdminRequest = (id) => api.delete(`/admin/advance-requests/${id}`)
export const approveAdminRequest = (id, review_comment) =>
  api.patch(`/admin/advance-requests/${id}/approve`, { review_comment })
export const refuseAdminRequest = (id, review_comment) =>
  api.patch(`/admin/advance-requests/${id}/refuse`, { review_comment })

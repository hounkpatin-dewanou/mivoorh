import api from './axios'

export const fetchPublicCompanies = () => api.get('/companies')
export const fetchAdminCompanies = () => api.get('/admin/companies')
export const fetchAdminStats = () => api.get('/admin/stats')
export const createCompany = (payload) => api.post('/admin/companies', payload)
export const toggleCompany = (id) => api.patch(`/admin/companies/${id}/toggle`)
export const updateCompany = (id, payload) => api.put(`/admin/companies/${id}`, payload)
export const deleteCompany = (id) => api.delete(`/admin/companies/${id}`)

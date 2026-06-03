/** API espace RH — gestion des employés de l'entreprise connectée. */
import api from './axios'

export const fetchEmployees = () => api.get('/hr/employees')
export const fetchEmployee = (id) => api.get(`/hr/employees/${id}`)
export const createEmployee = (payload) => api.post('/hr/employees', payload)
export const updateEmployee = (id, payload) => api.put(`/hr/employees/${id}`, payload)
export const deactivateEmployee = (id) => api.patch(`/hr/employees/${id}/deactivate`)
export const deleteEmployee = (id) => api.delete(`/hr/employees/${id}`)
export const fetchHrStats = () => api.get('/hr/stats')

import api from './axios'

export const fetchHrRequests = (status) =>
  api.get('/hr/advance-requests', { params: status ? { status } : {} })

export const approveRequest = (id, review_comment) =>
  api.patch(`/hr/advance-requests/${id}/approve`, { review_comment })

export const refuseRequest = (id, review_comment) =>
  api.patch(`/hr/advance-requests/${id}/refuse`, { review_comment })

export const fetchEmployeeRequests = () => api.get('/employee/advance-requests')
export const createEmployeeRequest = (payload) => api.post('/employee/advance-requests', payload)
export const fetchEmployeeBalance = () => api.get('/employee/balance')

export const downloadHrExportCsv = (month, year) =>
  api.get('/hr/export/csv', { params: { month, year }, responseType: 'blob' })

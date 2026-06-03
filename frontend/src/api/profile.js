import api from './axios'

export const fetchProfile = () => api.get('/profile')
export const updateProfile = (payload) => api.put('/profile', payload)

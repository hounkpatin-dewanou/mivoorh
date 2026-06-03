/**
 * Client HTTP partagé (base URL via VITE_API_BASE_URL).
 * Docker : /api — dev local : http://localhost:8000/api
 */
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// Token Sanctum stocké au login (voir stores/auth.js)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api

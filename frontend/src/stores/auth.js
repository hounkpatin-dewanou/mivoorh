/**
 * Session utilisateur (token Sanctum + rôle) persistée dans localStorage.
 */
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const role = ref(localStorage.getItem('role') || null)

  const isAuthenticated = computed(() => !!token.value)

  function setSession(authUser, authToken) {
    user.value = authUser
    token.value = authToken
    role.value = authUser?.role ?? null
    localStorage.setItem('token', authToken)
    localStorage.setItem('role', role.value ?? '')
  }

  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })
    setSession(data.user, data.token)
    return data
  }

  async function register(payload) {
    const { data } = await api.post('/register', payload)
    setSession(data.user, data.token)
    return data
  }

  async function fetchUser() {
    if (!token.value) return null
    const { data } = await api.get('/user')
    user.value = data
    role.value = data.role
    localStorage.setItem('role', data.role)
    return data
  }

  async function logout() {
    try {
      if (token.value) await api.post('/logout')
    } catch {
      /* ignore */
    }
    user.value = null
    token.value = null
    role.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('role')
  }

  return {
    user,
    token,
    role,
    isAuthenticated,
    login,
    register,
    fetchUser,
    logout,
    setSession,
  }
})

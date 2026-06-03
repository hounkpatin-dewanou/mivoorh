/**
 * Gestion centralisée des erreurs API : toasts, déconnexion si 401.
 */
import api from './axios'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

export function setupApiInterceptors(router) {
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      const toast = useToastStore()
      const status = error.response?.status
      const message =
        error.response?.data?.message ||
        (error.response?.data?.errors
          ? Object.values(error.response.data.errors).flat().join(' ')
          : null) ||
        'Une erreur est survenue. Réessayez plus tard.'

      if (status === 401) {
        const auth = useAuthStore()
        auth.logout()
        router.push({ name: 'login' })
        toast.error('Session expirée. Veuillez vous reconnecter.')
      } else if (status === 403) {
        toast.error('Vous n\'avez pas les droits pour cette action.')
      } else if (status === 422) {
        toast.error(message)
      } else if (status >= 500) {
        toast.error('Erreur serveur. Vérifiez que l\'API Laravel est démarrée.')
      } else if (!error.response) {
        toast.error('Impossible de joindre l\'API (réseau ou serveur arrêté).')
      } else {
        toast.error(message)
      }

      return Promise.reject(error)
    },
  )
}

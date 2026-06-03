/**
 * Routage SPA : pages publiques, espaces /hr, /employee, /admin protégés par rôle.
 */
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  { path: '/', name: 'landing', component: () => import('../views/LandingView.vue') },
  { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { guest: true } },
  { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue'), meta: { guest: true } },
  {
    path: '/dashboard',
    name: 'dashboard',
    redirect: () => {
      const auth = useAuthStore()
      const map = { superadmin: '/admin/companies', hr: '/hr/dashboard', employee: '/employee/dashboard' }
      return map[auth.role] || '/login'
    },
    meta: { requiresAuth: true },
  },
  {
    path: '/hr',
    component: () => import('../layouts/HrLayout.vue'),
    meta: { requiresAuth: true, roles: ['hr'] },
    children: [
      { path: 'dashboard', name: 'hr-dashboard', component: () => import('../views/hr/HrDashboardView.vue') },
      { path: 'employees', name: 'hr-employees', component: () => import('../views/hr/HrEmployeesView.vue') },
      { path: 'employees/:id', name: 'hr-employee-detail', component: () => import('../views/hr/HrEmployeeDetailView.vue') },
      { path: 'requests', name: 'hr-requests', component: () => import('../views/hr/HrRequestsView.vue') },
      { path: 'export', name: 'hr-export', component: () => import('../views/hr/HrExportView.vue') },
      { path: 'profile', name: 'hr-profile', component: () => import('../views/ProfileView.vue') },
    ],
  },
  {
    path: '/employee',
    component: () => import('../layouts/EmployeeLayout.vue'),
    meta: { requiresAuth: true, roles: ['employee'] },
    children: [
      { path: 'dashboard', name: 'employee-dashboard', component: () => import('../views/employee/EmployeeDashboardView.vue') },
      { path: 'request/new', name: 'employee-request-new', component: () => import('../views/employee/EmployeeNewRequestView.vue') },
      { path: 'history', name: 'employee-history', component: () => import('../views/employee/EmployeeHistoryView.vue') },
      { path: 'profile', name: 'employee-profile', component: () => import('../views/ProfileView.vue') },
    ],
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, roles: ['superadmin'] },
    children: [
      { path: '', redirect: '/admin/companies' },
      { path: 'companies', name: 'admin-companies', component: () => import('../views/admin/AdminCompaniesView.vue') },
      { path: 'employees', name: 'admin-employees', component: () => import('../views/admin/AdminEmployeesView.vue') },
      { path: 'requests', name: 'admin-requests', component: () => import('../views/admin/AdminRequestsView.vue') },
      { path: 'profile', name: 'admin-profile', component: () => import('../views/ProfileView.vue') },
    ],
  },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../views/NotFoundView.vue') },
]

const router = createRouter({ history: createWebHistory(), routes })

// Garde : auth obligatoire, invités redirigés si déjà connecté, contrôle des rôles
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) return next({ name: 'login' })
  if (to.meta.guest && auth.isAuthenticated) return next({ name: 'dashboard' })
  if (to.meta.roles && !to.meta.roles.includes(auth.role)) return next({ name: 'dashboard' })
  next()
})

export default router

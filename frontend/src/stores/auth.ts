import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { getToken, onUnauthorized, request, setToken } from '@/api/client'
import { callMethod } from '@/api/method'

export type SessionUser = {
  user: string
  full_name: string
  roles: string[]
  employee?: string
}

const HRD_ROLES = ['HR Manager', 'HR User', 'System Manager']

export const useAuthStore = defineStore('auth', () => {
  const session = ref<SessionUser | null>(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => session.value !== null)
  const roles = computed(() => session.value?.roles ?? [])
  const isHrd = computed(() => roles.value.some((role) => HRD_ROLES.includes(role)))

  function clear() {
    session.value = null
    setToken(null)
  }

  /**
   * Frappe login sets a session cookie; `juri_hr.auth.issue_token` then returns an
   * API key pair so the SPA can authenticate without relying on cookies.
   */
  async function login(email: string, password: string) {
    loading.value = true
    try {
      await request('/api/method/login', { method: 'POST', body: { usr: email, pwd: password } })
      const issued = await callMethod<{ token: string } & SessionUser>('juri_hr.auth.issue_token')
      setToken(issued.token)
      session.value = {
        user: issued.user,
        full_name: issued.full_name,
        roles: issued.roles,
        employee: issued.employee,
      }
    } catch (error) {
      clear()
      throw error
    } finally {
      loading.value = false
    }
  }

  async function loadSession() {
    if (!getToken()) {
      session.value = null
      return null
    }
    loading.value = true
    try {
      session.value = await callMethod<SessionUser>('juri_hr.auth.session')
      return session.value
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await request('/api/method/logout', { method: 'POST' })
    } finally {
      clear()
    }
  }

  onUnauthorized(clear)

  return { session, loading, isAuthenticated, roles, isHrd, login, loadSession, logout }
})

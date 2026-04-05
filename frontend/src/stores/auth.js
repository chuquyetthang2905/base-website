import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'

/**
 * Auth Store (Pinia)
 *
 * State:
 * - accessToken: JWT stored in memory only (NOT localStorage).
 *   Cleared on page reload — user must hit /refresh to get a new one.
 *   This is intentional: the HttpOnly cookie carries the long-lived session.
 * - user: the authenticated user object (id, name, email, roles, etc.)
 * - loading: tracks async operations (login, register, refresh)
 *
 * The refresh_token is NEVER accessible here — it lives in an HttpOnly cookie
 * managed entirely by the browser. We just call POST /auth/refresh and the
 * browser attaches the cookie automatically.
 */
export const useAuthStore = defineStore('auth', () => {
  // ─── State ─────────────────────────────────────────────────────────────────
  const accessToken = ref(null)
  const user = ref(null)
  const loading = ref(false)
  // True after the first refresh attempt on app boot.
  // Router guard waits for this before making auth decisions.
  const initialized = ref(false)

  // ─── Getters ───────────────────────────────────────────────────────────────
  const isAuthenticated = computed(() => !!accessToken.value)

  const userRoles = computed(() => user.value?.roles?.map((r) => r.name) ?? [])

  const hasRole = (role) => userRoles.value.includes(role)

  const hasAnyRole = (roles) => roles.some((r) => userRoles.value.includes(r))

  const isAdmin = computed(() => hasRole('admin'))

  // ─── Actions ───────────────────────────────────────────────────────────────

  /**
   * Register a new user.
   * Does NOT log in — user must explicitly login after register.
   */
  async function register(name, email, password, passwordConfirmation) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      return data
    } finally {
      loading.value = false
    }
  }

  /**
   * Login: Exchange credentials for JWT + HttpOnly refresh cookie.
   * Stores access token and user in memory.
   */
  async function login(email, password) {
    loading.value = true
    try {
      const { data } = await api.post('/auth/login', { email, password })
      accessToken.value = data.data.access_token
      user.value = data.data.user
      initialized.value = true
      // Register stores reference for interceptor access
      _registerStoreRef()
      return data
    } finally {
      loading.value = false
    }
  }

  /**
   * Silent refresh: Get a new access token using the HttpOnly cookie.
   * Called on app boot (if cookie exists) and by Axios interceptor on 401.
   */
  async function refresh() {
    loading.value = true
    try {
      const { data } = await api.post('/auth/refresh')
      accessToken.value = data.data.access_token
      user.value = data.data.user
      _registerStoreRef()
      return true
    } catch {
      // Refresh failed — treat as unauthenticated (cookie expired/revoked)
      _clearState()
      return false
    } finally {
      loading.value = false
      initialized.value = true
    }
  }

  /**
   * Logout: Blacklist JWT + revoke refresh cookie server-side.
   */
  async function logout() {
    loading.value = true
    try {
      await api.post('/auth/logout')
    } catch {
      // Even if server call fails, clear local state
    } finally {
      _clearState()
      loading.value = false
    }
  }

  /**
   * Fetch the current authenticated user from /me.
   * Used to refresh user data (e.g. after profile update).
   */
  async function fetchMe() {
    const { data } = await api.get('/auth/me')
    user.value = data.data
  }

  /**
   * Called by Axios interceptor after a successful silent refresh.
   */
  function setAccessToken(token) {
    accessToken.value = token
  }

  /**
   * Called by Axios interceptor when refresh fails — force logout.
   */
  function forceLogout() {
    _clearState()
    // Redirect to login — use window.location to avoid circular router import
    window.location.href = '/login'
  }

  // ─── Private helpers ───────────────────────────────────────────────────────

  function _clearState() {
    accessToken.value = null
    user.value = null
  }

  /**
   * Expose this store instance on window.__piniaStores__ so the Axios
   * interceptor can access it without a circular import.
   */
  function _registerStoreRef() {
    if (!window.__piniaStores__) window.__piniaStores__ = {}
    window.__piniaStores__.useAuthStore = useAuthStore
  }

  return {
    // State
    accessToken,
    user,
    loading,
    initialized,
    // Getters
    isAuthenticated,
    userRoles,
    isAdmin,
    // Methods
    hasRole,
    hasAnyRole,
    register,
    login,
    refresh,
    logout,
    fetchMe,
    setAccessToken,
    forceLogout,
  }
})

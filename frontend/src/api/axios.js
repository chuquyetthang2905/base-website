import axios from 'axios'

/**
 * Axios Instance
 *
 * Central HTTP client for all API calls to the Laravel backend.
 *
 * Design decisions:
 * - baseURL reads from .env (VITE_API_BASE_URL=/api/v1) so we never hardcode
 *   the backend URL in individual service files.
 * - withCredentials: true is REQUIRED for the HttpOnly refresh_token cookie
 *   to be sent and received by the browser automatically.
 * - Authorization header is NOT set here by default — it is injected per-request
 *   by the request interceptor, reading from the Pinia auth store.
 *
 * Interceptors:
 * - Request: Attach Bearer token if available in auth store
 * - Response: On 401, attempt one silent refresh then retry.
 *             On second 401, force logout.
 */

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // Must be true for browser to send/receive HttpOnly cookies cross-origin
  // (Vite proxy handles the actual cross-origin boundary in dev)
  withCredentials: true,
})

// ─── Request Interceptor ──────────────────────────────────────────────────────
// Attach the access token from Pinia store to every outgoing request.
// We import the store lazily here (not at module load time) to avoid
// circular dependency issues during app bootstrap.
api.interceptors.request.use((config) => {
  // Lazy import to avoid circular deps: api ↔ authStore
  const { useAuthStore } = window.__piniaStores__ || {}
  if (useAuthStore) {
    const auth = useAuthStore()
    if (auth.accessToken) {
      config.headers.Authorization = `Bearer ${auth.accessToken}`
    }
  }
  return config
})

// ─── Response Interceptor ─────────────────────────────────────────────────────
// On 401: try silent refresh once, then retry the original request.
// On second 401 (refresh failed): force logout.
let isRefreshing = false
let failedQueue = []

const processQueue = (error, token = null) => {
  failedQueue.forEach((prom) => {
    if (error) {
      prom.reject(error)
    } else {
      prom.resolve(token)
    }
  })
  failedQueue = []
}

api.interceptors.response.use(
  // Pass successful responses through untouched
  (response) => response,

  async (error) => {
    const originalRequest = error.config

    // Only handle 401 errors that haven't already been retried
    if (error.response?.status !== 401 || originalRequest._retry) {
      return Promise.reject(error)
    }

    // Skip refresh attempt for auth endpoints themselves
    // (login, register, refresh) to avoid infinite loops
    const isAuthEndpoint = originalRequest.url?.includes('/auth/')
    if (isAuthEndpoint) {
      return Promise.reject(error)
    }

    // If already refreshing, queue this request until refresh completes
    if (isRefreshing) {
      return new Promise((resolve, reject) => {
        failedQueue.push({ resolve, reject })
      }).then((token) => {
        originalRequest.headers.Authorization = `Bearer ${token}`
        return api(originalRequest)
      }).catch((err) => Promise.reject(err))
    }

    // Mark as retried and start refresh
    originalRequest._retry = true
    isRefreshing = true

    try {
      // POST /refresh — browser sends HttpOnly cookie automatically
      const { data } = await api.post('/auth/refresh')
      const newToken = data.data.access_token

      // Update the store with the new token
      const { useAuthStore } = window.__piniaStores__ || {}
      if (useAuthStore) {
        useAuthStore().setAccessToken(newToken)
      }

      // Process any requests that were queued while refreshing
      processQueue(null, newToken)

      // Retry the original request with the new token
      originalRequest.headers.Authorization = `Bearer ${newToken}`
      return api(originalRequest)
    } catch (refreshError) {
      processQueue(refreshError, null)

      // Refresh failed — force logout
      const { useAuthStore } = window.__piniaStores__ || {}
      if (useAuthStore) {
        useAuthStore().forceLogout()
      }

      return Promise.reject(refreshError)
    } finally {
      isRefreshing = false
    }
  }
)

export default api

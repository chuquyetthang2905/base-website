<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()

// ─── Form state ──────────────────────────────────────────────────────────────
const email    = ref('')
const password = ref('')
const showPassword = ref(false)

// ─── Error state ─────────────────────────────────────────────────────────────
const serverErrors  = ref({})
const generalError  = ref('')
const clientErrors  = ref({})

const emailError    = computed(() => clientErrors.value.email    || serverErrors.value.email?.[0]    || '')
const passwordError = computed(() => clientErrors.value.password || serverErrors.value.password?.[0] || '')

// ─── Client-side validation ──────────────────────────────────────────────────
function validate() {
  const errs = {}
  if (!email.value.trim())                          errs.email = 'Email is required.'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) errs.email = 'Enter a valid email address.'
  if (!password.value)                              errs.password = 'Password is required.'
  clientErrors.value = errs
  return Object.keys(errs).length === 0
}

// ─── Google Login ────────────────────────────────────────────────────────────
onMounted(() => {
  const script = document.createElement('script')
  script.src = 'https://accounts.google.com/gsi/client'
  script.async = true
  script.defer = true
  script.onload = () => {
    window.google.accounts.id.initialize({
      client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
      callback: async (response) => {
        try {
          await auth.loginWithGoogle(response.credential)
          const redirectTo = route.query.redirect || '/dashboard'
          router.push(redirectTo)
        } catch (err) {
          generalError.value = err.response?.data?.message ?? 'Google login failed.'
        }
      },
    })
    window.google.accounts.id.renderButton(
      document.getElementById('google-signin-btn'),
      { theme: 'outline', size: 'large', width: '100%', text: 'signin_with' }
    )
  }
  document.head.appendChild(script)
})

// ─── Submit ──────────────────────────────────────────────────────────────────
async function submit() {
  serverErrors.value = {}
  generalError.value = ''
  if (!validate()) return

  try {
    await auth.login(email.value, password.value)
    const redirectTo = route.query.redirect || '/dashboard'
    router.push(redirectTo)
  } catch (err) {
    const status = err.response?.status
    const body   = err.response?.data
    if (status === 422) {
      serverErrors.value = body.errors ?? {}
    } else if (status === 401) {
      generalError.value = 'Invalid email or password.'
    } else {
      generalError.value = body?.message ?? 'Something went wrong. Please try again.'
    }
  }
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-sm" style="width: 100%; max-width: 420px;">
      <div class="card-body p-4 p-md-5">

        <!-- Header -->
        <h1 class="h4 fw-bold text-center mb-1">Welcome back</h1>
        <p class="text-muted text-center mb-4">Sign in to your account</p>

        <!-- General error alert -->
        <div v-if="generalError" class="alert alert-danger py-2" role="alert">
          {{ generalError }}
        </div>

        <form @submit.prevent="submit" novalidate>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="form-control"
              :class="{ 'is-invalid': emailError }"
              placeholder="you@example.com"
              autocomplete="email"
              required
            />
            <div v-if="emailError" class="invalid-feedback">{{ emailError }}</div>
          </div>

          <!-- Password -->
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group" :class="{ 'is-invalid': passwordError }">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="form-control"
                :class="{ 'is-invalid': passwordError }"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
              >
                {{ showPassword ? '🙈' : '👁' }}
              </button>
            </div>
            <div v-if="passwordError" class="invalid-feedback d-block">{{ passwordError }}</div>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="btn btn-primary w-100"
            :disabled="auth.loading"
          >
            <span
              v-if="auth.loading"
              class="spinner-border spinner-border-sm me-2"
              role="status"
              aria-hidden="true"
            />
            {{ auth.loading ? 'Signing in…' : 'Sign in' }}
          </button>

        </form>

        <hr class="m">

          <!-- Login with Google — rendered by Google Identity Services -->
          <div id="google-signin-btn" class="d-flex justify-content-center"></div>

        <!-- Footer link -->
        <p class="text-center mt-4 mb-0 text-muted small">
          Don't have an account?
          <RouterLink to="/register" class="text-decoration-none">Create one</RouterLink>
        </p>

      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

// ─── Form state ──────────────────────────────────────────────────────────────
const name                 = ref('')
const email                = ref('')
const password             = ref('')
const passwordConfirmation = ref('')
const showPassword         = ref(false)

// ─── Error / success state ───────────────────────────────────────────────────
const serverErrors = ref({})
const clientErrors = ref({})
const generalError = ref('')
const successMsg   = ref('')

const nameError    = computed(() => clientErrors.value.name     || serverErrors.value.name?.[0]                 || '')
const emailError   = computed(() => clientErrors.value.email    || serverErrors.value.email?.[0]                || '')
const passwordError= computed(() => clientErrors.value.password || serverErrors.value.password?.[0]             || '')
const passwordConfirmationError = computed(() =>
  clientErrors.value.passwordConfirmation || serverErrors.value.password_confirmation?.[0] || ''
)

// ─── Client-side validation ──────────────────────────────────────────────────
function validate() {
  const errs = {}
  if (!name.value.trim())                                       errs.name = 'Name is required.'
  else if (name.value.trim().length < 2)                        errs.name = 'Name must be at least 2 characters.'
  if (!email.value.trim())                                      errs.email = 'Email is required.'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value))    errs.email = 'Enter a valid email address.'
  if (!password.value)                                          errs.password = 'Password is required.'
  else if (password.value.length < 8)                           errs.password = 'Password must be at least 8 characters.'
  else if (!/[a-zA-Z]/.test(password.value))                   errs.password = 'Password must contain at least one letter.'
  else if (!/[0-9]/.test(password.value))                      errs.password = 'Password must contain at least one number.'
  if (password.value !== passwordConfirmation.value)            errs.passwordConfirmation = 'Passwords do not match.'
  clientErrors.value = errs
  return Object.keys(errs).length === 0
}

// ─── Submit ──────────────────────────────────────────────────────────────────
async function submit() {
  serverErrors.value = {}
  generalError.value = ''
  successMsg.value   = ''
  if (!validate()) return

  try {
    await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
    successMsg.value = 'Account created! Redirecting to login…'
    setTimeout(() => router.push('/login'), 1500)
  } catch (err) {
    const status = err.response?.status
    const body   = err.response?.data
    if (status === 422) {
      serverErrors.value = body.errors ?? {}
    } else {
      generalError.value = body?.message ?? 'Something went wrong. Please try again.'
    }
  }
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow-sm" style="width: 100%; max-width: 460px;">
      <div class="card-body p-4 p-md-5">

        <!-- Header -->
        <h1 class="h4 fw-bold text-center mb-1">Create an account</h1>
        <p class="text-muted text-center mb-4">Get started for free</p>

        <!-- Success alert -->
        <div v-if="successMsg" class="alert alert-success py-2" role="alert">
          {{ successMsg }}
        </div>

        <!-- General error alert -->
        <div v-if="generalError" class="alert alert-danger py-2" role="alert">
          {{ generalError }}
        </div>

        <form @submit.prevent="submit" novalidate>

          <!-- Name -->
          <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input
              id="name"
              v-model="name"
              type="text"
              class="form-control"
              :class="{ 'is-invalid': nameError }"
              placeholder="Nguyen Van A"
              autocomplete="name"
              required
            />
            <div v-if="nameError" class="invalid-feedback">{{ nameError }}</div>
          </div>

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
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="form-control"
                :class="{ 'is-invalid': passwordError }"
                placeholder="Min. 8 characters with letters & numbers"
                autocomplete="new-password"
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

          <!-- Confirm password -->
          <div class="mb-4">
            <label for="password-confirmation" class="form-label">Confirm password</label>
            <input
              id="password-confirmation"
              v-model="passwordConfirmation"
              :type="showPassword ? 'text' : 'password'"
              class="form-control"
              :class="{ 'is-invalid': passwordConfirmationError }"
              placeholder="••••••••"
              autocomplete="new-password"
              required
            />
            <div v-if="passwordConfirmationError" class="invalid-feedback d-block">
              {{ passwordConfirmationError }}
            </div>
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
            {{ auth.loading ? 'Creating account…' : 'Create account' }}
          </button>

        </form>

        <!-- Footer link -->
        <p class="text-center mt-4 mb-0 text-muted small">
          Already have an account?
          <RouterLink to="/login" class="text-decoration-none">Sign in</RouterLink>
        </p>

      </div>
    </div>
  </div>
</template>


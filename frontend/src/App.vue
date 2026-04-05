<script setup>
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
</script>

<template>
  <!--
    Show a full-screen spinner while the app checks for an existing session
    (POST /auth/refresh using the HttpOnly cookie).

    Without this, users see a flash: dashboard → redirect → login → redirect back.
    With this, the redirect only happens AFTER we know the auth state.
  -->
  <div v-if="!auth.initialized" class="min-vh-100 d-flex align-items-center justify-content-center">
    <div class="text-center">
      <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading…</span>
      </div>
      <p class="text-muted small">Loading…</p>
    </div>
  </div>

  <RouterView v-else />
</template>

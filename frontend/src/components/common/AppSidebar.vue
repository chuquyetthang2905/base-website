<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps({ open: Boolean })

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

const navItems = [
  { to: '/dashboard', icon: 'bi-grid-1x2-fill', label: 'Dashboard' },
  { to: '/profile',   icon: 'bi-person-circle',  label: 'Profile'   },
  { to: '/settings',  icon: 'bi-gear-fill',       label: 'Settings'  },
]

const adminItems = [
  { to: '/admin', icon: 'bi-shield-check', label: 'Admin Panel' },
]

const isActive = (path) =>
  route.path === path || route.path.startsWith(path + '/')

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <aside class="app-sidebar" :class="{ 'is-open': open }">

    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="logo-icon">
        <i class="bi bi-lightning-charge-fill"></i>
      </div>
      <span class="logo-text">BaseApp</span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="nav-label">Main Menu</div>

      <router-link
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="nav-item"
        :class="{ active: isActive(item.to) }"
      >
        <i :class="['bi', item.icon]" class="nav-icon"></i>
        <span>{{ item.label }}</span>
      </router-link>

      <template v-if="auth.isAdmin">
        <div class="nav-label mt-4">Administration</div>
        <router-link
          v-for="item in adminItems"
          :key="item.to"
          :to="item.to"
          class="nav-item"
          :class="{ active: isActive(item.to) }"
        >
          <i :class="['bi', item.icon]" class="nav-icon"></i>
          <span>{{ item.label }}</span>
        </router-link>
      </template>
    </nav>

    <!-- User footer -->
    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar">
          {{ auth.user?.name?.charAt(0).toUpperCase() ?? '?' }}
        </div>
        <div class="user-meta">
          <div class="user-name">{{ auth.user?.name }}</div>
          <div class="user-email">{{ auth.user?.email }}</div>
        </div>
      </div>
      <button class="logout-btn" title="Logout" @click="handleLogout">
        <i class="bi bi-box-arrow-right"></i>
      </button>
    </div>

  </aside>
</template>

<style scoped>
/* ── Shell ─────────────────────────────────────────── */
.app-sidebar {
  position: fixed;
  inset-block: 0;
  left: 0;
  width: var(--sidebar-width);
  background: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  transition: transform 0.3s ease;
}

/* ── Logo ─────────────────────────────────────────── */
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.375rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  flex-shrink: 0;
}

.logo-icon {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: #fff;
  flex-shrink: 0;
}

.logo-text {
  font-size: 1.2rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.3px;
}

/* ── Nav ───────────────────────────────────────────── */
.sidebar-nav {
  flex: 1;
  padding: 1rem 0.75rem;
  overflow-y: auto;
  overflow-x: hidden;
}

.nav-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.28);
  padding: 0.25rem 0.5rem 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.6rem 0.875rem;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.55);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.18s ease;
  margin-bottom: 2px;
}

.nav-icon {
  font-size: 1rem;
  flex-shrink: 0;
  width: 20px;
  text-align: center;
}

.nav-item:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.07);
}

.nav-item.active {
  color: #fff;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.75), rgba(139, 92, 246, 0.5));
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
}

/* ── Footer ─────────────────────────────────────────── */
.sidebar-footer {
  padding: 0.875rem 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex: 1;
  min-width: 0;
}

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
  color: #fff;
  flex-shrink: 0;
}

.user-meta {
  min-width: 0;
}

.user-name {
  color: #fff;
  font-size: 0.82rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  color: rgba(255, 255, 255, 0.38);
  font-size: 0.7rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.45);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.18s;
  flex-shrink: 0;
  font-size: 0.95rem;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

/* ── Mobile ─────────────────────────────────────────── */
@media (max-width: 991px) {
  .app-sidebar {
    transform: translateX(-100%);
  }

  .app-sidebar.is-open {
    transform: translateX(0);
  }
}
</style>

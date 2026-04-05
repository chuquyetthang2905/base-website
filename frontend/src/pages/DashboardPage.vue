<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import StatCard  from '@/components/common/StatCard.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const activities = [
  { icon: 'bi-person-plus-fill', text: 'New user registered',        time: '2 min ago',   color: 'primary' },
  { icon: 'bi-box-arrow-in-right', text: 'Login from new device',    time: '15 min ago',  color: 'info'    },
  { icon: 'bi-shield-check',     text: 'Security scan completed',    time: '1 hour ago',  color: 'success' },
  { icon: 'bi-exclamation-circle-fill', text: 'Failed login attempt', time: '2 hours ago', color: 'warning' },
  { icon: 'bi-key-fill',         text: 'Password changed',           time: '1 day ago',   color: 'danger'  },
]

const dateStr = new Date().toLocaleDateString('en-US', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
})
</script>

<template>
  <AppLayout pageTitle="Dashboard">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
      <div class="welcome-text">
        <h2>Welcome back, {{ auth.user?.name ?? 'there' }} 👋</h2>
        <p>Here's what's happening with your account today.</p>
      </div>
      <div class="welcome-date">{{ dateStr }}</div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
      <StatCard icon="bi-people-fill"       label="Total Users"      value="1,284" trend="+12%"  color="primary" />
      <StatCard icon="bi-lightning-charge-fill" label="Active Sessions" value="48"    trend="+5%"   color="success" />
      <StatCard icon="bi-graph-up-arrow"    label="API Requests"     value="24.5K" trend="+8%"   color="info"    />
      <StatCard icon="bi-clock-history"     label="Uptime"           value="99.9%" trend="+0.1%" color="warning" />
    </div>

    <!-- Content Grid -->
    <div class="content-grid">

      <!-- Recent Activity -->
      <div class="panel">
        <div class="panel-header">
          <h3 class="panel-title">Recent Activity</h3>
          <button class="btn-link">View all</button>
        </div>
        <div class="activity-list">
          <div v-for="(item, i) in activities" :key="i" class="activity-item">
            <div class="activity-icon" :class="`aicon-${item.color}`">
              <i :class="['bi', item.icon]"></i>
            </div>
            <div class="activity-body">
              <div class="activity-text">{{ item.text }}</div>
              <div class="activity-time">{{ item.time }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Card -->
      <div class="panel">
        <div class="panel-header">
          <h3 class="panel-title">My Profile</h3>
          <button class="btn-link">Edit</button>
        </div>

        <div class="profile-hero">
          <div class="profile-avatar-lg">
            {{ auth.user?.name?.charAt(0).toUpperCase() ?? '?' }}
          </div>
          <div class="profile-name">{{ auth.user?.name }}</div>
          <div class="profile-email">{{ auth.user?.email }}</div>
          <div class="profile-roles">
            <span v-for="role in auth.user?.roles" :key="role.name" class="role-pill">
              {{ role.name }}
            </span>
            <span v-if="!auth.user?.roles?.length" class="role-pill">user</span>
          </div>
        </div>

        <div class="profile-stats">
          <div class="pstat">
            <div class="pstat-value">{{ auth.user?.roles?.length ?? 0 }}</div>
            <div class="pstat-label">Roles</div>
          </div>
          <div class="pstat">
            <div class="pstat-value">0</div>
            <div class="pstat-label">Posts</div>
          </div>
          <div class="pstat">
            <div class="pstat-value" :class="auth.user?.is_active ? 'text-success' : 'text-danger'">
              {{ auth.user?.is_active ? 'Active' : 'Off' }}
            </div>
            <div class="pstat-label">Status</div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
/* ── Welcome Banner ─────────────────────────────────── */
.welcome-banner {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 55%, #06b6d4 100%);
  border-radius: 20px;
  padding: 2rem 2.5rem;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.75rem;
  overflow: hidden;
  position: relative;
}

.welcome-banner::before,
.welcome-banner::after {
  content: '';
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.05);
  pointer-events: none;
}

.welcome-banner::before { width: 220px; height: 220px; top: -60px;  right: 60px; }
.welcome-banner::after  { width: 160px; height: 160px; bottom: -50px; right: -30px; }

.welcome-text h2 {
  font-size: 1.45rem;
  font-weight: 700;
  margin: 0 0 0.35rem;
}

.welcome-text p {
  margin: 0;
  opacity: 0.82;
  font-size: 0.9rem;
}

.welcome-date {
  font-size: 0.82rem;
  opacity: 0.7;
  text-align: right;
  flex-shrink: 0;
}

/* ── Stats ──────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
  margin-bottom: 1.75rem;
}

@media (max-width: 1199px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px)  { .stats-grid { grid-template-columns: 1fr; } }

/* ── Content Grid ───────────────────────────────────── */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1.25rem;
}

@media (max-width: 1099px) { .content-grid { grid-template-columns: 1fr; } }

/* ── Panel ──────────────────────────────────────────── */
.panel {
  background: #fff;
  border-radius: 18px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.panel-title {
  font-size: 0.975rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.btn-link {
  border: none;
  background: none;
  color: var(--primary);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.btn-link:hover { text-decoration: underline; }

/* ── Activity ───────────────────────────────────────── */
.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.825rem 0;
  border-bottom: 1px solid var(--border);
}

.activity-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #fff;
  flex-shrink: 0;
}

.aicon-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.aicon-success { background: linear-gradient(135deg, #10b981, #34d399); }
.aicon-warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.aicon-danger  { background: linear-gradient(135deg, #ef4444, #f87171); }
.aicon-info    { background: linear-gradient(135deg, #3b82f6, #60a5fa); }

.activity-text { font-size: 0.865rem; color: var(--text);       font-weight: 500; }
.activity-time { font-size: 0.75rem;  color: var(--text-muted); margin-top: 0.1rem; }

/* ── Profile card ───────────────────────────────────── */
.profile-hero {
  text-align: center;
  padding: 1.25rem 0 1rem;
}

.profile-avatar-lg {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
  margin: 0 auto 0.875rem;
  box-shadow: 0 8px 20px rgba(99,102,241,0.33);
}

.profile-name  { font-size: 1.05rem; font-weight: 700; color: var(--text); }
.profile-email { font-size: 0.82rem; color: var(--text-muted); margin-top: 0.15rem; }

.profile-roles {
  display: flex;
  justify-content: center;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-top: 0.75rem;
}

.role-pill {
  background: var(--primary-light);
  color: var(--primary);
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.2rem 0.7rem;
  border-radius: 100px;
  text-transform: capitalize;
}

.profile-stats {
  display: flex;
  border-top: 1px solid var(--border);
  margin-top: 1rem;
  padding-top: 1rem;
}

.pstat {
  flex: 1;
  text-align: center;
}

.pstat:not(:last-child) { border-right: 1px solid var(--border); }

.pstat-value { font-size: 1.2rem; font-weight: 800; color: var(--text); }
.pstat-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem; }
</style>


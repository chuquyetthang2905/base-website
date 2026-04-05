<script setup>
defineProps({
  pageTitle: { type: String, default: '' },
})

const emit = defineEmits(['toggle'])
</script>

<template>
  <header class="app-navbar">
    <div class="navbar-left">
      <!-- Mobile hamburger -->
      <button class="icon-btn d-lg-none" aria-label="Menu" @click="emit('toggle')">
        <i class="bi bi-list"></i>
      </button>

      <div class="page-title-wrap">
        <h1 class="page-title">{{ pageTitle }}</h1>
      </div>
    </div>

    <div class="navbar-right">
      <button class="icon-btn" aria-label="Search">
        <i class="bi bi-search"></i>
      </button>

      <button class="icon-btn notif-btn" aria-label="Notifications">
        <i class="bi bi-bell"></i>
        <span class="badge-dot"></span>
      </button>
    </div>
  </header>
</template>

<style scoped>
.app-navbar {
  height: 64px;
  background: #fff;
  border-bottom: 1px solid var(--border);
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
  flex-shrink: 0;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.page-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon-btn {
  width: 38px;
  height: 38px;
  border: 1px solid var(--border);
  background: #fff;
  border-radius: 10px;
  font-size: 1rem;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.18s;
  position: relative;
}

.icon-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
  background: var(--primary-light);
}

.badge-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 8px;
  height: 8px;
  background: var(--danger);
  border-radius: 50%;
  border: 2px solid #fff;
}
</style>

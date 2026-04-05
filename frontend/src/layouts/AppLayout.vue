<script setup>
import { ref } from 'vue'
import AppSidebar from '@/components/common/AppSidebar.vue'
import AppNavbar from '@/components/common/AppNavbar.vue'

defineProps({
  pageTitle: { type: String, default: 'Dashboard' },
})

const sidebarOpen = ref(false)
</script>

<template>
  <div class="app-layout">
    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="sidebar-overlay"
      @click="sidebarOpen = false"
    />

    <AppSidebar :open="sidebarOpen" />

    <div class="app-content">
      <AppNavbar :pageTitle="pageTitle" @toggle="sidebarOpen = !sidebarOpen" />
      <main class="app-main">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg);
}

.app-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: var(--sidebar-width);
  min-width: 0;
  transition: margin-left 0.3s ease;
}

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

.app-main {
  flex: 1;
  padding: 2rem;
  overflow-x: hidden;
}

@media (max-width: 991px) {
  .app-content {
    margin-left: 0;
  }
}
</style>

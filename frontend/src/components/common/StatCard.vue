<script setup>
defineProps({
  icon:  { type: String,           required: true },
  label: { type: String,           required: true },
  value: { type: [String, Number], required: true },
  trend: { type: String,           default: null  }, // e.g. '+12%' or '-5%'
  color: { type: String,           default: 'primary' }, // primary | success | warning | danger | info
})
</script>

<template>
  <div class="stat-card" :class="`color-${color}`">
    <div class="stat-icon">
      <i :class="['bi', icon]"></i>
    </div>

    <div class="stat-body">
      <div class="stat-label">{{ label }}</div>
      <div class="stat-value">{{ value }}</div>
    </div>

    <div
      v-if="trend"
      class="stat-trend"
      :class="trend.startsWith('+') ? 'up' : 'down'"
    >
      <i :class="trend.startsWith('+') ? 'bi-arrow-up-right' : 'bi-arrow-down-right'"></i>
      {{ trend }}
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 1.375rem 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.09);
}

/* Left accent bar */
.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  border-radius: 16px 0 0 16px;
}

/* Color variants */
.color-primary .stat-icon { background: var(--primary-light); color: var(--primary); }
.color-primary::before    { background: var(--primary); }

.color-success .stat-icon { background: rgba(16,185,129,0.1); color: var(--success); }
.color-success::before    { background: var(--success); }

.color-warning .stat-icon { background: rgba(245,158,11,0.1); color: var(--warning); }
.color-warning::before    { background: var(--warning); }

.color-danger  .stat-icon { background: rgba(239,68,68,0.1);  color: var(--danger); }
.color-danger::before     { background: var(--danger); }

.color-info    .stat-icon { background: rgba(59,130,246,0.1); color: var(--info); }
.color-info::before       { background: var(--info); }

/* Icon */
.stat-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}

/* Body */
.stat-body {
  flex: 1;
  min-width: 0;
}

.stat-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin-bottom: 0.3rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-value {
  font-size: 1.65rem;
  font-weight: 800;
  color: var(--text);
  line-height: 1;
}

/* Trend badge */
.stat-trend {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 0.15rem;
}

.stat-trend.up   { background: rgba(16,185,129,0.1); color: var(--success); }
.stat-trend.down { background: rgba(239,68,68,0.1);  color: var(--danger);  }
</style>

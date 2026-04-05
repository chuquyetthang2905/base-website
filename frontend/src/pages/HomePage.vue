<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const scrolled = ref(false)

function onScroll() {
  scrolled.value = window.scrollY > 24
}

onMounted(()  => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))

const features = [
  {
    icon: 'bi-shield-lock-fill',
    title: 'Secure by Default',
    desc: 'JWT auth with HttpOnly refresh cookies, token rotation, and theft detection built-in from day one.',
    color: 'indigo',
  },
  {
    icon: 'bi-lightning-charge-fill',
    title: 'Blazing Fast',
    desc: 'Vue 3 + Vite for instant HMR and optimized builds. Laravel 12 API with sub-millisecond routing.',
    color: 'violet',
  },
  {
    icon: 'bi-people-fill',
    title: 'Roles & Permissions',
    desc: 'Flexible role-based access control. Protect routes and API endpoints with a single middleware.',
    color: 'cyan',
  },
  {
    icon: 'bi-code-slash',
    title: 'Clean Architecture',
    desc: 'Repository pattern, DTOs, and service layer keep your business logic decoupled and testable.',
    color: 'green',
  },
  {
    icon: 'bi-arrow-repeat',
    title: 'Silent Token Refresh',
    desc: 'Automatic access-token renewal with a request queue — users stay logged in transparently.',
    color: 'orange',
  },
  {
    icon: 'bi-grid-1x2-fill',
    title: 'Modular Design',
    desc: 'Every feature is a self-contained module. Extend, remove, or swap components with ease.',
    color: 'rose',
  },
]

const stats = [
  { value: '99.9%',  label: 'Uptime SLA'      },
  { value: '<15ms',  label: 'Avg Response'     },
  { value: '2-in-1', label: 'Full-Stack Kit'   },
  { value: '6+',     label: 'Security Guards'  },
]
</script>

<template>
  <div class="home-page">

    <!-- ── Navbar ──────────────────────────────────────── -->
    <nav class="home-nav" :class="{ scrolled }">
      <div class="nav-container">
        <div class="nav-brand">
          <div class="brand-icon">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <span>BaseApp</span>
        </div>

        <div class="nav-links">
          <a href="#features">Features</a>
          <a href="#stats">About</a>
        </div>

        <div class="nav-actions">
          <router-link to="/login"    class="btn-nav-ghost">Sign in</router-link>
          <router-link to="/register" class="btn-nav-primary">Get Started</router-link>
        </div>
      </div>
    </nav>

    <!-- ── Hero ───────────────────────────────────────── -->
    <section class="hero">
      <div class="hero-orb orb-1"></div>
      <div class="hero-orb orb-2"></div>
      <div class="hero-orb orb-3"></div>

      <div class="hero-content">
        <div class="hero-badge">
          <i class="bi bi-lightning-charge-fill"></i>
          Laravel 12 &amp; Vue 3 — production ready
        </div>

        <h1 class="hero-title">
          The modern<br>
          <span class="gradient-text">full-stack starter</span><br>
          you've been waiting for
        </h1>

        <p class="hero-desc">
          Production-ready authentication, role-based access control,
          and clean architecture — all in one boilerplate so you can
          ship features, not scaffolding.
        </p>

        <div class="hero-actions">
          <router-link to="/register" class="btn-hero-primary">
            Get Started Free
            <i class="bi bi-arrow-right"></i>
          </router-link>
          <router-link to="/login" class="btn-hero-outline">
            Sign In
          </router-link>
        </div>

        <div class="hero-trust">
          <span class="trust-item"><i class="bi bi-check-circle-fill"></i> JWT Auth</span>
          <span class="trust-item"><i class="bi bi-check-circle-fill"></i> Token Rotation</span>
          <span class="trust-item"><i class="bi bi-check-circle-fill"></i> Role Guards</span>
          <span class="trust-item"><i class="bi bi-check-circle-fill"></i> CORS Configured</span>
        </div>
      </div>
    </section>

    <!-- ── Stats ──────────────────────────────────────── -->
    <section class="stats-section" id="stats">
      <div class="section-wrap">
        <div class="stats-grid">
          <div v-for="s in stats" :key="s.label" class="stat-item">
            <div class="stat-val">{{ s.value }}</div>
            <div class="stat-lbl">{{ s.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Features ───────────────────────────────────── -->
    <section class="features-section" id="features">
      <div class="section-wrap">
        <div class="section-tag">Features</div>
        <h2 class="section-heading">Everything you need out of the box</h2>
        <p class="section-sub">
          A comprehensive foundation for building secure, scalable web applications.
        </p>

        <div class="features-grid">
          <div
            v-for="feat in features"
            :key="feat.title"
            class="feature-card"
          >
            <div class="feat-icon" :class="`icon-${feat.color}`">
              <i :class="['bi', feat.icon]"></i>
            </div>
            <h3>{{ feat.title }}</h3>
            <p>{{ feat.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA ────────────────────────────────────────── -->
    <section class="cta-section">
      <div class="cta-orb"></div>
      <div class="section-wrap cta-inner">
        <h2>Ready to build something great?</h2>
        <p>Create your account in seconds. No credit card required.</p>
        <router-link to="/register" class="btn-cta">
          Create Free Account
          <i class="bi bi-arrow-right"></i>
        </router-link>
      </div>
    </section>

    <!-- ── Footer ─────────────────────────────────────── -->
    <footer class="home-footer">
      <div class="section-wrap footer-inner">
        <div class="footer-brand">
          <div class="brand-icon">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <span>BaseApp</span>
        </div>
        <div class="footer-links">
          <router-link to="/login">Login</router-link>
          <router-link to="/register">Register</router-link>
        </div>
      </div>
      <div class="footer-copy">
        © {{ new Date().getFullYear() }} BaseApp — Built with Laravel 12 &amp; Vue 3
      </div>
    </footer>

  </div>
</template>

<style scoped>
/* ── Reset & vars ─────────────────────────────────────────────────────── */
.home-page {
  --h-primary:   #6366f1;
  --h-violet:    #8b5cf6;
  --h-cyan:      #06b6d4;
  --h-dark:      #0f172a;
  --h-text-muted: #64748b;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: var(--h-dark);
}

.section-wrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* ── Navbar ─────────────────────────────────────────────────────────────── */
.home-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 900;
  padding: 1.25rem 0;
  transition: padding 0.3s, background 0.3s, box-shadow 0.3s;
}

.home-nav.scrolled {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(20px);
  box-shadow: 0 1px 20px rgba(0, 0, 0, 0.07);
  padding: 0.7rem 0;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  gap: 2.5rem;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 1.2rem;
  font-weight: 800;
  color: var(--h-dark);
  text-decoration: none;
}

.brand-icon {
  width: 34px;
  height: 34px;
  background: linear-gradient(135deg, var(--h-primary), var(--h-violet));
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.95rem;
  flex-shrink: 0;
}

.nav-links {
  flex: 1;
  display: flex;
  gap: 2rem;
}

.nav-links a {
  color: var(--h-text-muted);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: color 0.2s;
}

.nav-links a:hover { color: var(--h-primary); }

.nav-actions {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.btn-nav-ghost {
  padding: 0.45rem 1.1rem;
  border-radius: 9px;
  color: var(--h-dark);
  font-weight: 600;
  font-size: 0.875rem;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
}

.btn-nav-ghost:hover {
  background: rgba(99, 102, 241, 0.07);
  color: var(--h-primary);
}

.btn-nav-primary {
  padding: 0.45rem 1.1rem;
  border-radius: 9px;
  background: linear-gradient(135deg, var(--h-primary), var(--h-violet));
  color: #fff;
  font-weight: 600;
  font-size: 0.875rem;
  text-decoration: none;
  transition: opacity 0.2s, transform 0.2s;
}

.btn-nav-primary:hover {
  opacity: 0.88;
  transform: translateY(-1px);
}

/* ── Hero ────────────────────────────────────────────────────────────── */
.hero {
  min-height: 100vh;
  background: linear-gradient(160deg, #fff 0%, #f0f0ff 45%, #e4f4ff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 9rem 2rem 6rem;
  position: relative;
  overflow: hidden;
}

.hero-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(90px);
  opacity: 0.3;
  pointer-events: none;
}

.orb-1 {
  width: 550px;
  height: 550px;
  background: radial-gradient(circle, var(--h-primary), transparent 70%);
  top: -180px;
  left: -150px;
}

.orb-2 {
  width: 420px;
  height: 420px;
  background: radial-gradient(circle, var(--h-violet), transparent 70%);
  top: 80px;
  right: -120px;
}

.orb-3 {
  width: 320px;
  height: 320px;
  background: radial-gradient(circle, var(--h-cyan), transparent 70%);
  bottom: 40px;
  left: 35%;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: rgba(99, 102, 241, 0.09);
  color: var(--h-primary);
  border: 1px solid rgba(99, 102, 241, 0.2);
  padding: 0.375rem 1rem;
  border-radius: 100px;
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 1.75rem;
}

.hero-title {
  font-size: clamp(2.6rem, 6vw, 4.75rem);
  font-weight: 800;
  line-height: 1.08;
  letter-spacing: -1.5px;
  margin-bottom: 1.5rem;
}

.gradient-text {
  background: linear-gradient(135deg, var(--h-primary) 0%, var(--h-violet) 50%, var(--h-cyan) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-desc {
  font-size: 1.1rem;
  color: var(--h-text-muted);
  max-width: 580px;
  margin: 0 auto 2.5rem;
  line-height: 1.72;
}

.hero-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 2rem;
}

.btn-hero-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, var(--h-primary), var(--h-violet));
  color: #fff;
  font-weight: 700;
  font-size: 1rem;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.35);
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-hero-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 40px rgba(99, 102, 241, 0.45);
}

.btn-hero-outline {
  display: inline-flex;
  align-items: center;
  padding: 0.875rem 2rem;
  border: 2px solid rgba(99, 102, 241, 0.3);
  color: var(--h-primary);
  font-weight: 700;
  font-size: 1rem;
  border-radius: 14px;
  text-decoration: none;
  transition: border-color 0.2s, background 0.2s;
}

.btn-hero-outline:hover {
  border-color: var(--h-primary);
  background: rgba(99, 102, 241, 0.05);
}

.hero-trust {
  display: flex;
  justify-content: center;
  gap: 1.25rem;
  flex-wrap: wrap;
}

.trust-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: var(--h-text-muted);
  font-weight: 500;
}

.trust-item i { color: #10b981; }

/* ── Stats ─────────────────────────────────────────────────────────────── */
.stats-section {
  background: var(--h-dark);
  padding: 4.5rem 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  text-align: center;
}

.stat-item {}

.stat-val {
  font-size: 2.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.4rem;
  line-height: 1;
}

.stat-lbl {
  color: rgba(255, 255, 255, 0.45);
  font-size: 0.875rem;
  font-weight: 500;
}

/* ── Features ──────────────────────────────────────────────────────────── */
.features-section {
  padding: 6.5rem 0;
  background: #fff;
}

.section-tag {
  display: inline-block;
  background: rgba(99, 102, 241, 0.08);
  color: var(--h-primary);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  padding: 0.3rem 0.875rem;
  border-radius: 6px;
  margin-bottom: 1rem;
}

.section-heading {
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 800;
  letter-spacing: -0.5px;
  margin-bottom: 0.75rem;
}

.section-sub {
  color: var(--h-text-muted);
  font-size: 1.05rem;
  max-width: 520px;
  margin-bottom: 3.5rem;
  line-height: 1.65;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.375rem;
}

.feature-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 1.75rem;
  transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s, border-color 0.22s;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
  border-color: transparent;
  background: #fff;
}

.feat-icon {
  width: 50px;
  height: 50px;
  border-radius: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  margin-bottom: 1.25rem;
}

.icon-indigo { background: rgba(99,102,241,0.1); color: #6366f1; }
.icon-violet { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.icon-cyan   { background: rgba(6,182,212,0.1);  color: #06b6d4; }
.icon-green  { background: rgba(16,185,129,0.1); color: #10b981; }
.icon-orange { background: rgba(245,158,11,0.1); color: #f59e0b; }
.icon-rose   { background: rgba(244,63,94,0.1);  color: #f43f5e; }

.feature-card h3 {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.feature-card p {
  font-size: 0.875rem;
  color: var(--h-text-muted);
  line-height: 1.65;
  margin: 0;
}

/* ── CTA ──────────────────────────────────────────────────────────────── */
.cta-section {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 55%, #0891b2 100%);
  padding: 7rem 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.cta-orb {
  position: absolute;
  width: 700px;
  height: 700px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  top: -250px;
  left: -100px;
  pointer-events: none;
}

.cta-inner {
  position: relative;
  z-index: 1;
}

.cta-section h2 {
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
}

.cta-section p {
  color: rgba(255, 255, 255, 0.78);
  font-size: 1.1rem;
  margin-bottom: 2.5rem;
}

.btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2.5rem;
  background: #fff;
  color: var(--h-primary);
  font-weight: 700;
  font-size: 1.05rem;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.24);
}

/* ── Footer ───────────────────────────────────────────────────────────── */
.home-footer {
  background: var(--h-dark);
  padding: 2.5rem 0 2rem;
}

.footer-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.footer-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #fff;
  font-size: 1.1rem;
  font-weight: 700;
}

.footer-links {
  display: flex;
  gap: 2rem;
}

.footer-links a {
  color: rgba(255, 255, 255, 0.45);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s;
}

.footer-links a:hover { color: #fff; }

.footer-copy {
  color: rgba(255, 255, 255, 0.3);
  font-size: 0.78rem;
  text-align: center;
}

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 991px) {
  .nav-links { display: none; }
}

@media (max-width: 767px) {
  .stats-grid     { grid-template-columns: repeat(2, 1fr); }
  .features-grid  { grid-template-columns: 1fr; }
}

@media (max-width: 1023px) {
  .features-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

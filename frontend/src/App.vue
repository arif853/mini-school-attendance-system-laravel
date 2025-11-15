<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useUiStore } from './stores/ui'
import { useAuthStore } from './stores/auth'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()
const authStore = useAuthStore()

onMounted(() => {
  uiStore.initTheme()
})

const links = [
  { label: 'Dashboard', to: '/dashboard', icon: '📊' },
  { label: 'Students', to: '/students', icon: '👩‍🎓' },
  { label: 'Attendance', to: '/attendance', icon: '📝' },
]

const activePath = computed(() => route.path)
const showShell = computed(() => route.meta.shell !== false)

const logout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div v-if="showShell" class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <span class="brand__pill">AI</span>
        <div>
          <p class="brand__title">Mini Attendance</p>
          <p class="brand__subtitle">Vue · Laravel</p>
        </div>
      </div>
      <nav>
        <RouterLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="nav-link"
          :class="{ active: activePath.startsWith(link.to) }"
        >
          <span class="nav-link__icon">{{ link.icon }}</span>
          <span>{{ link.label }}</span>
        </RouterLink>
      </nav>
      <button class="theme-toggle" @click="uiStore.toggleTheme">
        <span class="theme-toggle__icon">{{ uiStore.theme === 'light' ? '🌙' : '☀️' }}</span>
        <span>{{ uiStore.theme === 'light' ? 'Dark mode' : 'Light mode' }}</span>
      </button>
      <footer class="sidebar__footer">AI-assisted workflow demo</footer>
    </aside>

    <section class="content">
      <header class="content__header">
        <div>
          <p class="content__eyebrow">School Ops</p>
          <h1>Attendance Control Center</h1>
        </div>
        <div class="user-pill" v-if="authStore.user">
          <div>
            <p class="user-pill__name">{{ authStore.user.name }}</p>
            <p class="user-pill__email">{{ authStore.user.email }}</p>
          </div>
          <button class="logout" @click="logout">Logout</button>
        </div>
        <p class="content__hint" v-else>All data updates in real-time via Laravel API.</p>
      </header>
      <main class="content__body">
        <RouterView />
      </main>
    </section>
  </div>
  <div v-else class="auth-shell">
    <RouterView />
  </div>
</template>

<style scoped>
.app-shell {
  display: grid;
  grid-template-columns: 260px 1fr;
  min-height: 100vh;
  background: var(--color-background-soft);
}

.sidebar {
  background: var(--color-background);
  border-right: 1px solid var(--color-border);
  padding: 2rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.brand {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.brand__pill {
  background: var(--color-accent);
  color: #0a0a0a;
  border-radius: 999px;
  font-weight: 700;
  padding: 0.4rem 0.8rem;
}

.brand__title {
  font-size: 1.1rem;
  font-weight: 600;
}

.brand__subtitle {
  font-size: 0.85rem;
  color: var(--color-text-soft);
}

nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.65rem 0.8rem;
  border-radius: 0.6rem;
  color: var(--color-text);
  font-weight: 500;
  text-decoration: none;
  transition: background 0.2s ease, color 0.2s ease;
}

.nav-link__icon {
  font-size: 1.1rem;
}

.nav-link:hover {
  background: var(--color-background-soft);
}

.nav-link.active {
  background: var(--color-accent-soft);
  color: #0a0a0a;
}

.theme-toggle {
  margin-top: 0.75rem;
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 0.8rem;
  border-radius: 0.6rem;
  border: 1px solid var(--color-border);
  background: transparent;
  color: var(--color-text);
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease, border 0.2s ease;
}

.theme-toggle:hover {
  background: var(--color-background-soft);
  border-color: var(--color-border-hover);
}

.theme-toggle__icon {
  font-size: 1.1rem;
}

.sidebar__footer {
  margin-top: auto;
  font-size: 0.8rem;
  color: var(--color-text-soft);
}

.content {
  padding: 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.content__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.content__eyebrow {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.1em;
  color: var(--color-text-soft);
  margin-bottom: 0.25rem;
}

.content__hint {
  color: var(--color-text-soft);
}

.content__body {
  background: var(--color-background);
  border: 1px solid var(--color-border);
  border-radius: 1rem;
  padding: 1.5rem;
  min-height: 70vh;
}

.user-pill {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 999px;
  padding: 0.4rem 0.75rem;
}

.user-pill__name {
  font-weight: 600;
}

.user-pill__email {
  font-size: 0.8rem;
  color: var(--color-text-soft);
}

.logout {
  border: none;
  background: transparent;
  color: var(--color-text);
  font-weight: 500;
  cursor: pointer;
}

.auth-shell {
  min-height: 100vh;
}

@media (max-width: 900px) {
  .app-shell {
    grid-template-columns: 1fr;
  }

  .sidebar {
    flex-direction: row;
    flex-wrap: wrap;
    border-right: none;
    border-bottom: 1px solid var(--color-border);
  }

  nav {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .content {
    padding: 1.5rem;
  }
}
</style>

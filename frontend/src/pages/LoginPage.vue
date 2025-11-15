<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')

const submit = async () => {
  error.value = ''
  try {
    await authStore.login({ email: email.value, password: password.value })
    const redirect = route.query.redirect || '/dashboard'
    router.push(redirect)
  } catch (err) {
    error.value = err.message || 'Unable to login'
  }
}
</script>

<template>
  <section class="login">
    <div class="login__card">
      <header>
        <p class="eyebrow">Attendance Console</p>
        <h1>Sign in</h1>
        <p>Use your staff credentials to access dashboards.</p>
      </header>
      <form @submit.prevent="submit">
        <label>
          <span>Email</span>
          <input v-model="email" type="email" required autocomplete="email" />
        </label>
        <label>
          <span>Password</span>
          <input v-model="password" type="password" required autocomplete="current-password" />
        </label>
        <p v-if="error" class="error">{{ error }}</p>
        <button type="submit" :disabled="authStore.loading">
          {{ authStore.loading ? 'Signing in…' : 'Login' }}
        </button>
      </form>
    </div>
  </section>
</template>

<style scoped>
.login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-background-soft);
  padding: 2rem;
}

.login__card {
  width: min(400px, 100%);
  background: var(--color-background);
  border: 1px solid var(--color-border);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

header {
  margin-bottom: 1.5rem;
}

.eyebrow {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.1em;
  color: var(--color-text-soft);
  margin-bottom: 0.25rem;
}

form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.9rem;
}

input {
  padding: 0.65rem 0.75rem;
  border-radius: 0.6rem;
  border: 1px solid var(--color-border);
  background: transparent;
  color: var(--color-text);
}

button {
  padding: 0.75rem;
  border-radius: 0.6rem;
  border: none;
  background: var(--color-accent);
  color: #0a0a0a;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error {
  color: #ff6b6b;
}
</style>

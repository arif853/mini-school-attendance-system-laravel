import { defineStore } from 'pinia'
import api, { setAuthToken } from '../services/api'

const TOKEN_STORAGE_KEY = 'attendance-token'

function readStoredToken() {
  if (typeof window === 'undefined') return null
  return window.localStorage.getItem(TOKEN_STORAGE_KEY)
}

function persistToken(token) {
  if (typeof window === 'undefined') return
  if (token) {
    window.localStorage.setItem(TOKEN_STORAGE_KEY, token)
  } else {
    window.localStorage.removeItem(TOKEN_STORAGE_KEY)
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    loading: false,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    async hydrate() {
      const token = readStoredToken()
      if (!token) {
        setAuthToken(null)
        return
      }
      this.token = token
      setAuthToken(token)
      try {
        const { data } = await api.get('/auth/me')
        this.user = data
      } catch (error) {
        await this.logout(false)
      }
    },
    async login(credentials) {
      this.loading = true
      try {
        const { data } = await api.post('/auth/login', credentials)
        this.token = data.token
        this.user = data.user
        setAuthToken(this.token)
        persistToken(this.token)
        return data
      } finally {
        this.loading = false
      }
    },
    async logout(remote = true) {
      if (remote && this.token) {
        try {
          await api.post('/auth/logout')
        } catch (error) {
          // ignore network errors during logout
        }
      }
      this.user = null
      this.token = null
      setAuthToken(null)
      persistToken(null)
    },
  },
})

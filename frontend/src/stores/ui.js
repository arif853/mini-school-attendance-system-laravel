import { defineStore } from 'pinia'

const STORAGE_KEY = 'attendance-theme'

function getPreferredTheme() {
  if (typeof window === 'undefined') {
    return 'light'
  }
  const stored = window.localStorage.getItem(STORAGE_KEY)
  if (stored === 'light' || stored === 'dark') {
    return stored
  }
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function applyTheme(theme) {
  document.documentElement.dataset.theme = theme
}

export const useUiStore = defineStore('ui', {
  state: () => ({
    theme: getPreferredTheme(),
  }),
  actions: {
    initTheme() {
      applyTheme(this.theme)
    },
    toggleTheme() {
      this.theme = this.theme === 'light' ? 'dark' : 'light'
      window.localStorage.setItem(STORAGE_KEY, this.theme)
      applyTheme(this.theme)
    },
  },
})

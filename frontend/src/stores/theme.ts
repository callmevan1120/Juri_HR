import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

const STORAGE_KEY = 'jurihr:theme'

export type Theme = 'light' | 'dark'

function initialTheme(): Theme {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored === 'light' || stored === 'dark') return stored
  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

export const useThemeStore = defineStore('theme', () => {
  const theme = ref<Theme>(initialTheme())

  watch(
    theme,
    (value) => {
      document.documentElement.classList.toggle('dark', value === 'dark')
      localStorage.setItem(STORAGE_KEY, value)
    },
    { immediate: true },
  )

  function toggle() {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
  }

  return { theme, toggle }
})

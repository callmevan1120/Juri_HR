<script setup lang="ts">
import { useThemeStore } from '@/stores/theme'

const theme = useThemeStore()
</script>

<template>
    <button
        type="button"
        class="tap-target rounded-lg px-2 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        :aria-label="theme.theme === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
        @click="theme.toggle"
    >
        <svg
            class="mx-auto size-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            aria-hidden="true"
        >
            <path
                v-if="theme.theme === 'dark'"
                d="M12 3v2m0 14v2M5 12H3m18 0h-2M6.3 6.3 4.9 4.9m14.2 14.2-1.4-1.4m0-12.8 1.4-1.4M4.9 19.1l1.4-1.4M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
            />
            <path v-else d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
        </svg>
    </button>
</template>

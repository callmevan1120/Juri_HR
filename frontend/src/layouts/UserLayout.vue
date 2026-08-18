<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import OfflineBanner from '@/components/OfflineBanner.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import { USER_NAV } from '@/router/nav'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
    await auth.logout()
    await router.replace('/login')
}
</script>

<template>
    <div class="flex min-h-svh flex-col">
        <OfflineBanner />

        <header
            class="sticky top-0 z-10 flex h-14 items-center gap-2 border-b border-gray-200 bg-white px-4 pt-[env(safe-area-inset-top)] dark:border-gray-800 dark:bg-gray-900"
        >
            <img src="/logo.png" alt="" class="h-7 w-auto" />
            <span class="font-semibold">JuriHR</span>
            <div class="ml-auto flex items-center gap-1">
                <ThemeToggle />
                <button
                    v-if="auth.isHrd"
                    type="button"
                    class="tap-target rounded-lg px-3 text-sm"
                    @click="router.push('/admin')"
                >
                    Admin
                </button>
                <button
                    type="button"
                    class="tap-target rounded-lg px-3 text-sm text-red-600"
                    @click="logout"
                >
                    Keluar
                </button>
            </div>
        </header>

        <main class="flex-1 p-4 pb-24">
            <slot />
        </main>

        <nav
            class="fixed inset-x-0 bottom-0 z-10 border-t border-gray-200 bg-white pb-[env(safe-area-inset-bottom)] dark:border-gray-800 dark:bg-gray-900"
            aria-label="Navigasi bawah"
        >
            <ul class="flex">
                <li v-for="item in USER_NAV" :key="item.to" class="flex-1">
                    <RouterLink
                        :to="item.to"
                        class="tap-target flex flex-col items-center justify-center gap-0.5 py-2 text-[11px] text-gray-600 dark:text-gray-400"
                        active-class="text-primary-600 dark:text-primary-400"
                    >
                        <svg
                            class="size-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            aria-hidden="true"
                        >
                            <path :d="item.icon" />
                        </svg>
                        {{ item.label }}
                    </RouterLink>
                </li>
            </ul>
        </nav>
    </div>
</template>

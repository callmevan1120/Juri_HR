<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import OfflineBanner from '@/components/OfflineBanner.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import { ADMIN_NAV } from '@/router/nav'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const sidebarOpen = ref(false)
const userMenuOpen = ref(false)

async function logout() {
    await auth.logout()
    await router.replace('/login')
}
</script>

<template>
    <div class="min-h-svh">
        <OfflineBanner />

        <div class="flex">
            <!-- Sidebar -->
            <aside
                class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 border-r border-gray-200 bg-white transition-transform lg:static lg:translate-x-0 dark:border-gray-800 dark:bg-gray-900"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-14 items-center gap-2 px-4">
                    <img src="/logo.png" alt="" class="h-7 w-auto" />
                    <span class="font-semibold">JuriHR</span>
                </div>
                <nav class="space-y-1 p-2" aria-label="Menu utama">
                    <RouterLink
                        v-for="item in ADMIN_NAV"
                        :key="item.to"
                        :to="item.to"
                        class="tap-target flex items-center gap-3 rounded-lg px-3 text-sm hover:bg-gray-100 dark:hover:bg-gray-800"
                        active-class="bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300"
                        @click="sidebarOpen = false"
                    >
                        <svg
                            class="size-5 shrink-0"
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
                </nav>
            </aside>

            <button
                v-if="sidebarOpen"
                type="button"
                class="fixed inset-0 z-20 bg-black/40 lg:hidden"
                aria-label="Tutup menu"
                @click="sidebarOpen = false"
            ></button>

            <div class="min-w-0 flex-1">
                <!-- Topbar -->
                <header
                    class="flex h-14 items-center justify-between gap-2 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-900"
                >
                    <button
                        type="button"
                        class="tap-target rounded-lg px-2 lg:hidden"
                        aria-label="Buka menu"
                        @click="sidebarOpen = true"
                    >
                        <svg
                            class="mx-auto size-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="ml-auto flex items-center gap-1">
                        <ThemeToggle />
                        <RouterLink
                            to="/admin/pengumuman"
                            class="tap-target rounded-lg px-2 text-gray-600 dark:text-gray-300"
                            aria-label="Notifikasi"
                        >
                            <svg
                                class="mx-auto size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    d="M15 17h5l-1.4-2V11a6.6 6.6 0 0 0-5-6.4V3h-3v1.6A6.6 6.6 0 0 0 5.4 11v4L4 17h5m6 0a3 3 0 0 1-6 0"
                                />
                            </svg>
                        </RouterLink>

                        <div class="relative">
                            <button
                                type="button"
                                class="tap-target rounded-lg px-3 text-sm font-medium"
                                :aria-expanded="userMenuOpen"
                                @click="userMenuOpen = !userMenuOpen"
                            >
                                {{ auth.session?.full_name ?? 'Pengguna' }}
                            </button>
                            <div
                                v-if="userMenuOpen"
                                class="card absolute right-0 z-10 mt-1 w-44 p-1 text-sm"
                                @click="userMenuOpen = false"
                            >
                                <RouterLink
                                    to="/profil"
                                    class="tap-target flex items-center rounded-lg px-3 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Profil
                                </RouterLink>
                                <button
                                    type="button"
                                    class="tap-target flex w-full items-center rounded-lg px-3 text-left text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                                    @click="logout"
                                >
                                    Keluar
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-4 lg:p-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppToast from '@/components/ui/AppToast.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import GuestLayout from '@/layouts/GuestLayout.vue'
import UserLayout from '@/layouts/UserLayout.vue'
import { useThemeStore } from '@/stores/theme'

useThemeStore()

const route = useRoute()

const LAYOUTS = { admin: AdminLayout, user: UserLayout, guest: GuestLayout }

const layout = computed(() => LAYOUTS[route.meta.layout ?? 'guest'])
</script>

<template>
    <component :is="layout">
        <RouterView />
    </component>
    <AppToast />
</template>

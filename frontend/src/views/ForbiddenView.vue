<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppButton from '@/components/ui/AppButton.vue'
</script>

<template>
    <div class="grid min-h-svh place-items-center p-6 text-center">
        <div class="space-y-3">
            <p class="text-primary-600 dark:text-primary-400 text-4xl font-bold">403</p>
            <h1 class="text-lg font-semibold">Akses ditolak</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Anda tidak punya izin untuk membuka halaman ini.
            </p>
            <RouterLink to="/">
                <AppButton variant="secondary">Kembali ke Home</AppButton>
            </RouterLink>
        </div>
    </div>
</template>

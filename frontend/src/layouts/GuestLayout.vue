<script setup lang="ts">
import OfflineBanner from '@/components/OfflineBanner.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
</script>

<template>
    <div class="min-h-svh">
        <OfflineBanner />
        <div class="absolute top-3 right-3">
            <ThemeToggle />
        </div>
        <slot />
    </div>
</template>

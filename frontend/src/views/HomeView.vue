<script setup lang="ts">
const appName = import.meta.env.VITE_APP_NAME ?? 'JuriHR'
</script>

<template>
    <main class="grid min-h-svh place-items-center p-6">
        <h1 class="text-2xl font-semibold">{{ appName }}</h1>
    </main>
</template>
